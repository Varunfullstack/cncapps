<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg ["path_bu"] . "/BUActivity.inc.php");

class BUEmailRequest extends Business
{

    public $buActivity;

    function BUActivity(&$owner)
    {
        $this->buActivity = new BUActivity($this);
    }

    public function createServiceRequestsFromEmails()
    {

        set_include_path($path . PATH_SEPARATOR . APPLICATION_DIR);
        require_once('Zend/Mail/Storage/Imap.php');
        require_once('Zend/Mime.php');

        try {
            $mail = new Zend_Mail_Storage_Imap($GLOBALS['request_mail_options']);
        } catch (Exception $e) {
            $this->raiseError('Error connecting to SupplierCertificate email account: ' . $e->getMessage());
        }

        $processedMessageNum = array();

        foreach ($mail as $messageNum => $message) {

            $bodyPart = $this->getMessagePart($message, 'text/plain');

            if ($bodyPart) {

                if ($this->processMessage($message->subject, $bodyPart)) {

                    $processedMessageNum[] = $messageNum;

                }

            }

            break;
        }
        /*
        Remove processed messages from inbox
        */
        if (count($processedMessageNum) > 0) {
            foreach ($processedMessageNum as $key => $messageNum) {
                $mail->removeMessage($messageNum);
            }
        }

        return count($processedMessageNum);

    }

    /**
     * Return first found part of given type
     *
     * recurses through all message parts
     *
     * @param mixed $mail
     * @param mixed $contentType
     */
    private function getMessagePart($message, $contentType)
    {
        $foundPart = null;
        foreach (new RecursiveIteratorIterator($message) as $part) {
            if (strtok($part->contentType, ';') == $contentType) {
                $foundPart = $part;
                break;
            }
        }
        return $foundPart;
    }

    /**
     * Import one email message with a PDF certificate attached
     *
     * @todo Move to a common lib
     *
     * @param mixed $user
     * @param mixed $bodyPart
     * @param mixed $filePart
     */
    protected function processMessage($subject, $bodyPart)
    {
        $processed = false;

        /* decode body */
        if ($bodyPart->contentTransferEncoding == 'base64') {
//      $bodyContent = quoted_printable_decode(base64_decode( $bodyPart->getContent() ) );
            $bodyContent = base64_decode($bodyPart->getContent());
        } else {
            $bodyContent = quoted_printable_decode($bodyPart->getContent());
        }
        /* decode subject */
//    $subject = quoted_printable_decode( $subject );    

        $request = false;
        /*
        Determine type of request from subject
        */
        /*
        Get customer number
        */
        if ($openBracketPos = strpos($subject, '[')) {

            $subjectLength = strlen($subject);
            $charPos = $openBracketPos + 1;

            $request['customerID'] = false;
            while (
                $subject[$charPos] != ']'
                AND $subject[$charPos] != '-'
                AND $charPos <= $subjectLength
            ) {
                $request['customerID'] .= $subject[$charPos];
                $charPos++;
            }
        }

        /*
        If we found a dash then the postcode is inside the square brackets and this
        is not Server Guard
        */
        if ($subject[$charPos] == '-') {

            $request['serverguardFlag'] = 'N';

            $charPos = $openBracketPos + 1;

        } else {

            $request['serverguardFlag'] = 'Y';
            /*
            post code will be in a second set of brackets
            */
            $charPos = strpos($subject, '[', $charPos) + 1;
        }


        $request['postcode'] = false;

        while ($subject[$charPos] != ']' AND $charPos <= $subjectLength) {
            $request['postcode'] .= $subject[$charPos];
            $charPos++;
        }


        if ($request['postcode'] && $request['customerID']) {

            $request['reason'] = $bodyContent;

            $this->buActivity->addCustomerRaisedRequest($request);

            $processed = true;

        }

        return $processed;

    }

    /**
     * Parse the plain text string for a field value
     *
     * format is:
     *
     * fieldname=fieldvalue\n where \n is the EOL character
     *
     * @param mixed $fieldName
     * @param mixed $text
     * @return string
     */
    private function getFieldFromText($fieldName, $text)
    {
        $fieldValue = false;

        $fieldNamePos = stripos($text, $fieldName);

        $endOfTextPos = strlen($text);

        if ($fieldNamePos !== false) {

            $fieldValuePos = $fieldNamePos + strlen($fieldName) + 1; // go past the =

            $endOfLinePos = $fieldValuePos;

            while (substr($text, $endOfLinePos, 1) != "\n" && $endOfLinePos <= $endOfTextPos) {
                $fieldValue .= substr($text, $endOfLinePos, 1);
                $endOfLinePos++;
            }
        }

        return $fieldValue;

    }


} // End of class
?>
