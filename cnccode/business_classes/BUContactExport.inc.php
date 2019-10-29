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
     * @param $contractItemIDs
     * @return bool|mysqli_result
     */
    function search(
        $dsSearchForm,
        $contractItemIDs
    )
    {
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);

        $query =
            "SELECT DISTINCT";

        if ($dsSearchForm->getValue(CTContactExport::searchFormExportEmailOnlyFlag) == 'Y') {
            $query .= " con_email AS EmailAddress";
        } else {
            $query .=
                " con_custno as CustomerID,
        con_title AS Title,
        con_last_name AS LastName,
        con_first_name AS FirstName,
        con_position AS Position,
        supportLevel as SupportLevel,
        cus_name AS Company,
        add_add1 AS BusinessStreet,
        add_add2 AS BusinessStreet2,
        add_add3  AS BusinessStreet3,
        add_town AS BusinessCity,
        add_county AS BusinessState,
        add_postcode AS BusinessPostalCode,
        add_phone AS BusinessPhone,
        if(con_phone, concat(\"=\",\"\"\"\"\"\",con_phone,\"\"\"\"\"\"),null)  AS BusinessPhone2,
        if(con_mobile_phone, concat(\"=\",\"\"\"\"\"\",con_mobile_phone,\"\"\"\"\"\"), null) AS Mobile,
        con_email AS EmailAddress,
        CONCAT(con_first_name,' ',con_last_name) AS DisplayName,
        cus_prospect AS Prospect";


            if ($dsSearchForm->getValue(CTContactExport::searchFormSendMailshotFlag)) {
                $query .= ", cus_mailshot AS `Mailshot`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot2Flag)) {
                $query .= ", con_mailflag2 AS `" . $dsHeader->getValue(DBEHeader::mailshot2FlagDesc) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot3Flag)) {
                $query .= ", con_mailflag3 AS `" . $dsHeader->getValue(DBEHeader::mailshot3FlagDesc) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot4Flag)) {
                $query .= ", con_mailflag4 AS `" . $dsHeader->getValue(DBEHeader::mailshot4FlagDesc) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot8Flag)) {
                $query .= ", con_mailflag8 AS `" . $dsHeader->getValue(DBEHeader::mailshot8FlagDesc) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot9Flag)) {
                $query .= ", con_mailflag9 AS `" . $dsHeader->getValue(DBEHeader::mailshot9FlagDesc) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot11Flag)) {
                $query .= ", con_mailflag11 AS `" . $dsHeader->getValue(DBEHeader::mailshot11FlagDesc) . "`";
            }
            if ($dsSearchForm->getValue(DBEContact::hrUser)) {
                $query .= ", hrUser as HR";
            }

            if ($dsSearchForm->getValue(DBEContact::reviewUser)) {
                $query .= ", reviewUser as review";
            }
        }// end

        $query .= "
      FROM contact
      JOIN address ON
        (con_siteno = add_siteno  AND con_custno = add_custno)
      JOIN customer ON
        con_custno = cus_custno";

        if ($contractItemIDs) {
            $query .=
                " JOIN custitem cc ON cc.cui_custno = cus_custno";
        }

        $query .= " WHERE 1 = 1 ";

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

        $searchCriteria = $dsSearchForm->getValue(CTContactExport::searchCriteria);
        if ($contractItemIDs) {
            $query .=
                " AND  ( declinedFlag = 'N' ";
            if ($searchCriteria === 'AND') {
                $query .= "AND cui_itemno IN(
                    " . implode(
                        ',',
                        $contractItemIDs
                    ) . "
                ))";
            } else {
                $query .= "and (" . implode(
                        ' or ',
                        array_map(
                            function ($contractItemID) {
                                return " cui_itemno = $contractItemID ";
                            },
                            $contractItemIDs
                        )
                    ) . ") )";
            }
        }

        if ($dsSearchForm->getValue(CTContactExport::searchFormExportEmailOnlyFlag)) {
            $query .= " AND con_email <> '' and con_email is not null ";
        }

        $possibleOrQueries = "";

        if ($dsSearchForm->getValue(DBEContact::customerID)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= " cus_custno =  " . $dsSearchForm->getValue(DBEContact::customerID) . " ";
        }
        if ($dsSearchForm->getValue(DBECustomer::prospectFlag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  cus_prospect =  '" . $dsSearchForm->getValue(
                    DBECustomer::prospectFlag
                ) . "' ";
        }

        if ($dsSearchForm->getValue(DBEContact::sendMailshotFlag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  cus_mailshot =  'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot2Flag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  con_mailflag2 =  'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot3Flag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  con_mailflag3 =  'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot4Flag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  con_mailflag4 =  'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot8Flag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  con_mailflag8 =  'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot9Flag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  con_mailflag9 =  'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot11Flag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  con_mailflag11 =  'Y' ";
        }

        if ($dsSearchForm->getValue(DBEContact::hrUser)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  " . DBEContact::hrUser . " = 'Y' ";
        }

        if ($dsSearchForm->getValue(DBEContact::reviewUser)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= " " . DBEContact::reviewUser . " = 'Y' ";
        }

        if (strlen($possibleOrQueries)) {
            $query .= " and (" . $possibleOrQueries . ")";
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
            $body = str_replace('[%ContactFirstName%]', $row['FirstName'], $body);
            $body = str_replace('[%ContactLastName%]', $row['LastName'], $body);
            $buMail->mime->setHTMLBody($body);

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
        }
    }
}
