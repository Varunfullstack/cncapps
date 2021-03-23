<?php
/**
 * Sales Service Request Alert Emails
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

class BUDailySalesRequestEmail extends Business
{
    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function getRequests()
    {

        $sql =
            "SELECT
          cus_name,
          odh_ordno
        FROM
          ordhead
          JOIN customer ON cus_custno = odh_custno
        WHERE
          serviceRequestInternalNote > ''";

        return $this->db->query($sql);

    }

    function sendEmail()
    {
        global $cfg;

        $buMail = new BUMail($this);

        $senderEmail = CONFIG_SALES_EMAIL;
        $senderName = 'CNC Sales Department';

        $toEmail = CONFIG_SALES_EMAIL;

        $results = $this->getRequests();

        if ($row = $results->fetch_object()) {

            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $template->set_file('page', 'DailySalesRequestEmail.inc.html');

            $template->set_block('page', 'requestBlock', 'requests');

            do {

                $urlOrder = SITE_URL. '/SalesOrder.php?action=displaySalesOrder&ordheadID=' . $row->odh_ordno;

                $template->set_var(
                    array(
                        'customer' => $row->cus_name,
                        'ordheadID' => $row->odh_ordno,
                        'urlOrder' => $urlOrder
                    )
                );

                $template->parse('requests', 'requestBlock', true);

            } while ($row = $results->fetch_object());


            $template->parse('output', 'page', true);

            $body = $template->get_var('output');

            $hdrs = array(
                'From' => $senderEmail,
                'To' => $toEmail,
                'Subject' => 'Daily Sales Request Report',
                'Date' => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            echo $body;
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
    }
}
