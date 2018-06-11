<?php /**
 * Prizewinner business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");

require_once($cfg["path_dbe"] . "/DBEPrizewinner.inc.php");
require_once($cfg["path_dbe"] . "/DBEJPrizewinner.inc.php");

require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_bu"] . "/BUContact.inc.php");
require_once($cfg["path_bu"] . "/BUCustomerNew.inc.php");

class BUPrizewinner extends Business
{
    var $dbePrizewinner = "";

    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbePrizewinner = new dbePrizewinner($this);
        $this->dbeJPrizewinner = new dbeJPrizewinner($this);
    }

    function updatePrizewinner(&$dsData)
    {
        $this->setMethodName('updatePrizewinner');


        if ($this->dbePrizewinner->getRow($dsData->getValue('prizewinnerID'))) {

            $approvedFlagBefore = $this->dbePrizewinner->getValue('approvedFlag');

        } else {
            $approvedFlagBefore = 'N';          // new row
        }


        $this->updateDataaccessObject($dsData, $this->dbePrizewinner);

        if ($dsData->getValue('approvedFlag') == 'Y' AND $approvedFlagBefore == 'N') {

            /*
            Send emails out
            */
            $this->sendWinnerEmail($this->dbePrizewinner->getvalue('contactID'));

        }
        return TRUE;
    }

    function sendWinnerEmail($winnerContactID)
    {

        $buMail = new BUMail($this);

        $buCustomer = new BUCustomer($this);
        $buCustomer->getContactByID($winnerContactID, $dsContact);

        $buCustomer->getCustomerByID($dsContact->getValue('customerID'), $dsCustomer);

        $senderEmail = CONFIG_CUSTOMER_SERVICE_EMAIL;
        $senderName = 'CNC Support Department';

        $toEmail = $dsContact->getValue('email');

        $template = new Template (EMAIL_TEMPLATE_DIR, "remove");
        $template->set_file('page', 'PrizewinnerWinnersEmail.inc.html');

        $winnerName = $dsContact->getValue('firstName') . ' ' . $dsContact->getValue('lastName');
        $winnerCompany = $dsCustomer->getValue(DBECustomer::name);

        $template->setVar(
            array(
                'firstName' => $dsContact->getValue('firstName')
            )
        );

        $template->parse('output', 'page', true);

        $body = $template->get_var('output');

        $hdrs = array(
            'From' => $senderEmail,
            'To' => $toEmail,
            'Subject' => 'CNC Technical Support Questionnaire Feedback',
            'Date' => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset' => 'UTF-8',
            'html_charset' => 'UTF-8',
            'head_charset' => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );


        if ($GLOBALS ['server_type'] == MAIN_CONFIG_SERVER_TYPE_LIVE) {
            /*
            Individual emails to each support contact at every company
            */
            $buContact = new BUContact($this);
            $buContact->getTechnicalMailshotContacts($dsContact);

            $duplicateList = array();

            while ($dsContact->fetchNext()) {

                $template = new Template (EMAIL_TEMPLATE_DIR, "remove");
                $template->set_file('page', 'PrizewinnerContactsEmail.inc.html');

                $buCustomer->getCustomerByID($dsContact->getValue('customerID'), $dsCustomer);

                $template->setVar(
                    array(
                        'contactName' => $dsContact->getValue('firstName') . ' ' . $dsContact->getValue('lastName'),
                        'contactCompany' => $dsCustomer->getValue(DBECustomer::name),
                        'winnerName' => $winnerName,
                        'winnerCompanyName' => $winnerCompany
                    )
                );

                $template->parse('output', 'page', true);

                $body = $template->get_var('output');

                // exclude winner and duplicates
                if (
                    $dsContact->getValue('contactID') != $winnerContactID AND
                    !in_array(strtolower($dsContact->getValue('email')), $duplicateList)
                ) {
                    $duplicateList[] = strtolower($dsContact->getValue('email'));

                    $this->sendOnePrizewinnerContactsEmail($senderEmail, $dsContact->getValue('email'), $body);

                }

            } // end while

        } else {
            /* just one email to test user */
            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");
            $template->set_file('page', 'PrizewinnerContactsEmail.inc.html');

            $buCustomer->getCustomerByID($dsContact->getValue('customerID'), $dsCustomer);

            $template->setVar(
                array(
                    'contactName' => 'Contact Name',
                    'contactCompany' => 'Contact Company',
                    'winnerName' => $winnerName,
                    'winnerCompanyName' => $winnerCompany
                )
            );

            $template->parse('output', 'page', true);

            $body = $template->get_var('output');

            $this->sendOnePrizewinnerContactsEmail($senderEmail, CONFIG_TEST_EMAIL, $body);

        }


    } // end sendWinnerEmails

    /**
     * Send one email to a support contact
     *
     * @param mixed $senderEmail
     * @param mixed $toEmail
     * @param mixed $body
     */
    function sendOnePrizewinnerContactsEmail($senderEmail, $toEmail, $body)
    {
        $buMail = new BUMail($this);

        $hdrs = array(
            'From' => $senderEmail,
            'To' => $toEmail,
            'Subject' => 'CNC Technical Support Questionnaire Feedback',
            'Date' => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );


        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset' => 'UTF-8',
            'html_charset' => 'UTF-8',
            'head_charset' => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

    function getPrizewinnerByID($ID, &$dsResults)
    {
        $this->dbeJPrizewinner->getRow($ID);
        return ($this->getData($this->dbeJPrizewinner, $dsResults));
    }

    function getAll(&$dsResults)
    {
        $this->dbeJPrizewinner->getRows();

        return ($this->getData($this->dbeJPrizewinner, $dsResults));
    }

    function deletePrizewinner($ID)
    {
        $this->setMethodName('deletePrizewinner');
        return $this->dbePrizewinner->deleteRow($ID);
    }

    function getAllPrizewinners(&$dsResults)
    {
        $this->dbeJPrizewinner->getRows();

        return ($this->getData($this->dbeJPrizewinner, $dsResults));
    }

    function deleteQuestion($ID)
    {
        $this->setMethodName('deleteQuestion');
        if ($this->canDeleteQuestion($ID)) {
            return $this->dbePrizewinner->deleteRow($ID);
        } else {
            return FALSE;
        }
    }
}// End of class
?>