<?php 
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBECallActivity.inc.php');
require_once($cfg['path_dbe'] . '/DBEJCallActivity.php');

require_once($cfg['path_dbe'] . '/DBEProblem.inc.php');
require_once($cfg['path_dbe'] . '/DBEProblemRaiseType.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBEContact.inc.php');
require_once($cfg['path_dbe'] . '/DBESite.inc.php');
require_once($cfg['path_dbe'] . '/DBEHeader.inc.php');
require_once($cfg['path_dbe'] . '/DBEProblemNotStartReason.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUExpenseType.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_bu'] . '/BURootCause.inc.php');
require_once($cfg['path_bu'] . '/BUActivityType.inc.php');
require_once($cfg['path_dbe'] . '/DBEJCallActType.php');
require_once($cfg['path_dbe'] . '/DBEJCallActivity.php');

class CTSRActivity extends CTCNC
{
    const GREEN = '#BDF8BA';
    const CONTENT = '#F4f4f2';
    public $serverGuardArray =
    array(
        ""  => "Please select",
        "Y" => "ServerGuard Related",
        "N" => "Not ServerGuard Related"
    );
    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = [
            SALES_PERMISSION,
            ACCOUNTS_PERMISSION,
            TECHNICAL_PERMISSION,
            SUPERVISOR_PERMISSION,
            REPORTS_PERMISSION,
            MAINTENANCE_PERMISSION,
            RENEWALS_PERMISSION,
        ];

        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {            
            case "getCallActivity":
                echo json_encode($this->getActivityDetails());
                exit;
            break;   
            case "messageToSales":
                echo json_encode($this->messageToSales());
                exit;
            break;   
            case "updateActivity":
                echo json_encode($this->updateCallActivity());
                exit;
            break;            
            case "getCustomerContacts":
                echo json_encode($this->getCustomerContacts());
                exit;
            case "getCustomerSites":
                echo json_encode($this->getCustomerSites());
                exit;
            case "getPriorities":
                echo json_encode($this->getPriorities());
                exit;
            
                exit;
            case "getCustomerContracts":
                echo json_encode($this->getCustomerContracts());
                exit;
            case "getRootCauses":
                echo json_encode($this->getRootCauses());
                exit;
            case "createProblem":
                echo json_encode($this->addNewSR());
                exit;
            case "getCallActivityType":
                echo json_encode($this->getCallActivityType());
                exit;
            case "getCustomerRaisedRequest":
                echo json_encode($this->getCustomerRaisedRequest());
                exit;
            case "getCallActivityBasicInfo":
                echo json_encode($this->getCallActivityBasicInfo());
                exit;
            case "getDocuments":
                echo json_encode($this->getActivityDocuments($_REQUEST["callActivityID"],$_REQUEST["problemID"]));
                exit;
            case "saveFixedInformation":
                echo json_encode($this->saveFixedInformation());
                exit;
            case "getInitialActivity":
                echo json_encode($this->getInitialActivity());
                exit;
            case "saveManagementReviewDetails":
                echo json_encode($this->saveManagementReviewDetails());
                exit;
            case "changeProblemPriority":
                echo json_encode($this->changeProblemPriority());
                exit;
            default:
           
            $this->setTemplate();
            break;
        }
        
    }
    function setTemplate()
    {
        $this->setMethodName('setTemplate');
        $this->setMenuId(102);        
        $title=$this->getTitle();
        //echo explode('<',$title)[1]; exit;
        $this->setPageTitle($title);
        $this->setTemplateFiles(
            'Activity',
            'Activity.inc'
        );

        $this->template->parse(
            'CONTENTS',
            'Activity',
            true
        );
        $this->parsePage();
    } 
    function getTitle()
    {
        $action = $this->getAction();
        if(isset($_REQUEST['callActivityID']))
        {
            $dbeCallActivity = new DBECallActivity($this);
            $callActivityID=$_REQUEST['callActivityID'];
            if(isset($callActivityID))
            {
                $dbeCallActivity->setPKValue($callActivityID);
                $dbeCallActivity->getRow();
                $problemID=$dbeCallActivity->getValue(DBECallActivity::problemID);
                $dbeProblem =new DBEProblem($this);
                $dbeProblem->setPKValue($problemID);
                $dbeProblem->getRow();
                
            }
        }

        switch($action)
        {
            case "displayActivity":
                return "Service Request " . $problemID . $this->getProblemRaiseIcon($dbeProblem);
                break;
            case "editActivity":
                return "Edit Service Request " . $problemID . $this->getProblemRaiseIcon($dbeProblem);
                break;
            case "gatherFixedInformation":
                return "Service Request Fix Summary " . $problemID . $this->getProblemRaiseIcon($dbeProblem);
                break;
            case "gatherManagementReviewDetails":
                return "Management Review Reason";
            default:
                return 'Activity';
                break;
        }
        return 'Activity';
    }
    private function getProblemRaiseIcon($dbeJProblem)
    {
        
        if (isset($dbeJProblem)) {
            $raiseTypeId = $dbeJProblem->getValue(DBEProblem::raiseTypeId);
            if (isset($raiseTypeId) && $raiseTypeId != null) {
                $dbeProblemRaiseType = new  DBEProblemRaiseType($this);
                $dbeProblemRaiseType->setPKValue($raiseTypeId);
                $dbeProblemRaiseType->getRow();
                $return = "<div style='font-size: 14px;font-weight: 100; display:inline-block'>
                  <div class='tooltip' > ";
                $title="";
                switch ($dbeProblemRaiseType->getValue(DBEProblemRaiseType::description)) {
                    case 'Email':
                        $return .= "<i class='fal fa-envelope ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title="This Service Request was raised by email";
                        break;
                    case 'Portal':
                        //$return .=  "<i class='fab fa-edge ml-5 pointer' style='font-size: 18px;' ></i>";
                        $return .=  "<i class='icon-chrome_icon' style='font-size: 18px; margin:5px; color:#000080 ' ></i>";
                        $title="This Service Request was raised by the portal";
                        break;
                    case 'Phone':
                        $return .=  "<i class='fal fa-phone ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title="This Service Request was raised by phone";
                        break;
                    case 'On site':
                        $return .=  "<i class='fal fa-building ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title="This Service Request was raised by an on site engineer";
                        break;
                    case 'Alert':
                        $return .=  "<i class='fal fa-bell ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title="This Service Request was raised by an alert";
                        break;
                    case 'Sales':
                        $return .=  "<i class='fal fa-shopping-cart ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title="This Service Request was raised via Sales";
                        break;
                    case 'Manual':
                        $return .=  "<i class='fal fa-user-edit ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title="This Service Request was raised manually";
                        break;
                }
                $return =$return."<div class='tooltiptext tooltip-bottom' style='width:300px' >$title</div> </div> ";
                return $return;
            }
        } else return null;
    }
    private function getActivityDetails()
    {
        $callActivityID=$_REQUEST["callActivityID"];
        //get call activity
        
        $dbejCallActivity=new DBEJCallActivity($this);
        $dbeProblem=new DBEProblem($this);
        $dbejCallActivity->setPKValue($callActivityID);
        $dbejCallActivity->getRow();
        $problemID=$dbejCallActivity->getValue(DBECallActivity::problemID);
        $dbeProblem->setPKValue($problemID);
        $dbeProblem->getRow();
        $customerId= $dbejCallActivity->getValue(DBEJCallActivity::customerID);
        $contactID= $dbejCallActivity->getValue(DBEJCallActivity::contactID);
        $siteId=$dbejCallActivity->getValue(DBEJCallActivity::siteNo);
        $projectLink=BUProject::getCurrentProjectLink(
            $customerId
        );
        $dbeCustomer=new DBECustomer($this);
        $dbeCustomer->setPKValue( $customerId);
        $dbeCustomer->getRow();        
        $customerNameDisplayClass=$this->getCustomerNameDisplayClass($dbeCustomer);
        $dbeContact=new DBEContact($this);
        $dbeContact->setPKValue($contactID);
        $dbeContact->getRow();
        $dbeSite=new DBESite($this);
        $dbeSite->setPKValue( $siteId);
        $dbeSite->setValue(DBESite::customerID,$customerId);
        $dbeSite->getRowByCustomerIDSiteNo();
        $buActivity=new BUActivity($this);
        $buUser = new BUUser($this);
        $dbeLastActivity = $buActivity->getLastActivityInProblem($problemID);

        if (
            $dbeLastActivity->getValue(DBEJCallActivity::callActTypeID) == 0 &&
            $dbeLastActivity->getValue(DBEJCallActivity::userID) != $GLOBALS['auth']->is_authenticated()
        ) {
            $currentUserBgColor = self::GREEN;
            $currentUser = $dbeLastActivity->getValue(DBEJCallActivity::userName) . ' Is Adding New Activity To This Request Now';
        } else {
            $currentUserBgColor = self::CONTENT;
            $currentUser = null;
        }
        $expenses=$this->getActivityExpenses($callActivityID);
        $dbeUser = $this->getDbeUser();
        $dbeUser->setValue(
            DBEUser::userID,
            $this->userID
        );
        $dbeUser->getRow();
        $dbeUserActivity=new DBEUser($this);
        $dbeUserActivity->getRow($dbejCallActivity->getValue(DBEJCallActivity::userID));
        $hdAssignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
        $esAssignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
        $imAssignedMinutes = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
        $projectTeamAssignedMinutes = $dbeProblem->getValue(DBEProblem::projectTeamLimitMinutes);
        $projectUsedMinutes = $buActivity->getUsedTimeForProblemAndTeam($problemID,5);
        $hdUsedMinutes = $buActivity->getHDTeamUsedTime($problemID);
        $esUsedMinutes = $buActivity->getESTeamUsedTime($problemID);
        $imUsedMinutes = $buActivity->getSPTeamUsedTime($problemID);
        return [
            "callActivityID"=> $callActivityID,
            "problemID"=>$problemID,
            "projectLink"=>$projectLink,
            "customerNameDisplayClass"=>$customerNameDisplayClass,
            'customerId' =>  $customerId,
            "customerName"=>$dbeCustomer->getValue(DBECustomer::name),
            "contactID"=>$contactID,
            "contactPhone"=>$dbeContact->getValue(DBEContact::phone),            
            "contactName"=>$dbeContact->getValue(DBEContact::firstName)." ".$dbeContact->getValue(DBEContact::lastName),
            "contactMobilePhone"=>$dbeContact->getValue(DBEContact::mobilePhone),
            "contactEmail"=>$dbeContact->getValue(DBEContact::email),
            "siteNo"=>$dbeSite->getValue(DBESite::siteNo),
            "sitePhone"=>$dbeSite->getValue(DBESite::phone),
            "siteAdd1"=>$dbeSite->getValue(DBESite::add1),
            "siteAdd2"=>$dbeSite->getValue(DBESite::add2),
            "siteAdd3"=>$dbeSite->getValue(DBESite::add3),
            "siteTown"=>$dbeSite->getValue(DBESite::town),
            "sitePostcode"=>$dbeSite->getValue(DBESite::postcode),            
            "linkedSalesOrderID"=>$dbejCallActivity->getValue(DBEJCallActivity::linkedSalesOrderID),
            "activities"=>$this->getOtherActivity($problemID),
            'criticalFlag'=> $dbejCallActivity->getValue(
                DBEJCallActivity::criticalFlag
            ) == 'Y' ? 1 : 0,
            'monitoringFlag'                    => $this->checkMonitoring($problemID) ? 1 : 0,
            "totalActivityDurationHours"        =>$dbeProblem->getValue(DBEProblem::totalActivityDurationHours),
            "chargeableActivityDurationHours"   => $dbeProblem->getValue(DBEProblem::chargeableActivityDurationHours),
            "onSiteActivities"                  =>$this->getOnSiteActivity($callActivityID),
            "problemStatus"                     => $dbejCallActivity->getValue(DBEJCallActivity::problemStatus),
            "serverGuard"                       =>  $dbejCallActivity->getValue(DBEJCallActivity::serverGuard),
            "problemHideFromCustomerFlag"       =>$dbeProblem->getValue(DBEProblem::hideFromCustomerFlag),
            "canEdit"                           =>$buActivity->checkActivityEditionByProblem($dbejCallActivity,$this,$dbeProblem),
            "canDelete"                         =>!$dbejCallActivity->getValue(DBEJCallActivity::endTime) || 
                                                    ($dbejCallActivity->getValue(DBEJCallActivity::status) != 'A' && 
                                                    $this->hasPermissions(MAINTENANCE_PERMISSION)),
            "hasExpenses"                       =>count($expenses)?true:false,
            "isSDManger"                        =>$buUser->isSdManager($this->userID),
            "hideFromCustomerFlag"              =>$dbejCallActivity->getValue(DBEJCallActivity::hideFromCustomerFlag),
            "allowSCRFlag"                      =>$dbejCallActivity->getValue(DBEJCallActivity::allowSCRFlag),
            "priority"                          => $buActivity->priorityArray[$dbejCallActivity->getValue(DBEJCallActivity::priority)],
            "problemStatusDetials"              =>$buActivity->problemStatusArray[$dbeProblem->getValue(DBEProblem::status)],
            "awaitingCustomerResponseFlag"      =>$dbeProblem->getValue(DBEProblem::awaitingCustomerResponseFlag),
            "activityType"                      =>$dbejCallActivity->getValue(DBEJCallActivity::activityType),
            "authorisedBy"                      =>$this->getAuthorisedBy($dbeProblem),
            "engineerName"                      =>$dbejCallActivity->getValue(DBEJCallActivity::userName),
            "contractType"                      =>$this->getContractType($dbejCallActivity),
            "curValue"                          =>$dbejCallActivity->getValue(DBEJCallActivity::curValue),
            "serverGuardDetials"                =>$this->serverGuardArray[$dbejCallActivity->getValue(DBEJCallActivity::serverGuard)],
            "date"                              =>$dbejCallActivity->getValue(DBEJCallActivity::date),
            "projectDescription"                =>$dbejCallActivity->getValue(DBEJCallActivity::projectDescription),
            "startTime"                         =>$dbejCallActivity->getValue(DBEJCallActivity::startTime),
            "endTime"                           =>$dbejCallActivity->getValue(DBEJCallActivity::endTime),
            "rootCauseDescription"              =>$dbejCallActivity->getValue(DBEJCallActivity::rootCauseDescription),
            "completeDate"                      =>$dbejCallActivity->getValue(DBEJCallActivity::completeDate),
            "reason"                            =>$dbejCallActivity->getValue(DBEJCallActivity::reason),
            "internalNotes"                     =>$dbejCallActivity->getValue(DBEJCallActivity::internalNotes),
            "currentUser"                       =>$currentUser ,
            "currentUserBgColor"                =>$currentUserBgColor,
            "documents"                         =>$this->getActivityDocuments($callActivityID,$problemID),
            "expenses"                          =>$expenses,
            "partsUsed"                         =>null,
            'disabledChangeRequest'             => $dbeProblem->getValue(DBEProblem::status) == 'P' ? '' : 'disabled',
            'contactNotes'                      => $dbejCallActivity->getValue(DBEJCallActivity::contactNotes),            
            'techNotes'                         => $dbejCallActivity->getValue(DBEJCallActivity::techNotes),
            "callActTypeID"                     => $dbejCallActivity->getValue(DBEJCallActivity::callActTypeID),
            'alarmDate'                         => $dbejCallActivity->getValue(DBEJCallActivity::alarmDate),
            'alarmTime'                         => $dbejCallActivity->getValue(DBEJCallActivity::alarmTime),
            'alarmDateMessage'                  => $dbejCallActivity->getValue(DBEJCallActivity::alarmDate),            
            'alarmTimeMessage'                  => $dbejCallActivity->getValue(DBEJCallActivity::alarmTime),
            'canChangeInitialDateAndTime'       =>$dbeUser->getValue(DBEUser::changeInitialDateAndTimeFlag) == 'Y'?true:false,
            "isInitalDisabled"                  =>$this->isInitalDisabled($dbejCallActivity),
            'contactSupportLevel'               => $dbeContact->getValue(DBEContact::supportLevel),
            'hdRemainMinutes'                => $hdAssignedMinutes - $hdUsedMinutes,
            'esRemainMinutes'                => $esAssignedMinutes - $esUsedMinutes,
            'imRemainMinutes'                => $imAssignedMinutes - $imUsedMinutes,
            'projectRemainMinutes'           => $projectTeamAssignedMinutes - $projectUsedMinutes,
            "canChangePriorityFlag"          =>$dbeUser->getValue(DBEUser::changePriorityFlag) == 'Y'?true:false,
            "userID"                            => $dbejCallActivity->getValue(DBEJCallActivity::userID),
            "actUserTeamId"                       => $dbeUserActivity->getValue(DBEUser::teamID),
            "contractCustomerItemID"            => $dbejCallActivity->getValue(DBEJCallActivity::contractCustomerItemID),
            "changeSRContractsFlag"             => $dbeUser->getValue(DBEUser::changeSRContractsFlag) == 'Y'?true:false,
            "rootCauseID"                       => $dbejCallActivity->getValue(DBEJCallActivity::rootCauseID),
            'submitAsOvertime'                  => $dbejCallActivity->getValue(DBECallActivity::submitAsOvertime),
            "siteMaxTravelHours"                =>$dbeSite->getValue(DBESite::maxTravelHours),
            "projectId"                         =>$dbejCallActivity->getValue(DBEJCallActivity::projectID),
            "projects"                          =>BUProject::getCustomerProjects($customerId),
            "cncNextAction"                     =>$dbejCallActivity->getValue(DBEJCallActivity::cncNextAction),
            "customerNotes"                     =>$dbejCallActivity->getValue(DBEJCallActivity::customerNotes),
            'activityTypeHasExpenses'           =>BUActivityType::hasExpenses($dbejCallActivity->getValue(DBEJCallActivity::callActTypeID)),
            'assetName'                         =>$dbeProblem->getValue(DBEProblem::assetName),
            'assetTitle'                        =>$dbeProblem->getValue(DBEProblem::assetTitle),
            "emptyAssetReason"                  =>$dbeProblem->getValue(DBEProblem::emptyAssetReason),
        ];
    }
    /**
     * @param DBEJCallActivity  $dbejCallActivity
     * @return Boolean
     */
    function isInitalDisabled($dbejCallActivity)
    {
        $initial_disabled = false;        
        if (
        in_array(
            $dbejCallActivity->getValue(DBEJCallActivity::callActTypeID),
            array(
                CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID
            )
        )
        ) {
            if (!$this->hasPermissions(MAINTENANCE_PERMISSION)) {
                $initial_disabled = true;                
            } 
        }
        return $initial_disabled ;
    }
     /**
     * @param DataSet|DBECustomer $dsCustomer
     * @return string
     */
    function getCustomerNameDisplayClass($dsCustomer)
    {
        if (
            $dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &&
            $dsCustomer->getValue(DBECustomer::specialAttentionEndDate) >= date('Y-m-d')
        ) {
            return 'specialAttentionCustomer';
        }

        return null;
    }
    /**
     * get list of other activities in this problem or project
     */
    function getOtherActivity($problemID){
        $includeTravel =$_REQUEST["includeTravel"]=='true'?true:false;
        $includeOperationalTasks =$_REQUEST["includeOperationalTasks"]=='true'?true:false;
        $includeServerGuardUpdates =$_REQUEST["includeServerGuardUpdates"]=='true'?true:false;
        $dbejCallActivity= new DBEJCallActivity($this);
        $activities=array();
        $dbejCallActivity->getRowsByproblemID(
            $problemID,
            $includeTravel,
            $includeOperationalTasks,
            false,
            false,
            $includeServerGuardUpdates
        );
        while($dbejCallActivity->fetchNext())
        {
            array_push($activities,[
                "callActivityID"    =>$dbejCallActivity->getValue(DBEJCallActivity::callActivityID),
                "dateEngineer"      =>$dbejCallActivity->getValue(DBEJCallActivity::dateEngineer),                
                "contactName"       =>$dbejCallActivity->getValue(DBEJCallActivity::contactName),
                "activityType"      =>$dbejCallActivity->getValue(DBEJCallActivity::activityType),  
                "date"              =>$dbejCallActivity->getValue(DBEJCallActivity::date),  
                "startTime"         =>$dbejCallActivity->getValue(DBEJCallActivity::startTime),  
                ]
        );
        }
        return  $activities;
    }
    private function checkMonitoring($problemID)
    {
        $buActivity=new BUActivity($this);
        return $buActivity->checkMonitoringFlag($problemID);
    }
    private function getOnSiteActivity($callActivityID)
    {
        $buActivity=new BUActivity($this);
        $db = $buActivity->getOnSiteActivitiesWithinFiveDaysOfActivity($callActivityID);      
        $activities=array();
        while($db->next_record())
        {
            array_push($activities,[
                "callActivityID"=>$db->Record['caa_callactivityno'],
                "problemno"=>$db->Record['caa_problemno'],
                "title"=>$db->Record['cns_name'].' on '.$db->Record['formattedDate'].' ('.$db->Record['caa_problemno'].')',
                
                ]);
        }
        return $activities;
    }  
    private function getAuthorisedBy($dbeJProblem)
    {
        $authorisedByName="";
        if ((int)$dbeJProblem->getValue(DBEProblem::authorisedBy)) {
            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($dbeJProblem->getValue(DBEProblem::authorisedBy));
            $authorisedByName = $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                    DBEContact::lastName
                );                
        }
        return $authorisedByName;
    }
    private function getContractType($dbejCallActivity)
    {
        $contractDescription = 'T & M';
        if ($dbejCallActivity->getValue(DBEJCallActivity::contractCustomerItemID)) {
            $dbeContract = new DBEJContract($this);
            $dbeContract->getRowByContractID($dbejCallActivity->getValue(DBEJCallActivity::contractCustomerItemID));
            $contractDescription = Controller::htmlDisplayText(
                $description = $dbeContract->getValue(DBEJContract::itemDescription) . ' ' . $dbeContract->getValue(
                        DBEJContract::adslPhone
                    ) . ' ' . $dbeContract->getValue(DBEJContract::notes) . ' ' . $dbeContract->getValue(
                        DBEJContract::postcode
                    )
            );
        }  
        return $contractDescription;
    }
    /**
     * Documents display and upload
     *
     * @param $callActivityID
     * @param $problemID     
     * @throws Exception
     */
    function getActivityDocuments($callActivityID,
                       $problemID              
    )
    {
        
        $dbeJCallDocument = new DBEJCallDocument($this);
        $dbeJCallDocument->setValue(
            DBEJCallDocument::problemID,
            $problemID
        );
        $dbeJCallDocument->getRowsByColumn(DBEJCallDocument::problemID);
        $documents=array();
        while ($dbeJCallDocument->fetchNext()) {  
            array_push($documents,            
                array(
                    'id'             =>$dbeJCallDocument->getValue(DBEJCallDocument::callDocumentID),
                    'description'    => $dbeJCallDocument->getValue(DBEJCallDocument::description),
                    'filename'       => $dbeJCallDocument->getValue(DBEJCallDocument::filename),
                    'createUserName' => $dbeJCallDocument->getValue(DBEJCallDocument::createUserName),
                    'createDate'     => $dbeJCallDocument->getValue(DBEJCallDocument::createDate),                                        
                )
            );           
        }
    return $documents;
    }
    private function getActivityExpenses($callActivityID)
    {
        $buExpense = new BUExpense($this);
        $dsExpense = new DataSet($this);
        $buExpense->getExpensesBycallActivityID(
            $callActivityID,
            $dsExpense
        );
        $expenses=array();
        if ($dsExpense->rowCount() > 0) {
             
            while ($dsExpense->fetchNext()) {

                $expenseID = $dsExpense->getValue(DBEJExpense::expenseID);
                array_push($expenses,
                    array(
                        'id'   => $expenseID,
                        'expenseType' =>$dsExpense->getValue(DBEJExpense::expenseType),
                        'mileage'     => $dsExpense->getValue(DBEJExpense::mileage),
                        'value'       => $dsExpense->getValue(DBEJExpense::value),
                        'vatFlag'     => $dsExpense->getValue(DBEJExpense::vatFlag)
                    )
                );
            }
        }
        return  $expenses;
    }
    function messageToSales()
    {
        try {
            $buActivity = new BUActivity($this);
            $this->setMethodName('messageToSales');
            $body = file_get_contents('php://input');
            $body = json_decode($body);
            $message = $body->message;
            $callActivityID = $body->callActivityID;
            if (!$callActivityID) {
                http_response_code(400);
                return ['error' => true, 'errorDescription' => "callActivityID is missing"];
            }

            $buActivity->sendEmailToSales(
                $callActivityID,
                $message
            );
            return ["status" => "ok"];
        } catch (Exception $ex) {
            http_response_code(400);
            return ['error' => true, 'errorDescription' => $ex->message];
        }
    }
   
    function validTime($body, $dbeProblem,$buActivity,$dbeCallActivity)
    {
        $problemID=$dbeCallActivity->getValue(DBECallActivity::problemID);
        $callActivityID=$dbeCallActivity->getValue(DBECallActivity::callActivityID);
        $durationHours = common_convertHHMMToDecimal(
            $body->endTime
        ) - common_convertHHMMToDecimal($body->startTime);

        $durationMinutes = convertHHMMToMinutes(
            $body->endTime
        ) - convertHHMMToMinutes($body->startTime);

        if (in_array(
            $body->callActTypeID,
            [4, 8, 11, 18]
        )) {            
            $userID    = $body->userID;
            $dbeUser = new DBEUser($this);
            $dbeUser->getRow($userID);
            $teamID = $dbeUser->getValue(DBEUser::teamID);
            if ($teamID <= 4) {
                $usedTime = 0;
                $allocatedTime = 0;
                if ($teamID == 1) {
                    $usedTime = $buActivity->getHDTeamUsedTime(
                        $problemID,
                        $callActivityID
                    );
                    $allocatedTime = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
                }
                if ($teamID == 2) {
                    $usedTime = $buActivity->getESTeamUsedTime(
                        $problemID,
                        $callActivityID
                    );
                    $allocatedTime = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
                }
                if ($teamID == 4) {
                    $usedTime = $buActivity->getSPTeamUsedTime(
                        $problemID,
                        $callActivityID
                    );
                    $allocatedTime = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
                }
                if ($teamID == 5) {
                    $usedTime = $buActivity->getUsedTimeForProblemAndTeam(
                        $problemID,
                        5,
                        $callActivityID
                    );
                    $allocatedTime = $dbeProblem->getValue(DBEProblem::projectTeamLimitMinutes);
                }
                if ($usedTime + $durationMinutes > $allocatedTime) {
                    return 'You cannot assign more time than left over';
                }
            }
            // check time exceed 
            $buHeader = new BUHeader($this);
            $dsHeader = new DataSet($this);
            $buHeader->getHeader($dsHeader);

            if (
                $dbeCallActivity->getValue(
                    DBEJCallActivity::callActTypeID
                ) == CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID &&
                $durationHours > $dsHeader->getValue(DBEHeader::customerContactWarnHours)
            ) {
                return
                    'Warning: Duration exceeds ' . $dsHeader->getValue(
                        DBEHeader::customerContactWarnHours
                    ) . ' hours';
            }
            if ($dbeCallActivity->getValue(
                DBEJCallActivity::callActTypeID
            ) == CONFIG_REMOTE_TELEPHONE_ACTIVITY_TYPE_ID) {
                if ($durationHours > $dsHeader->getValue(DBEHeader::remoteSupportWarnHours)) {
                    return 'Warning: Activity duration exceeds ' . $dsHeader->getValue(
                        DBEHeader::remoteSupportWarnHours
                    ) . ' hours';
                }
                // $minHours = $dsHeader->getValue(DBEHeader::RemoteSupportMinWarnHours);
                // if ($durationHours < $minHours) {
                //     return
                //         'Remote support under ' . (floor(
                //             $minHours * 60
                //         )) . ' minutes, should this be Customer Contact instead?”.';
                // }
            }
        }
        return '';
    }
    function updateCallActivity()
    {
        $this->setMethodName('updateCallActivity');
        $buActivity      = new BUActivity($this);
        $dbeProblem      = new DBEProblem($this);
        $dbejCallActType = new DBEJCallActType($this);
        $dsCallActivity  = new DataSet($this);
        $body            = file_get_contents('php://input');
        $body            = json_decode($body);
        $callActivityID  = $body->callActivityID;
        //echo $body->priority; exit;
        if ($callActivityID)
            $dbejCallActType->getRow($body->callActTypeID);
        $buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );

        //$dbeCallActivity->getRow($callActivityID);        
        //$dbeContact->getRow($body->contactID);

        
        $previousStartTime = $dsCallActivity->getValue(DBECallActivity::startTime);
        $previousEndTime = $dsCallActivity->getValue(DBECallActivity::endTime);
        $formError = (!$dsCallActivity->populateFromArray(["1" => $body]));

        
        if ($formError) {
            http_response_code(400);
            return ["error" => $formError,"type"=>"populateFromArray"];
        }
        $dsCallActivity->setUpdateModeUpdate();
        $dsCallActivity->post();
        $dsCallActivity->addColumn('priorityChangeReason',DA_TEXT,true,$body->priorityChangeReason??null);
        $dsCallActivity->setValue('priorityChangeReason',$body->priorityChangeReason??null);         
        $dsCallActivity->addColumn('emptyAssetReason',DA_TEXT,true,$body->emptyAssetReason??null);
        $dsCallActivity->setValue('emptyAssetReason',$body->emptyAssetReason??null);         

        $problemID=$dsCallActivity->getValue(DBECallActivity::problemID);
        $dbeProblem->getRow($problemID);

        if (($previousStartTime != $body->startTime) || ($previousEndTime != $body->endTime)
            && $dsCallActivity->getValue(DBECallActivity::overtimeExportedFlag) == 'N'
        ) {
            
            $dsCallActivity->setValue(DBECallActivity::overtimeDurationApproved, null);
            $dsCallActivity->setValue(DBECallActivity::overtimeApprovedDate, null);
            $dsCallActivity->setValue(DBECallActivity::overtimeApprovedBy, null);
        }

        // if no end time set then set to time now
        if (
            $body->nextStatus != 'update' &&
            $dbejCallActType->getValue(DBEJCallActType::requireCheckFlag) == 'N' &&
            $dbejCallActType->getValue(DBEJCallActType::onSiteFlag) == 'N' &&
            !$body->endTime
        ) {
            $dsCallActivity->setValue(
                DBEJCallActivity::endTime,
                date('H:i')
            );
        }
        //check activity time
        if ($body->endTime) {
            $timeError = $this->validTime($body, $dbeProblem, $buActivity, $dsCallActivity);
            if ($timeError != '') {
                http_response_code(400);
                return ["error"=> $timeError];
            }
        }
        if ($body->nextStatus == 'Fixed') {
            //try to close all the activities
            $buActivity->closeActivitiesWithEndTime(
                $problemID
            );

            if ($buActivity->countOpenActivitiesInRequest(
                $problemID,
                $body->callActivityID
            ) > 0) {
                http_response_code(400);
                return ["error"=>'Can not fix, there are open activities on this request'];
            }
        }
        $dsCallActivity->setUpdateModeUpdate();
        if (isset($body->submitAsOvertime)) {
            $dsCallActivity->setValue(
                DBECallActivity::submitAsOvertime,
                isset($body->submitAsOvertime)
            );
            $dsCallActivity->post();
        }
        //-----------check status
        $dsCallActivity->setUpdateModeUpdate();
        $updateAwaitingCustomer = false;
        if ($body->nextStatus == 'CustomerAction') {
            $dsCallActivity->setValue(DBEJCallActivity::awaitingCustomerResponseFlag, 'Y');
            $updateAwaitingCustomer = true;
        } elseif ($body->nextStatus == 'CncAction') {
            $dsCallActivity->setValue(DBEJCallActivity::awaitingCustomerResponseFlag, 'N');
            $updateAwaitingCustomer = true;
        } elseif ($body->nextStatus == 'Escalate') {
            if (
                !in_array($dbeProblem->getValue(DBEProblem::status), ["I", "F", "C"])
                && !$body->escalationReason
            ) {
                http_response_code(400);
                return ["error"=>'Please provide an escalate reason'];
                $buActivity->escalateProblemBycallActivityID($body->callActivityID, $body->escalationReason);
            }
            if ($updateAwaitingCustomer) {
                $dbeProblem->setValue(
                    DBEProblem::awaitingCustomerResponseFlag,
                    $dsCallActivity->getValue(DBECallActivity::awaitingCustomerResponseFlag)
                );
                $dbeProblem->updateRow();
            }
            $dsCallActivity->post();
        }
        
        $enteredEndTime = $buActivity->updateCallActivity(
            $dsCallActivity
        );
 
        /*
            If an end time was entered and this is a chargeable on site activity then see whether to
            create a travel activity automatically OR if one exists for today prompt whether another should be
            added.
            */
        if (
            $enteredEndTime &&
            $dbejCallActType->getValue(DBECallActType::onSiteFlag)  == 'Y' &&
            $dbejCallActType->getValue(DBEJCallActType::itemSalePrice) > 0
        ) {
            if (
                $this->buActivity->travelActivityForCustomerEngineerTodayExists(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID),
                    $dsCallActivity->getValue(DBEJCallActivity::siteNo),
                    $dsCallActivity->getValue(DBEJCallActivity::userID),
                    $dsCallActivity->getValue(DBEJCallActivity::date)
                )
                &&  $body->siteMaxTravelHours > 0    // the site has travel hours
            ) {
                http_response_code(301);
                return ["redirectTo" => "Activity.php?action=promptCreateTravel&nextStatus=$body->nextStatus&callActivityID=$body->callActivityID"];
            } else {
                $buActivity->createTravelActivity($body->callActivityID);
            }
        }
        if ($body->nextStatus == 'Fixed') {
            //try to close all the activities
            http_response_code(301);
            return ["redirectTo" => "Activity.php?action=gatherFixedInformation&callActivityID=$body->callActivityID"];
        }
        return ["status" => "1"];        
    }
    
    /**
     * @param $customerID
     * @param $contactID
     * @param string $templateName
     */
    function getCustomerContacts(
                             $templateName = 'ActivityEdit'
    )
    {
        $customerID=$_REQUEST["customerId"];
        $contactID=$_REQUEST["contactID"];
        if(!isset($customerID))
            return [];
        $dbeContact = new DBEContact($this);
        $dbeSite = new DBESite($this);
        $dbeContact->getRowsByCustomerID(
            $customerID,
            false,
            true,
            true
        );

        $contacts=array();
        $lastSiteNo = null;
        while ($dbeContact->fetchNext()) {
            $dataDelegate = "";
             $startMainContactStyle = null;
            $endMainContactStyle = null;

            if ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelMain) {
                $startMainContactStyle = '*';
                $endMainContactStyle = '*';
            } elseif ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelDelegate) {
                $startMainContactStyle = '- Delegate';
                $endMainContactStyle = '- Delegate';
                $dataDelegate = "data-delegate='true'";
            } elseif ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelSupervisor) {
                $startMainContactStyle = '- Supervisor';
                $endMainContactStyle = '- Supervisor';
            }

            $dbeSite->setValue(
                DBESite::customerID,
                $dbeContact->getValue(DBEContact::customerID)
            );
            $dbeSite->setValue(
                DBESite::siteNo,
                $dbeContact->getValue(DBEContact::siteNo)
            );
            $dbeSite->getRow();

            $name = $dbeContact->getValue(DBEContact::firstName) . ' ' . $dbeContact->getValue(DBEContact::lastName);

            if ($dbeContact->getValue(DBEContact::position)) {
                $name .= ' (' . $dbeContact->getValue(DBEContact::position) . ')';
            }
            $optGroupOpen = null;

            if ($dbeContact->getValue(DBEContact::siteNo) != $lastSiteNo) {
                $optGroupOpen = '<optgroup label="' . $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                        DBESite::town
                    ) . ' ' . $dbeSite->getValue(DBESite::postcode) . '">';
            }
            $lastSiteNo = $dbeContact->getValue(DBEContact::siteNo);

            array_push($contacts,            
                array(
                    'id'                    => $dbeContact->getValue(DBEContact::contactID),
                    'contactName'           => $name,
                    'startMainContactStyle' => $startMainContactStyle,
                    'endMainContactStyle'   => $endMainContactStyle,
                    'optGroupOpen'          => $optGroupOpen,
                    'dataDelegate'          => $dataDelegate
                )
            );
            
        }
        return $contacts;
    }
    function getCustomerSites()
    {
        $customerID=$_REQUEST["customerId"];
        if(!isset($customerID))
        return [];
         // Site selection
         $dbeSite = new DBESite($this);
         $dbeSite->setValue(
             DBESite::customerID,
             $customerID
         );
         $dbeSite->getRowsByCustomerID();
         $sites=array();
         while ($dbeSite->fetchNext()) {
             $siteDesc = $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                     DBESite::town
                 ) . ' ' . $dbeSite->getValue(DBESite::postcode);
            array_push($sites,             
                 array(                     
                     'id'       => $dbeSite->getValue(DBESite::siteNo),
                     'name'     => $siteDesc
                 )
             );             
         }
         return $sites;
    }
    function getPriorities()
    {
        $buActivity = new BUActivity($this);
        $priorities=array();
        foreach ($buActivity->priorityArray as $key => $value) {
            array_push( $priorities,
                array(                    
                    'id'         => $key,
                    'name'     => $value
                )
            );            
        }
        return $priorities;
    }
    
    function getCustomerContracts()
    {
        $customerID = $_REQUEST["customerId"];
        $contractCustomerItemID = $_REQUEST["contractCustomerItemID"];
        $linkedToSalesOrder = $_REQUEST["linkedToSalesOrder"];
        $contracts = array();
        $buCustomerItem = new BUCustomerItem($this);
        $dsContract = new DataSet($this);
        if ($customerID) {
            $buCustomerItem->getContractsByCustomerID(
                $customerID,
                $dsContract,
                null
            );
        }

        if (!$contractCustomerItemID) {
            array_push($contracts,["id" => "", "name" => "tandMSelected", "renewalType" =>null]);
        }

        // if ($linkedToSalesOrder) {
        //     $this->template->set_var(
        //         [

        //             'salesOrderReason' => "- Must be selected because this is linked to a Sales Order"
        //         ]

        //     );
        // } 
        while ($dsContract->fetchNext()) {

            $description = $dsContract->getValue(DBEJContract::itemDescription) . ' ' . $dsContract->getValue(
                DBEJContract::adslPhone
            ) . ' ' . $dsContract->getValue(DBEJContract::notes) . ' ' . $dsContract->getValue(
                DBEJContract::postcode
            );
            array_push($contracts,
                array(
                    'contractCustomerItemID' => $dsContract->getValue(DBEJContract::customerItemID),
                    'contractDescription'    => $description,
                    'prepayContract'         => $dsContract->getValue(DBEJContract::itemTypeID) == 57,
                    'isDisabled'             => !$dsContract->getValue(
                        DBEJContract::allowSRLog
                    ) || $linkedToSalesOrder=='true' ? true : false,
                    'renewalType'           => $dsContract->getValue(DBEJContract::renewalType)
                )
            );            
         }
         return $contracts;
    } 
     
    function getRootCauses()
    {
        $rootCauses=array();
        $buRootCause = new BURootCause($this);
        $dsRootCause = new DataSet($this);
        $buRootCause->getAll($dsRootCause);

        while ($dsRootCause->fetchNext()) {
            array_push($rootCauses,
                array(
                    'id'      => $dsRootCause->getValue(DBERootCause::rootCauseID),
                    'description' => $dsRootCause->getValue(
                            DBERootCause::description
                        ) . " (" . $dsRootCause->getValue(
                            DBERootCause::longDescription
                        ) . ")",
                    'fixedText'   => base64_encode(
                        $dsRootCause->getValue(
                            DBERootCause::fixedExplanation
                        )
                    )
                )

            );           
        }
        return $rootCauses;
    }
    /**
     * Log new Service Request using json Data
     */
    function addNewSR()
    {

        try {

            $body                   = file_get_contents('php://input');
            $body                   = json_decode($body);
            $buActivity             = new BUActivity($this);
            $body->date             = date(DATE_MYSQL_DATE);
            $body->startTime        = date('H:i');
            $body->dateRaised       = date(DATE_MYSQL_DATE);
            $body->timeRaised       = date('H:i');
            $body->callActTypeID    = CONFIG_INITIAL_ACTIVITY_TYPE_ID;
            //return ["team"=>$body->notStartWorkReason];
            $dsCallActivity = $buActivity->createActivityFromJson($body);
            if (isset($dsCallActivity)) {
                if (isset($body->pendingReopenedID) && isset($body->deletePending) && $body->deletePending == 'true') {
                    //delete pending
                    $dbePendingReopened = new DBEPendingReopened($this);
                    $dbePendingReopened->deleteRow($body->pendingReopenedID);
                }
                if ($body->startWork) {
                    $newActivityID = $buActivity->createFollowOnActivity(
                        $dsCallActivity->getValue(DBEJCallActivity::callActivityID),
                        $this->getParam('callActivityTypeID'),
                        false,
                        $this->getParam('reason'),
                        true,
                        false,
                        $GLOBALS['auth']->is_authenticated(),
                        $this->getParam('moveToUsersQueue')
                    );
                    //$nextURL ="Activity.php?action=createFollowOnActivity&callActivityID=".$newActivityID ."&moveToUsersQueue=1";                
                    $nextURL = "SRActivity.php?action=editActivity&callActivityID=" . $newActivityID;
                } else {
                    $nextURL = "CurrentActivityReport.php";
                }
                $currentUser = $this->getDbeUser();
                if (!$body->startWork && $currentUser->getValue(DBEUser::teamID) == 1) {
                    //$body->notStartWorkReason
                    $dbeProblemNotStartReason = new DBEProblemNotStartReason($this);
                    $dbeProblemNotStartReason->setValue(DBEProblemNotStartReason::problemID, $dsCallActivity->getValue(DBEJCallActivity::problemID));
                    $dbeProblemNotStartReason->setValue(DBEProblemNotStartReason::reason, $body->notStartWorkReason);
                    $dbeProblemNotStartReason->setValue(DBEProblemNotStartReason::userID, $currentUser->getValue(DBEUser::userID));
                    $dbeProblemNotStartReason->setValue(DBEProblemNotStartReason::createAt, $body->dateRaised . ' ' . $body->timeRaised . ':00');
                    $dbeProblemNotStartReason->insertRow();
                }
                if(isset($body->customerproblemno)&&$body->customerproblemno!=null)
                {
                    $buActivity->deleteCustomerRaisedRequest($body->customerproblemno);
                }
                $problemID=$dsCallActivity->getValue(DBEJCallActivity::problemID);
                $dbeProblem=new DBEProblem($this);
                $dbeProblem->getRow($problemID);
                return ["status" => 1, "nextURL" => $nextURL, "problemID" => $problemID, "callActivityID" => $dsCallActivity->getValue(DBEJCallActivity::callActivityID),"raiseTypeId"=>$dbeProblem->getValue(DBEProblem::raiseTypeId)];
            } else return ["status" => 0];
        } catch (Exception $exception) {
            return ["status" => 3, "error" => $exception->getMessage()];
        }
    }
    function getCallActivityType()
    {
        $callActivityID=$this->getParam("callActivityID");
        $callActivity=new DBECallActivity($this);
        $callActivity->getRow($callActivityID);
        return $callActivity->getValue(DBECallActivity::callActTypeID);
    }
    function getCustomerRaisedRequest()
    {
        $Id= $this->getParam("customerproblemno");
        if($Id)
        {            
            $buActivity=new BUActivity($this);
            return $buActivity->getCustomerRaisedRequest($Id);
        }
        else return null;
    }
    function getCallActivityBasicInfo(){

        $callActivityID = $this->getParam("callActivityID");
        if (isset($callActivityID)) {
            $callActivity = new DBEJCallActivity($this);
            $callActivity->getRow($callActivityID);
            return [
                "callActivityID" => $callActivity->getValue(DBECallActivity::callActivityID),
                "problemID" => $callActivity->getValue(DBECallActivity::problemID),
                "callActTypeID" => $callActivity->getValue(DBECallActivity::callActTypeID),
                "customerID" => $callActivity->getValue(DBEJCallActivity::customerID),
                "customerName" => $callActivity->getValue(DBEJCallActivity::customerName),
                "contactID" => $callActivity->getValue(DBEJCallActivity::contactID),
                "contractCustomerItemID"=> $callActivity->getValue(DBEJCallActivity::contractCustomerItemID),
                "linkedSalesOrderID"=> $callActivity->getValue(DBEJCallActivity::linkedSalesOrderID),
                "problemHideFromCustomerFlag"=>$callActivity->getValue(DBEJCallActivity::problemHideFromCustomerFlag),
                "rootCauseID"=>$callActivity->getValue(DBEJCallActivity::rootCauseID),                
            ];
        } else return null;
    }
    function getInitialActivity(){
        $problemID=$_REQUEST["problemID"];
        if(!isset($problemID))
        return null;
        
        $buActivity=new BUActivity($this);
        $dbeJCallActivity=$buActivity->getFirstActivityInProblem($problemID,57);//initial activity
         if($dbeJCallActivity)
        return [
            "callActivityID"=>$dbeJCallActivity->getValue(DBEJCallActivity::callActivityID),
            "reason"=>$dbeJCallActivity->getValue(DBEJCallActivity::reason),
        ];
        else 
        return null;

    }
    function saveFixedInformation()
    {
        $body                   = file_get_contents('php://input');
        $body                   = json_decode($body);
        if(!isset($body->problemID)||
        !isset($body->contractCustomerItemID)||
        !isset($body->rootCauseID)||
        !isset($body->resolutionSummary)
        )
        {
            http_response_code(400);
            return ["error"=>$body];
        }
        $buActivity=new BUActivity($this);
        $buActivity->setProblemToFixed(
            $body->problemID,
            false,
            $body->contractCustomerItemID,
            $body->rootCauseID,
            $body->resolutionSummary
        );
        return ["status"=>true];
        
    }
    function saveManagementReviewDetails()
    {
        $body                   = file_get_contents('php://input');
        $body                   = json_decode($body);
        if(!isset($body->problemID)||
        !isset($body->description)
        )
        {
            http_response_code(400);
            return ["error"=>$body];
        }
        $buActivity=new BUActivity($this);
        $buActivity->updateManagementReviewReason(
            $body->problemID,
            $body->description
        );
        return ["status"=>true];
        
    }
    function changeProblemPriority()
    {
        $body                   = file_get_contents('php://input');
        $body                   = json_decode($body);
        if(!isset($body->callActivityID)||
        !isset($body->priorityChangeReason)||
        !isset($body->priority)
        )
        {
            http_response_code(400);
            return ["error"=>$body];
        }
        $buActivity=new BUActivity($this);
       return $buActivity->updateCallActivityPriority(
            $body->callActivityID,
            $body->priority,
            $body->priorityChangeReason
        );
    }
}
?>