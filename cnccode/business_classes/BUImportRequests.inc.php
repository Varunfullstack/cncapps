<?php
/**
 * Email request business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Business\BUActivity;

global $cfg;
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once $cfg['path_dbe'] . '/DBEProblem.inc.php';
require_once $cfg['path_dbe'] . '/DBEPendingReopened.php';

class BUImportRequests extends Business
{

    var $buActivity = '';

    var $updateDb = false;

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->buActivity = new BUActivity($this);
        $this->updateDb   = new dbSweetcode;
    }

    public function createServiceRequests()
    {
        $db = new dbSweetcode();
        echo "Start Import<BR/>";
        $processedMessages = 0;
        //TODO: create a class that represents the automated_request row
        /*
        Putting a limit on this means that if the process gets behind it will process in batches
        instead of putting a big load on the server.
        */
        $sql = "
      SELECT
        *
      FROM
        automated_request
      WHERE
        importedFlag = 'N'
        AND importErrorFound = 'N'
      ORDER BY
        automatedRequestId
      LIMIT 15";
        $db->query($sql);
        $toDelete = [];
        /** @var \CNCLTD\AutomatedRequest $automatedRequest */
        while ($automatedRequest = $db->nextObjectRecord(\CNCLTD\AutomatedRequest::class)) {
            echo 'Start processing ' . $automatedRequest->getAutomatedRequestID() . "<BR/>";
            echo '<br>Description: ';
            echo $automatedRequest->getTextBody();
            echo '<br>';
            $errorString = '';
            if ($this->processMessage(
                $automatedRequest
            )) {      // error string returned
                echo $automatedRequest->getAutomatedRequestID() . " processed successfully<BR/>";
                $processedMessages++;
                $dbeLastActivity = $this->buActivity->getLastActivityInProblem(
                    $automatedRequest->getServiceRequestID()
                );
                if ($dbeLastActivity->rowCount > 0) $this->buActivity->updateInbound(
                    $dbeLastActivity->getValue(DBEJCallActivity::callActivityID),
                    true
                );
            } else {
                echo $automatedRequest->getAutomatedRequestID() . " failed<BR/>";
                $this->sendFailureEmail(
                    $automatedRequest->getSenderEmailAddress(),
                    $automatedRequest->getCreateDateTime(),
                    $automatedRequest->getSubjectLine(),
                    $automatedRequest->getHtmlBody(),
                    $errorString
                );
            }
            $toDelete[] = $automatedRequest->getAutomatedRequestID();

        } // end while
        echo $processedMessages . " requests imported<BR/>";
        if (count($toDelete)) {
            echo 'Deleting requests';
            $sql = "DELETE FROM automated_request
                    WHERE automatedRequestId IN (" . implode(
                    ',',
                    $toDelete
                ) . ")";
            $db->query($sql);
        }
        echo "End<BR/>";
        return $processedMessages;

    }

    /**
     * @param $record
     * @return bool|mixed
     * @throws Exception
     */
    protected function processMessage($record)
    {
        return $this->buActivity->processAutomaticRequest($record);
    }

    function sendFailureEmail($sender,
                              $dateTime,
                              $subject,
                              $body,
                              $errorString
    )
    {
        global $cfg;
        $buMail      = new BUMail($this);
        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $toEmail     = "CNCServiceDesk@" . CONFIG_PUBLIC_DOMAIN;
        $template    = new Template(
            $cfg["path_templates"], "remove"
        );
        $template->set_file(
            'page',
            'ImportRequestFailedEmail.inc.html'
        );
        $template->set_var(
            'sender',
            $sender
        );
        $template->set_var(
            'dateTime',
            $dateTime
        );
        $template->set_var(
            'subject',
            $subject
        );
        $template->set_var(
            'body',
            $body
        );
        $template->set_var(
            'errorString',
            $errorString
        );
        $template->parse(
            'output',
            'page',
            true
        );
        $body = $template->get_var('output');
        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => "Automated import failure from $sender",
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        $buMail->mime->setHTMLBody($body);
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body        = $buMail->mime->get($mime_params);
        $hdrs        = $buMail->mime->headers($hdrs);
        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }
} // End of class
?>