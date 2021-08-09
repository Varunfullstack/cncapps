<?php /**
 * Contact business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
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
    function search($dsSearchForm,
                    $contractItemIDs
    )
    {
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $dbeCustomer = new DBECustomer($this);
        $DBEContact  = new DBEContact($this);
        $dbeCustItem = new DBECustomerItem($this);
        $dbeSite     = new DBESite($this);
        $query       = "SELECT DISTINCT";
        if ($dsSearchForm->getValue(CTContactExport::searchFormExportEmailOnlyFlag) == 'Y') {
            $query .= " {$DBEContact->getDBColumnName(DBEContact::email)} AS EmailAddress";
        } else {
            $query .= " 
                {$DBEContact->getDBColumnName($DBEContact::customerID)} as CustomerID,
        {$DBEContact->getDBColumnName($DBEContact::title)} AS Title,
        {$DBEContact->getDBColumnName($DBEContact::lastName)} AS LastName,
        {$DBEContact->getDBColumnName($DBEContact::firstName)} AS FirstName,
        {$DBEContact->getDBColumnName($DBEContact::position)} AS Position,
        {$DBEContact->getDBColumnName($DBEContact::supportLevel)} as SupportLevel,
        {$dbeCustomer->getDBColumnName(DBECustomer::name)} AS Company,
        {$dbeSite->getDBColumnName(DBESite::add1)} AS BusinessStreet,
        {$dbeSite->getDBColumnName(DBESite::add2)} AS BusinessStreet2,
        {$dbeSite->getDBColumnName(DBESite::add3)}  AS BusinessStreet3,
        {$dbeSite->getDBColumnName(DBESite::town)} AS BusinessCity,
        {$dbeSite->getDBColumnName(DBESite::county)} AS BusinessState,
        {$dbeSite->getDBColumnName(DBESite::postcode)} AS BusinessPostalCode,
        if({$dbeSite->getDBColumnName(DBESite::phone)}, concat(\"'\",{$dbeSite->getDBColumnName(DBESite::phone)}),null) AS BusinessPhone,
        if({$DBEContact->getDBColumnName($DBEContact::phone)}, concat(\"'\",{$DBEContact->getDBColumnName($DBEContact::phone)}),null)  AS BusinessPhone2,
        if({$DBEContact->getDBColumnName($DBEContact::mobilePhone)}, concat(\"'\",{$DBEContact->getDBColumnName($DBEContact::mobilePhone)}), null) AS Mobile,
        {$DBEContact->getDBColumnName($DBEContact::email)} AS EmailAddress,
        CONCAT({$DBEContact->getDBColumnName($DBEContact::firstName)},' ',{$DBEContact->getDBColumnName($DBEContact::lastName)}) AS DisplayName,
        {$dbeCustomer->getDBColumnName(DBECustomer::becameCustomerDate)} is not null and {$dbeCustomer->getDBColumnName(DBECustomer::droppedCustomerDate)} is null AS Prospect";
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot)) {
                $query .= ", {$dbeCustomer->getDBColumnName(DBECustomer::mailshotFlag)} AS `Mailshot`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot2Flag)) {
                $query .= ", {$DBEContact->getDBColumnName($DBEContact::mailshot2Flag)} AS `" . $dsHeader->getValue(
                        DBEHeader::mailshot2FlagDesc
                    ) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot3Flag)) {
                $query .= ", {$DBEContact->getDBColumnName($DBEContact::mailshot3Flag)} AS `" . $dsHeader->getValue(
                        DBEHeader::mailshot3FlagDesc
                    ) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot8Flag)) {
                $query .= ", {$DBEContact->getDBColumnName($DBEContact::mailshot8Flag)} AS `" . $dsHeader->getValue(
                        DBEHeader::mailshot8FlagDesc
                    ) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot9Flag)) {
                $query .= ", {$DBEContact->getDBColumnName($DBEContact::mailshot9Flag)} AS `" . $dsHeader->getValue(
                        DBEHeader::mailshot9FlagDesc
                    ) . "`";
            }
            if ($dsSearchForm->getValue(CTContactExport::searchFormMailshot11Flag)) {
                $query .= ", {$DBEContact->getDBColumnName($DBEContact::mailshot11Flag)} AS `" . $dsHeader->getValue(
                        DBEHeader::mailshot11FlagDesc
                    ) . "`";
            }
            if ($dsSearchForm->getValue(DBEContact::hrUser)) {
                $query .= ", {$DBEContact->getDBColumnName($DBEContact::hrUser)} as HR";
            }
            if ($dsSearchForm->getValue(DBEContact::reviewUser)) {
                $query .= ", {$DBEContact->getDBColumnName($DBEContact::reviewUser)} as review";
            }
            $query .= ", active";

        }// end
        $query .= "
      FROM {$DBEContact->getTableName()}
      JOIN {$dbeSite->getTableName()} ON
        ({$DBEContact->getDBColumnName($DBEContact::siteNo)} = {$dbeSite->getDBColumnName(DBESite::siteNo)}  AND {$DBEContact->getDBColumnName($DBEContact::customerID)} = {$dbeSite->getDBColumnName(DBESite::customerID)})
      JOIN {$dbeCustomer->getTableName()} ON
        {$DBEContact->getDBColumnName($DBEContact::customerID)} = {$dbeCustomer->getDBColumnName(DBECustomer::customerID)}";
        if ($contractItemIDs) {

            $query .= " JOIN {$dbeCustItem->getTableName()}  ON {$dbeCustItem->getDBColumnName(DBECustomerItem::customerID)} = {$dbeCustomer->getDBColumnName(DBECustomer::customerID)}";
        }
        $query .= " WHERE 1 = 1 ";
        $query .= " and active =  " . ($dsSearchForm->getValue(DBEContact::active) ? '1' : '0');
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
                    $hasNone         = true;
                }
                if ($hasNone) {
                    if (count($selectedOptions)) {
                        $query .= " and ( {$DBEContact->getDBColumnName($DBEContact::supportLevel)} is null or {$DBEContact->getDBColumnName($DBEContact::supportLevel)} = '' or {$DBEContact->getDBColumnName($DBEContact::supportLevel)} in (" . implode(
                                ",",
                                $selectedOptions
                            ) . ")) ";
                    } else {
                        $query .= " and {$DBEContact->getDBColumnName($DBEContact::supportLevel)} is null";
                    }
                } else {
                    $query .= " and {$DBEContact->getDBColumnName($DBEContact::supportLevel)} in (" . implode(
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
            $query .= " AND  ( {$dbeCustItem->getDBColumnName(DBECustomerItem::declinedFlag)} = 'N' ";
            if ($searchCriteria === 'AND') {
                $query .= "AND {$dbeCustItem->getDBColumnName(DBECustomerItem::itemID)} IN(
                    " . implode(
                        ',',
                        $contractItemIDs
                    ) . "
                ))";
            } else {
                $query .= "and (" . implode(
                        ' or ',
                        array_map(
                            function ($contractItemID) use ($dbeCustItem) {
                                return " {$dbeCustItem->getDBColumnName(DBECustomerItem::itemID)} = $contractItemID ";
                            },
                            $contractItemIDs
                        )
                    ) . ") )";
            }
        }
        if ($dsSearchForm->getValue(CTContactExport::searchFormExportEmailOnlyFlag)) {
            $query .= " AND {$DBEContact->getDBColumnName($DBEContact::email)} <> '' and {$DBEContact->getDBColumnName($DBEContact::email)} is not null ";
        }
        $possibleOrQueries = "";
        if ($dsSearchForm->getValue(DBEContact::customerID)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= " {$dbeCustomer->getDBColumnName(DBECustomer::customerID)}=  {$dsSearchForm->getValue(DBEContact::customerID)} ";
        }
        if ($dsSearchForm->getValue(CTContactExport::searchFormProspectFlag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $dbeCustomer = new DBECustomer($this);
            $condition   = "  ( {$dbeCustomer->getDBColumnName(
                    DBECustomer::becameCustomerDate
                )} is null or {$dbeCustomer->getDBColumnName(DBECustomer::droppedCustomerDate)} is not null) ";
            if ($dsSearchForm->getValue(CTContactExport::searchFormProspectFlag) != 'Y') {
                $possibleOrQueries .= "  not {$condition}";
            } else {
                $possibleOrQueries .= $condition;
            }
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  {$dbeCustomer->getDBColumnName(DBECustomer::mailshotFlag)} =  'Y' ";
        }
        if ($dsSearchForm->getValue(CTContactExport::searchFormReferredFlag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $dbeCustomer = new DBECustomer($this);
            $condition   = "   {$dbeCustomer->getDBColumnName(DBECustomer::referredFlag)} = 1 ";
            if ($dsSearchForm->getValue(CTContactExport::searchFormReferredFlag) != 'Y') {
                $possibleOrQueries .= "  not {$condition}";
            } else {
                $possibleOrQueries .= $condition;
            }
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot2Flag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  {$DBEContact->getDBColumnName($DBEContact::mailshot2Flag)} =  'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot3Flag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  {$DBEContact->getDBColumnName($DBEContact::mailshot3Flag)} =  'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot8Flag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  {$DBEContact->getDBColumnName($DBEContact::mailshot8Flag)} =  'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot9Flag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  {$DBEContact->getDBColumnName($DBEContact::mailshot9Flag)} =  'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::mailshot11Flag)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= "  {$DBEContact->getDBColumnName($DBEContact::mailshot11Flag)} =  'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::hrUser)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= " {$DBEContact->getDBColumnName($DBEContact::hrUser)}  = 'Y' ";
        }
        if ($dsSearchForm->getValue(DBEContact::reviewUser)) {
            if (strlen($possibleOrQueries)) {
                $possibleOrQueries .= $searchCriteria;
            }
            $possibleOrQueries .= " {$DBEContact->getDBColumnName($DBEContact::reviewUser)} = 'Y' ";
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
        $body        = $dsForm->getValue(CTContactExport::searchFormEmailBody);
        $subject     = $dsForm->getValue(CTContactExport::searchFormEmailSubject);
        /*
        Loop through contacts sending email to each
        */
        while ($row = $results->fetch_array(MYSQLI_ASSOC)) {
            $buMail  = new BUMail($this);
            $toEmail = $row['EmailAddress'];
            $hdrs    = array(
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
            $thisBody    = $buMail->mime->get($mime_params);
            $hdrs        = $buMail->mime->headers($hdrs);
            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $thisBody
            );
        }
    }
}
