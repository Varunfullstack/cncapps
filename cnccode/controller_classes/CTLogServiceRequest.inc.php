<?php

/**
         * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Mustafa Taha
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEPendingReopened.php');

// Actions
class CTLogServiceRequest extends CTCNC
{
    function __construct(
        $requestMethod,
        $postVars,
        $getVars,
        $cookieVars,
        $cfg,
        $checkPermissions = true
    ) {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
      
        if ($checkPermissions) {

            $roles = [
                "technical",
            ];
            if (!self::hasPermissions($roles)) {
                Header("Location: /NotAllowed.php");
                exit;
            }
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case "searchCustomers": 
                echo json_encode($this->searchCustomers());
                exit;                
             break; 
             case "getCustomerSR": 
                echo json_encode($this->getCustomerSR());
                exit;                
             break;
             case "getCustomerSites": 
                echo json_encode($this->getCustomerSites());
                exit;                
             break;
             case "getCustomerAssets": 
                echo json_encode($this->getCustomerAssets());
                exit;                
             break;                                  
            default:
                $this->setTemplate();
                break;
        }
    }

    function setTemplate()
    {
        $this->setMethodName('setTemplate');
        $this->setMenuId(102);
        $action = $this->getAction();
        $this->setPageTitle('Log Service Request');
         

        $this->setTemplateFiles(
            'LogServiceRequest',
            'LogServiceRequest.inc'
        );

        $this->template->parse(
            'CONTENTS',
            'LogServiceRequest',
            true
        );
        $this->parsePage();
    }   
    function searchCustomers()
    {
        $q=$_GET["q"];
        if (!$db = mysqli_connect(
            DB_HOST,
            DB_USER,
            DB_PASSWORD
        )) {
            echo 'Could not connect to mysql host ' . DB_HOST;
            exit;
        }
       
        $query =
                "SELECT 
                cus_custno,
                cus_name,
                con_contno,
                add_town AS site_name,
                concat(contact.con_first_name,' ',contact.con_last_name) contact_name,                
                contact.con_first_name,
                contact.con_last_name,
                contact.con_phone,
                contact.con_notes,
                address.add_phone,
                supportLevel,
                con_position,
                cus_referred,
                specialAttentionContactFlag = 'Y' as specialAttentionContact,
                (
                SELECT
                    COUNT(*)
                FROM
                    problem
                WHERE
                    pro_custno = cus_custno
                    AND pro_status IN( 'I', 'P')
                ) AS openSrCount,
                (SELECT cui_itemno IS NOT NULL FROM custitem WHERE custitem.`cui_itemno` = 4111 AND custitem.`declinedFlag` <> 'Y' AND custitem.`cui_custno` = customer.`cus_custno` limit 1) AS hasPrepay,
                (SELECT item.`itm_desc` FROM custitem LEFT JOIN item ON cui_itemno = item.`itm_itemno` WHERE itm_desc LIKE '%servicedesk%' AND custitem.`declinedFlag` <> 'Y' AND custitem.`cui_custno` = customer.`cus_custno` limit 1 ) AS hasServiceDesk
            
                FROM customer
    
                JOIN contact ON con_custno = cus_custno
                JOIN address ON add_custno = cus_custno AND add_siteno = con_siteno
                WHERE supportLevel is not null ";
        if ($q&&$q!="") {
            mysqli_select_db(
                $db,
                DB_NAME
            );
            $query .= " AND (";
            $query .= " concat(con_first_name,' ',con_last_name) LIKE '%" . mysqli_real_escape_string(
                $db, $q) . "%' ";                         
            $query .= " OR customer.cus_name LIKE '%" . mysqli_real_escape_string(
                $db, $q) . "%' ";
            $query .= " OR customer.cus_custno LIKE '%" . mysqli_real_escape_string(
                    $db, $q) . "%' ";
            $query .=" ) ";
        }
        $query .= " and active 
                    ORDER BY cus_name, con_last_name, con_first_name 
                    ";
        // echo $query;
        // exit;
        $result = mysqli_query(
            $db,
            $query
        );
        return mysqli_fetch_all( $result,MYSQLI_ASSOC);
        
        
    }
    function getCustomerSR()
    {
        $buActivity = new BUActivity($this);
        $customerId=$_GET['customerId'];
        $contactId=$_GET['contactId'];
        $dsContactSrs = $buActivity->getProblemsByContact($contactId);
        $contactSR=array();
        while ($dsContactSrs->fetchNext()) {
            $urlProblemHistoryPopup =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'problemHistoryPopup',
                        'problemID' => $dsContactSrs->getValue(DBEJProblem::problemID),
                        'htmlFmt'   => CT_HTML_FMT_POPUP
                    )
                );
            array_push($contactSR,    
                array(
                    'contactProblemID'              => $dsContactSrs->getValue(DBEJProblem::problemID),
                    'contactDateRaised'             => Controller::dateYMDtoDMY(
                        $dsContactSrs->getValue(DBEJProblem::dateRaised)
                    ),
                    'contactReason'                 => self::truncate(
                        $dsContactSrs->getValue(DBEJProblem::reason),
                        100
                    ),
                    'contactLastReason'             => self::truncate(
                        $dsContactSrs->getValue(DBEJProblem::lastReason),
                        100
                    ),
                    'contactEngineerName'           => $dsContactSrs->getValue(DBEJProblem::engineerName),
                    'shouldBeHidden'                => $dsContactSrs->getValue(
                        DBEJProblem::status
                    ) == 'C' ? 'hidden' : null,
                    'contactActivityID'             => $dsContactSrs->getValue(DBEJProblem::lastCallActivityID),
                    'contactUrlProblemHistoryPopup' => $urlProblemHistoryPopup,
                    'contactPriority'               => $dsContactSrs->getValue(DBEJProblem::priority),
                    'contactPriorityClass'          => $dsContactSrs->getValue(
                        DBEJProblem::priority
                    ) == 1 ? 'redRow' : null,
                    "status"=>$dsContactSrs->getValue(DBEJProblem::status),
                )
            );

           

        }
        $dsActiveSrs = $buActivity->getActiveProblemsByCustomer($customerId);
        $customerSR=array();
        while ($dsActiveSrs->fetchNext()) {

            $urlCreateFollowOn =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'         => 'createFollowOnActivity',
                        'callActivityID' => $dsActiveSrs->getValue(DBEJProblem::lastCallActivityID),
                        'reason'         => $this->getParam('reason')
                    )
                );

            $urlProblemHistoryPopup =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'problemHistoryPopup',
                        'problemID' => $dsActiveSrs->getValue(DBEJProblem::problemID),
                        'htmlFmt'   => CT_HTML_FMT_POPUP
                    )
                );
            array_push(
                $customerSR,
                array(
                    'problemID'              => $dsActiveSrs->getValue(DBEJProblem::problemID),
                    'dateRaised'             => Controller::dateYMDtoDMY(
                        $dsActiveSrs->getValue(DBEJProblem::dateRaised)
                    ),
                    'reason'                 => self::truncate(
                        $dsActiveSrs->getValue(DBEJProblem::reason),
                        100
                    ),
                    'lastReason'             => self::truncate(
                        $dsActiveSrs->getValue(DBEJProblem::lastReason),
                        100
                    ),
                    'engineerName'           => $dsActiveSrs->getValue(DBEJProblem::engineerName),
                    'activityID'             => $dsActiveSrs->getValue(DBEJProblem::lastCallActivityID),
                    'urlProblemHistoryPopup' => $urlProblemHistoryPopup,
                    'priority'               => $dsActiveSrs->getValue(DBEJProblem::priority),
                    'priorityClass'          => $dsActiveSrs->getValue(
                        DBEJProblem::priority
                    ) == 1 ? 'class="redRow"' : null
                )
            );
        }
         return  ["contactSR"=>$contactSR,"customerSR"=> $customerSR];
    }
    function getCustomerSites()
    {
        $customerId = $_GET["customerId"];
        if (!$customerId)
            return [];
        $dbeSite = new DBESite($this);
        $dbeSite->setValue(
            DBESite::customerID,
            $customerId
        );
        $dbeSite->getRowsByCustomerID();
        $sites=array();
        while ($dbeSite->fetchNext()) {
            $siteDesc = $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                DBESite::town
            ) . ' ' . $dbeSite->getValue(DBESite::postcode);
            $siteNo = $dbeSite->getValue(DBESite::siteNo);
            array_push( $sites,["id"=>$siteNo,"title"=>$siteDesc]);
        }
        return $sites;
    }
    function getLabtechDB()
    {
        $dsn = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
        $options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        ];
        $labtechDB = new PDO(
            $dsn,
            LABTECH_DB_USERNAME,
            LABTECH_DB_PASSWORD,
            $options
        );
        return  $labtechDB;
    }
    function getCustomerAssets()
    {
        $customerId=$_GET["customerId"];
        $labtechDB= $this->getLabtechDB();
        $query="select  computers.name,computers.assetTag  from computers 
        join clients on 
            computers.clientid = clients.clientid
            and clients.externalID = $customerId          
        ";
        $statement = $labtechDB->prepare($query);
        $test = $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
}
