<?php
/**
 * Class to handle email sending operations
 *
 * Uses the PEAR Mail Queue to send and store email messages
 */
require_once($cfg["path_bu"] . "/BUUser.inc.php");
require_once($cfg["path_gc"] . "/Business.inc.php");
/*
 * PEAR Mail classes
 */
require_once("Mail.php");
require_once("Mail/mime.php");
require_once("Mail/smtp.php");
require_once("Mail/Queue.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUMail extends Business
{

    const SECONDS_DELAY_UNTIL_SEND = 1;
    const DELETE_AFTER_SEND = 1;
    const MAIL_QUEUE_SEND_LIMIT = 30;
    const MAIL_QUEUE_TRY_LIMIT = 5;

    private $crlf = "\n";
    private $mailQueue;
    private $mailQueueSendLimit;
    public $mime;
    private $buUser;

    /**
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);

        $this->mime = new Mail_Mime;

        $this->mailQueue = new Mail_Queue (
            $GLOBALS ['db_options'],
            $GLOBALS ['mail_options']
        );

        $this->buUser = new BUUser($this);
    }

    /**
     * Send mail message directly, bypassing the queue. Useful for "queue problem" email
     *
     * @param mixed $toEmail
     * @param mixed $headers
     * @param mixed $body
     * @param mixed $sendToSdManagerRecipients
     * @return mixed
     */
    public function send(
        $toEmail,
        $headers,
        $body,
        $sendToSdManagerRecipients = false      // obsolete
    )
    {
        $parameters = $this->prepareMessage($toEmail, $headers);

        $mail = new Mail_smtp(
            $GLOBALS ['mail_options']
        );

        $sent = $mail->send(
            $parameters['toEmail'],
            $parameters['headers'],
            $body
        );

        return $sent;
    }

    public function putInQueue(
        $fromEmail,
        $toEmail,
        $headers,
        $body,
        $sendToSdManagerRecipients = false
    )
    {
        $userID = false; // initialise

        $parameters = $this->prepareMessage($toEmail, $headers);

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

    private function prepareMessage($toEmail, $headers)
    {
        /*
        if we are not in live environment then send to test account but append list of
        live email address it would have gone to.
        */
        if ($GLOBALS ['server_type'] != MAIN_CONFIG_SERVER_TYPE_LIVE) {
            $headers['Subject'] = $headers['Subject'] . ' | Emails to: ' . $toEmail;
            $toEmail = CONFIG_TEST_EMAIL;

        }

        $headers['To'] = $toEmail;

        if (!$userID && isset($GLOBALS ['auth'])) {
            $userID = ( string )$GLOBALS ['auth']->is_authenticated();
        } else {
            $userID = 0;
        }

        return
            array(
                'userID' => $userID,
                'toEmail' => $toEmail,
                'headers' => $headers
            );

    }

    public function sendQueue()
    {
        /*
        reset sending field if started sending time older than 15 minutes
        */
        $sql =
            "UPDATE
          mail_queue
        SET
          time_started_sending = '0000-00-00 00:00:00'
        WHERE
          time_started_sending < DATE_SUB( NOW(), INTERVAL 15 MINUTE )";

        $this->db->query($sql);

        return $this->mailQueue->sendMailsInQueue(
            self::MAIL_QUEUE_SEND_LIMIT,
            0,
            self::MAIL_QUEUE_TRY_LIMIT,
            array($this, 'mailqueueCallBackAfterSend'),
            array($this, 'mailqueueCallBackBeforeSend')
        );

    }

    public function mailqueueCallBackBeforeSend($args)
    {
        $mailId = $args['id'];

        $sql =
            "SELECT
        time_started_sending
      FROM
        mail_queue
      WHERE
        id = $mailId";

        $result = $this->db->query($sql);

        $row = $result->fetch_object();

        $ret = false;

        if ($row->time_started_sending == '0000-00-00 00:00:00') {
            $ret = true;
            /*
            Set is_sending flag
            */
            $sql =
                "UPDATE
          mail_queue
        SET
          time_started_sending = NOW()
        WHERE
          id = $mailId";

            $this->db->query($sql);
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
        $sql =
            "UPDATE
        mail_queue
      SET
        time_started_sending = '0000-00-00 00:00:00'
      WHERE
        id = $mailId";

        $this->db->query($sql);
    }
}

?>