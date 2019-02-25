<?php /**
 * Contact business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');

class BUContactExport extends Business
{

    var $dbeContact = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function search(
        $dsSearchForm,
        $quotationItemIDs,
        $contractItemIDs,
        $sectorIDs
    )
    {
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $query =
            "SELECT DISTINCT";

        if ($dsSearchForm->getValue('exportEmailOnlyFlag')) {
            $query .= " con_email AS EmailAddress";
        } else {
            $query .=
                " con_custno as CustomerID,
        con_title AS Title,
        con_last_name AS LastName,
        con_first_name AS FirstName,
        con_position AS Position,
        cus_name AS Company,
        add_add1 AS BusinessStreet,
        add_add2 AS BusinessStreet2,
        add_add3  AS BusinessStreet3,
        add_town AS BusinessCity,
        add_county AS BusinessState,
        add_postcode AS BusinessPostalCode,
        add_phone AS BusinessPhone,
        con_phone AS BusinessPhone2,
        con_mobile_phone AS Mobile,
        con_fax AS BusinessFax,
        con_email AS EmailAddress,
        CONCAT(con_first_name,' ',con_last_name) AS DisplayName,
        cus_prospect AS Prospect";

            if ($dsSearchForm->getValue('noOfPCs')) {
                $query .= ", CONCAT( '\'', '" . $dsSearchForm->getValue('noOfPCs') . "') AS `PCs`";
            }
            if ($dsSearchForm->getValue('noOfServers')) {
                $query .= ", '" . $dsSearchForm->getValue('noOfServers') . "' AS `Servers >=`";
            }
            if ($dsSearchForm->getValue('sendMailshotFlag')) {
                $query .= ", 'Y' AS `Mailshot`";
            }
            if ($dsSearchForm->getValue('mailshot2Flag')) {
                $query .= ", 'Y' AS `" . $dsHeader->getValue('mailshot2FlagDesc') . "`";
            }
            if ($dsSearchForm->getValue('mailshot3Flag')) {
                $query .= ", 'Y' AS `" . $dsHeader->getValue('mailshot3FlagDesc') . "`";
            }
            if ($dsSearchForm->getValue('mailshot4Flag')) {
                $query .= ", 'Y' AS `" . $dsHeader->getValue('mailshot4FlagDesc') . "`";
            }
            if ($dsSearchForm->getValue('mailshot8Flag')) {
                $query .= ", 'Y' AS `" . $dsHeader->getValue('mailshot8FlagDesc') . "`";
            }
            if ($dsSearchForm->getValue('mailshot9Flag')) {
                $query .= ", 'Y' AS `" . $dsHeader->getValue('mailshot9FlagDesc') . "`";
            }
            if ($dsSearchForm->getValue('newCustomerFromDate')) {
                $query .= ", '" . $dsSearchForm->getValue('newCustomerFromDate') . "' AS `New Customer From`";
            }
            if ($dsSearchForm->getValue('newCustomerToDate')) {
                $query .= ", '" . $dsSearchForm->getValue('newCustomerToDate') . "' AS `New Customer To`";
            }
            if ($dsSearchForm->getValue('droppedCustomerFromDate')) {
                $query .= ", '" . $dsSearchForm->getValue('droppedCustomerFromDate') . "' AS `Lost Customer From`";
            }
            if ($dsSearchForm->getValue('droppedCustomerToDate')) {
                $query .= ", '" . $dsSearchForm->getValue('droppedCustomerToDate') . "' AS `Lost Customer To`";
            }
            if ($dsSearchForm->getValue('broadbandRenewalFlag')) {
                $query .= ", 'Y' AS `Broadband Renewal`";

            }

            if ($dsSearchForm->getValue(DBEContact::hrUser)) {
                $query .= ", 'Y' as HR";
            }

            if ($dsSearchForm->getValue(DBEContact::reviewUser)) {
                $query .= ", 'Y' as review";
            }

            if ($dsSearchForm->getValue('broadbandIsp')) {
                $query .= ", '" . $dsSearchForm->getValue('broadbandIsp') . "' AS `BroadbandIsp`";

            }
            if ($dsSearchForm->getValue('contractRenewalFlag')) {
                $query .= ", 'Y' AS `Contract Renewal`";

            }
            if ($dsSearchForm->getValue('quotationRenewalFlag')) {
                $query .= ", 'Y' AS `Quotation Renewal`";

            }
            $query .= ", sec_desc AS `Sector`";


        }// end

        $query .= "
      FROM contact
      JOIN address ON
        (con_siteno = add_siteno  AND con_custno = add_custno)
      JOIN customer ON
        con_custno = cus_custno";

        if ($dsSearchForm->getValue('contractRenewalFlag')) {
            $query .=
                " JOIN custitem cc ON cc.cui_custno = cus_custno";
        }
        if ($dsSearchForm->getValue('quotationRenewalFlag')) {
            $query .=
                " JOIN custitem qc ON qc.cui_custno = cus_custno";
        }

        $query .= " JOIN sector ON sec_sectorno = cus_sectorno";

        $query .= " WHERE con_discontinued = 'N'";

        if ($dsSearchForm->getValue(DBEContact::customerID)) {
            $query .= " AND cus_custno =  " . $dsSearchForm->getValue(DBEContact::customerID);
        }

        if ($dsSearchForm->getValue(DBECustomer::prospectFlag)) {
            $query .= " AND cus_prospect =  '" . $dsSearchForm->getValue(DBECustomer::prospectFlag) . "'";
        }
        if ($dsSearchForm->getValue(DBECustomer::noOfServers)) {
            $query .= " AND noOfServers >=  " . $dsSearchForm->getValue(DBECustomer::noOfServers);
        }

        if ($dsSearchForm->getValue(DBECustomer::noOfPCs)) {
            $query .= " AND noOfPCs =  '" . $dsSearchForm->getValue(DBECustomer::noOfPCs) . "'";
        }

        if ($dsSearchForm->getValue(DBEContact::sendMailshotFlag)) {
            $query .= " AND cus_mailshot =  'Y'";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot2Flag)) {
            $query .= " AND con_mailflag2 =  'Y'";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot3Flag)) {
            $query .= " AND con_mailflag3 =  'Y'";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot4Flag)) {
            $query .= " AND con_mailflag4 =  'Y'";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot8Flag)) {
            $query .= " AND con_mailflag8 =  'Y'";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot9Flag)) {
            $query .= " AND con_mailflag9 =  'Y'";
        }

        if ($dsSearchForm->getValue(DBEContact::hrUser)) {
            $query .= " and " . DBEContact::hrUser . " = 'Y'";
        }

        if ($dsSearchForm->getValue(DBEContact::reviewUser)) {
            $query .= " and " . DBEContact::reviewUser . " = 'Y'";
        }

        if ($dsSearchForm->getValue('broadbandRenewalFlag')) {
            $query .= " AND declinedFlag = 'N'";
        }

        if ($dsSearchForm->getValue(DBEContact::supportLevel)) {
            $selectedOptions = json_decode($dsSearchForm->getValue(DBEContact::supportLevel));
            if (count($selectedOptions) < 5) {
                $hasNone = false;
                if (in_array(
                    "",
                    $selectedOptions
                )) {
                    $selectedOptions = array_slice(
                        $selectedOptions,
                        1
                    );
                    $hasNone = true;
                }


                if ($hasNone) {
                    if (count($selectedOptions)) {
                        $query .= " and ( supportLevel is null or supportLevel = '' or supportLevel in (" . implode(
                                ",",
                                $selectedOptions
                            ) . ")) ";
                    } else {
                        $query .= " and supportLevel is null";
                    }
                } else {
                    $query .= " and supportLevel in (" . implode(
                            ",",
                            array_map(
                                function ($str) {
                                    return sprintf(
                                        "'%s'",
                                        $str
                                    );
                                },
                                $selectedOptions
                            )
                        ) . ")";
                }
            }

            var_dump($query);
        }

        if (
            $dsSearchForm->getValue('broadbandRenewalFlag') &&
            $dsSearchForm->getValue('broadbandIsp')
        ) {
            $query .= " AND lower(ispID) = lower('" . $dsSearchForm->getValue('broadbandIsp') . "')";
        }
        if ($dsSearchForm->getValue('contractRenewalFlag')) {
            $query .= " AND declinedFlag = 'N'";
        }
        if ($dsSearchForm->getValue('quotationRenewalFlag')) {
            $query .= " AND declinedFlag = 'N'";
        }

        if ($dsSearchForm->getValue('contractRenewalFlag') && $contractItemIDs) {
            $query .=
                " AND cui_itemno IN(
                        " . implode(
                    ',',
                    $contractItemIDs
                ) . "
                    )";
        }

        if ($dsSearchForm->getValue('quotationRenewalFlag') && $quotationItemIDs) {
            $query .=
                " AND cui_itemno IN(
                        " . implode(
                    ',',
                    $quotationItemIDs
                ) . "
                    )";
        }

        if ($dsSearchForm->getValue('newCustomerFromDate')) {
            $query .=
                " AND cus_became_customer_date >= '" . $dsSearchForm->getValue('newCustomerFromDate') . "'";
        }
        if ($dsSearchForm->getValue('newCustomerToDate')) {
            $query .=
                " AND cus_became_customer_date <= '" . $dsSearchForm->getValue('newCustomerToDate') . "'";
        }
        if ($dsSearchForm->getValue('droppedCustomerFromDate')) {
            $query .=
                " AND cus_dropped_customer_date >= '" . $dsSearchForm->getValue('droppedCustomerFromDate') . "'";
        }
        if ($dsSearchForm->getValue('droppedCustomerToDate')) {
            $query .=
                " AND cus_dropped_customer_date <= '" . $dsSearchForm->getValue('droppedCustomerToDate') . "'";
        }
        if ($dsSearchForm->getValue('exportEmailOnlyFlag')) {
            $query .= " AND con_email <> ''";
        }

        if ($sectorIDs) {
            $query .=
                " AND cus_sectorno IN(
                        " . implode(
                    ',',
                    $sectorIDs
                ) . "
                    )";
        }

        return $this->db->query($query);

    }

    /**
     * Send email to all contacts in $results list
     *
     * @param mixed $dsForm
     * @param mixed $results
     */
    function sendEmail($dsForm,
                       $results
    )
    {
        $senderEmail = $dsForm->getValue('fromEmailAddress');
        $senderName = 'CNC';

        $body = $dsForm->getValue('emailBody');
        $subject = $dsForm->getValue('emailSubject');
        /*
        Loop through contacts sending email to each
        */
        while ($row = $results->fetch_array(MYSQLI_ASSOC)) {

            $buMail = new BUMail($this);

            $toEmail = $row['EmailAddress'];

            $hdrs = array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => $subject,
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            // add name to top of email
            $thisBody = '<P>' . $row['FirstName'] . ",</P > " . $body;

            $buMail->mime->setHTMLBody($thisBody);

            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );

            $thisBody = $buMail->mime->get($mime_params);

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $thisBody
            );
            /*
            only send one (first) email if this is the dev system
            */
            if ($GLOBALS ['server_type'] != MAIN_CONFIG_SERVER_TYPE_LIVE) {
                break;
            }

        } // end while

    }
}// End of class
?>
