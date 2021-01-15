<?php
/**
 * Class to handle email sending operations
 *
 * Uses the PEAR Mail Queue to send and store email messages
 */

use CNCLTD\Email\AttachmentCollection;

require_once($cfg["path_bu"] . "/BUUser.inc.php");
require_once($cfg["path_gc"] . "/Business.inc.php");
/*
 * PEAR Mail classes
 */
require_once(__DIR__ . "/../../php/PEAR/Mail.php");
require_once(__DIR__ . "/../../php/PEAR/Mail/smtp.php");
require_once(__DIR__ . "/../../php/PEAR/Mail/mime.php");
require_once(__DIR__ . "/../../php/PEAR/Mail/Queue.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUMail extends Business
{

    const SECONDS_DELAY_UNTIL_SEND = 1;
    const DELETE_AFTER_SEND        = 1;
    const MAIL_QUEUE_SEND_LIMIT    = 20;
    const MAIL_QUEUE_TRY_LIMIT     = 5;
    public  $mime;
    private $mailQueue;
    private $buUser;

    /**
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->mime      = new Mail_Mime;
        $this->mailQueue = new Mail_Queue (
            $GLOBALS['db_options'], $GLOBALS['mail_options']
        );
        $this->buUser    = new BUUser($this);
    }

    function sendEmailWithAttachments($body,
                                      $subject,
                                      $recipients,
                                      AttachmentCollection $attachmentCollection,
                                      $fromEmail = CONFIG_SUPPORT_EMAIL
    )
    {

        $hdrs = array(
            'From'         => $fromEmail,
            'To'           => $recipients,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        $mime = new Mail_mime();
        foreach ($attachmentCollection as $attachment) {
            $mime->addAttachment(
                $attachment->getContent(),
                $attachment->getContentType(),
                $attachment->getName(),
                $attachment->getIsFile()
            );
        }
        $mime->setHTMLBody($body);
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body        = $mime->get($mime_params);
        $hdrs        = $mime->headers($hdrs);
        $this->putInQueue($fromEmail, $recipients, $hdrs, $body);
    }

    public function putInQueue($fromEmail,
                               $toEmail,
                               $headers,
                               $body
    )
    {
        $parameters = $this->prepareMessage(
            $toEmail,
            $headers
        );
        return $this->mailQueue->put(
            $fromEmail,
            $parameters['toEmail'],
            $parameters['headers'],
            addslashes($body),
            self::SECONDS_DELAY_UNTIL_SEND,
            self::DELETE_AFTER_SEND,
            $parameters['userID']
        );

    }

    private function prepareMessage($toEmail,
                                    $headers
    )
    {
        /*
        if we are not in live environment then send to test account but append list of
        live email address it would have gone to.
        */
        if ($GLOBALS ['server_type'] != MAIN_CONFIG_SERVER_TYPE_LIVE) {
            $headers['Subject'] = $headers['Subject'] . ' | Emails to: ' . $toEmail;
            $toEmail            = CONFIG_TEST_EMAIL;
        }
        $userID = 0;
        if (isset($GLOBALS ['auth'])) {
            $userID = ( string )$GLOBALS ['auth']->is_authenticated();
        }
        return array(
            'userID'  => $userID,
            'toEmail' => $toEmail,
            'headers' => $headers
        );

    }

    function sendSimpleEmail($body,
                             $subject,
                             $recipients,
                             $fromEmail = CONFIG_SUPPORT_EMAIL,
                             ?array $cc = [],
                             ?array $bcc = []
    )
    {

        $hdrs = array(
            'From'         => $fromEmail,
            'To'           => $recipients,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        if (count($cc)) {
            $hdrs['Cc'] = implode(',', $cc);
            $recipients = implode(',', [$recipients, $hdrs['Cc']]);
        }
        if (count($bcc)) {
            $hdrs['Bcc'] = implode(',', $bcc);
            $recipients  = implode(',', [$recipients, $hdrs['Bcc']]);
        }
        $mime = new Mail_mime();
        $mime->setHTMLBody($body);
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body        = $mime->get($mime_params);
        $hdrs        = $mime->headers($hdrs);
        $this->putInQueue($fromEmail, $recipients, $hdrs, $body);
    }

    /**
     * Send mail message directly, bypassing the queue. Useful for "queue problem" email
     *
     * @param mixed $toEmail
     * @param mixed $headers
     * @param mixed $body
     * @return mixed
     */
    public function send($toEmail,
                         $headers,
                         $body
    )
    {
        $parameters = $this->prepareMessage(
            $toEmail,
            $headers
        );
        $mail       = new Mail_smtp(
            $GLOBALS ['mail_options']
        );
        $sent       = $mail->send(
            $parameters['toEmail'],
            $parameters['headers'],
            $body
        );
        return $sent;
    }

    public function sendQueue()
    {
        return $this->mailQueue->sendMailsInQueue(
            self::MAIL_QUEUE_SEND_LIMIT,
            0,
            self::MAIL_QUEUE_TRY_LIMIT
        );

    }

    public function mailqueueCallBackBeforeSend($args)
    {
        var_dump('Mailqueue call before send', $args);
        $mailId = $args['id'];
        $sql    = "SELECT
        time_started_sending
      FROM
        mail_queue
      WHERE
        id = $mailId";
        $this->db->commit();
        $result = $this->db->query($sql);
        $row    = $result->fetch_object();
        $ret    = false;
        if (!$row->time_started_sending || (DateTime::createFromFormat(
                DATE_MYSQL_DATETIME,
                $row->time_started_sending
            )) <= ((new DateTime())->sub(new DateInterval('PT15M')))) {
            $ret = true;
            /*
            Set is_sending flag
            */
            $sql = "UPDATE 
          mail_queue
        SET
          time_started_sending = NOW()
        WHERE
          id = $mailId";
            $this->db->query($sql);
            $this->db->commit();
        }
        return $ret;
    }

    /*
    This deals with the problem where sending failed and so the message still exists
    in the queue
    */
    public function mailqueueCallBackAfterSend($args)
    {
        $mailId = $args['id'];
        /*
        ReSet is_sending flag
        */
        $sql = "UPDATE
        mail_queue
      SET
        time_started_sending = null
      WHERE
        id = $mailId";
        $this->db->query($sql);
        $this->db->commit();
    }

    public function mailQueueSkipCallback($mailID)
    {

    }
}

?>