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
require_once($cfg['path_ct'] . '/CTContactExport.inc.php');

class BUContactExport extends Business
{
    /** @var */
    public $dbeContact;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    /**
     * @param DSForm $dsSearchForm
     * @param $quotationItemIDs
     * @param $contractItemIDs
     * @param $sectorIDs
     * @return bool|mysqli_result
     */
    function search(
        $dsSearchForm,
        $quotationItemIDs,
        $contractItemIDs,
        $sectorIDs
    )
    {
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);

        $query =
            "SELECT DISTINCT";

        if ($dsSearchForm->getValue(CTContactExport::searchFormExportEmailOnlyFlag)) {
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

            if ($dsSearchForm->getValue(CTContactExport::searchFormNoOfPCs)) {
                $query .= ", CONCAT( '\'', '" . $dsSearchForm->getValue(
                        CTContactExport::searchFormNoOfPCs
                    ) . "') AS `PCs`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormNoOfServers)) {
                $query .= ", '" . $dsSearchForm->getValue(CTContactExport::searchFormNoOfServers) . "' AS `Servers >=`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormSendMailshotFlag)) {
                $query .= ", 'Y' AS `Mailshot`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot2Flag)) {
                $query .= ", 'Y' AS `" . $dsHeader->getValue(DBEHeader::mailshot2FlagDesc) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot3Flag)) {
                $query .= ", 'Y' AS `" . $dsHeader->getValue(DBEHeader::mailshot3FlagDesc) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot4Flag)) {
                $query .= ", 'Y' AS `" . $dsHeader->getValue(DBEHeader::mailshot4FlagDesc) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot8Flag)) {
                $query .= ", 'Y' AS `" . $dsHeader->getValue(DBEHeader::mailshot8FlagDesc) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot9Flag)) {
                $query .= ", 'Y' AS `" . $dsHeader->getValue(DBEHeader::mailshot9FlagDesc) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormNewCustomerFromDate)) {
                $query .= ", '" . $dsSearchForm->getValue(
                        CTContactExport::searchFormNewCustomerFromDate
                    ) . "' AS `New Customer From`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormNewCustomerToDate)) {
                $query .= ", '" . $dsSearchForm->getValue(
                        CTContactExport::searchFormNewCustomerToDate
                    ) . "' AS `New Customer To`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormDroppedCustomerFromDate)) {
                $query .= ", '" . $dsSearchForm->getValue(
                        CTContactExport::searchFormDroppedCustomerFromDate
                    ) . "' AS `Lost Customer From`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormDroppedCustomerToDate)) {
                $query .= ", '" . $dsSearchForm->getValue(
                        CTContactExport::searchFormDroppedCustomerToDate
                    ) . "' AS `Lost Customer To`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormBroadbandRenewalFlag)) {
                $query .= ", 'Y' AS `Broadband Renewal`";

            }

            if ($dsSearchForm->getValue(DBEContact::hrUser)) {
                $query .= ", 'Y' as HR";
            }

            if ($dsSearchForm->getValue(DBEContact::reviewUser)) {
                $query .= ", 'Y' as review";
            }

            if ($dsSearchForm->getValue(CTContactExport::searchFormBroadbandIsp)) {
                $query .= ", '" . $dsSearchForm->getValue(
                        CTContactExport::searchFormBroadbandIsp
                    ) . "' AS `BroadbandIsp`";

            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormContractRenewalFlag)) {
                $query .= ", 'Y' AS `Contract Renewal`";

            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormQuotationRenewalFlag)) {
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

        if ($dsSearchForm->getValue(CTContactExport::searchFormContractRenewalFlag)) {
            $query .=
                " JOIN custitem cc ON cc.cui_custno = cus_custno";
        }
        if ($dsSearchForm->getValue(CTContactExport::searchFormQuotationRenewalFlag)) {
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

        if ($dsSearchForm->getValue(CTContactExport::searchFormBroadbandRenewalFlag)) {
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
        }

        if (
            $dsSearchForm->getValue(CTContactExport::searchFormBroadbandRenewalFlag) &&
            $dsSearchForm->getValue(CTContactExport::searchFormBroadbandIsp)
        ) {
            $query .= " AND lower(ispID) = lower('" . $dsSearchForm->getValue(
                    CTContactExport::searchFormBroadbandIsp
                ) . "')";
        }
        if ($dsSearchForm->getValue(CTContactExport::searchFormContractRenewalFlag)) {
            $query .= " AND declinedFlag = 'N'";
        }
        if ($dsSearchForm->getValue(CTContactExport::searchFormQuotationRenewalFlag)) {
            $query .= " AND declinedFlag = 'N'";
        }

        if ($dsSearchForm->getValue(CTContactExport::searchFormContractRenewalFlag) && $contractItemIDs) {
            $query .=
                " AND cui_itemno IN(
                        " . implode(
                    ',',
                    $contractItemIDs
                ) . "
                    )";
        }

        if ($dsSearchForm->getValue(CTContactExport::searchFormQuotationRenewalFlag) && $quotationItemIDs) {
            $query .=
                " AND cui_itemno IN(
                        " . implode(
                    ',',
                    $quotationItemIDs
                ) . "
                    )";
        }

        if ($dsSearchForm->getValue(CTContactExport::searchFormNewCustomerFromDate)) {
            $query .=
                " AND cus_became_customer_date >= '" . $dsSearchForm->getValue(
                    CTContactExport::searchFormNewCustomerFromDate
                ) . "'";
        }
        if ($dsSearchForm->getValue(CTContactExport::searchFormNewCustomerToDate)) {
            $query .=
                " AND cus_became_customer_date <= '" . $dsSearchForm->getValue(
                    CTContactExport::searchFormNewCustomerToDate
                ) . "'";
        }
        if ($dsSearchForm->getValue(CTContactExport::searchFormDroppedCustomerFromDate)) {
            $query .=
                " AND cus_dropped_customer_date >= '" . $dsSearchForm->getValue(
                    CTContactExport::searchFormDroppedCustomerFromDate
                ) . "'";
        }
        if ($dsSearchForm->getValue(CTContactExport::searchFormDroppedCustomerToDate)) {
            $query .=
                " AND cus_dropped_customer_date <= '" . $dsSearchForm->getValue(
                    CTContactExport::searchFormDroppedCustomerToDate
                ) . "'";
        }
        if ($dsSearchForm->getValue(CTContactExport::searchFormExportEmailOnlyFlag)) {
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
        $senderEmail = $dsForm->getValue(CTContactExport::searchFormFromEmailAddress);
        $body = $dsForm->getValue(CTContactExport::searchFormEmailBody);
        $subject = $dsForm->getValue(CTContactExport::searchFormEmailSubject);
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

        }
    }
}
