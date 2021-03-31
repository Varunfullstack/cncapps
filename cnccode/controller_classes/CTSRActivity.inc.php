<?php
global $cfg;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestServiceRequestId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\ChargeableWorkCustomerRequest\infra\ChargeableWorkCustomerRequestMySQLRepository;
use CNCLTD\ChargeableWorkCustomerRequest\usecases\CreateChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\usecases\GetPendingToProcessChargeableRequestInfo;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use CNCLTD\Exceptions\JsonHttpException;
use CNCLTD\Exceptions\ServiceRequestNotFoundException;
use CNCLTD\InternalDocuments\Base64FileDTO;
use CNCLTD\InternalDocuments\Entity\InternalDocumentMapper;
use CNCLTD\InternalDocuments\InternalDocumentRepository;
use CNCLTD\InternalDocuments\UseCases\AddDocumentsToServiceRequest;
use CNCLTD\ServiceRequestInternalNote\infra\ServiceRequestInternalNotePDORepository;
use CNCLTD\ServiceRequestInternalNote\ServiceRequestInternalNote;
use CNCLTD\ServiceRequestInternalNote\ServiceRequestInternalNotePDOMapper;
use CNCLTD\ServiceRequestInternalNote\UseCases\AddServiceRequestInternalNote;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBECallActivity.inc.php');
require_once($cfg['path_dbe'] . '/DBEJCallActivity.php');
require_once($cfg['path_dbe'] . '/DBEProblem.inc.php');
require_once($cfg['path_dbe'] . '/DBEProblemRaiseType.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBEContact.inc.php');
require_once($cfg['path_dbe'] . '/DBESite.inc.php');
require_once($cfg['path_dbe'] . '/DBEHeader.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUExpenseType.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_bu'] . '/BURootCause.inc.php');
require_once($cfg['path_bu'] . '/BUActivityType.inc.php');
require_once($cfg['path_dbe'] . '/DBEJCallActType.php');
require_once($cfg['path_dbe'] . '/DBECallBack.inc.php');
require_once($cfg['path_dbe'] . '/DBEJCallActivity.php');

class CTSRActivity extends CTCNC
{
    const GREEN                                                  = '#BDF8BA';
    const CONTENT                                                = '#F4f4f2';
    const GET_CUSTOMER_CONTACT_ACTIVITY_DURATION_THRESHOLD_VALUE = "getCustomerContactActivityDurationThresholdValue";
    const GET_REMOTE_SUPPORT_ACTIVITY_DURATION_THRESHOLD_VALUE   = "getRemoteSupportActivityDurationThresholdValue";
    const GET_DOCUMENTS_FOR_SERVICE_REQUEST                      = 'getDocumentsForServiceRequest';
    const GET_DOCUMENTS                                          = "getDocuments";
    const GET_CALL_ACTIVITY_BASIC_INFO                           = "getCallActivityBasicInfo";
    const GET_CUSTOMER_RAISED_REQUEST                            = "getCustomerRaisedRequest";
    const GET_CALL_ACTIVITY_TYPE                                 = "getCallActivityType";
    const CREATE_PROBLEM                                         = "createProblem";
    const GET_ROOT_CAUSES                                        = "getRootCauses";
    const GET_CUSTOMER_CONTRACTS                                 = "getCustomerContracts";
    const GET_PRIORITIES                                         = "getPriorities";
    const GET_CUSTOMER_SITES                                     = "getCustomerSites";
    const GET_CUSTOMER_CONTACTS                                  = "getCustomerContacts";
    const UPDATE_ACTIVITY                                        = "updateActivity";
    const MESSAGE_TO_SALES                                       = "messageToSales";
    const GET_CALL_ACTIVITY                                      = "getCallActivity";
    const SAVE_FIXED_INFORMATION                                 = "saveFixedInformation";
    const GET_INITIAL_ACTIVITY                                   = "getInitialActivity";
    const SAVE_MANAGEMENT_REVIEW_DETAILS                         = "saveManagementReviewDetails";
    const CHANGE_PROBLEM_PRIORITY                                = "changeProblemPriority";
    const USED_BUDGET_DATA                                       = "usedBudgetData";
    const UPLOAD_INTERNAL_DOCUMENT                               = "uploadInternalDocument";
    const VIEW_INTERNAL_DOCUMENT                                 = 'viewInternalDocument';
    const DELETE_INTERNAL_DOCUMENT                               = 'deleteInternalDocument';
    const REMOTE_SUPPORT_ACTIVITY_TYPE_ID                        = 8;
    const GET_NOT_ATTEMPT_FIRST_TIME_FIX                         = "getNotAttemptFirstTimeFix";
    const ADD_INTERNAL_NOTE                                      = "addInternalNote";
    const CHANGE_SERVICE_REQUEST_INTERNAL_NOTE                   = "changeServiceRequestInternalNote";
    const SAVE_TASK_LIST                                         = "saveTaskList";
    const ADD_ADDITIONAL_TIME_REQUEST                            = "addAdditionalTimeRequest";
    const GET_ADDITIONAL_CHARGEABLE_WORK_REQUEST_INFO            = "getAdditionalChargeableWorkRequestInfo";
    const CHECK_SERVICE_REQUEST_PENDING_CALLBACKS                = "checkServiceRequestPendingCallbacks";
    public  $serverGuardArray = array(
        ""  => "Please select",
        "Y" => "ServerGuard Related",
        "N" => "Not ServerGuard Related"
    );
    private $buActivity;
    private $internalDocumentRepository;
    /**
     * @var ServiceRequestInternalNotePDORepository
     */
    private $serviceRequestInternalNoteRepository;

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
        $roles                                      = [
            SALES_PERMISSION,
            ACCOUNTS_PERMISSION,
            TECHNICAL_PERMISSION,
            SUPERVISOR_PERMISSION,
            REPORTS_PERMISSION,
            MAINTENANCE_PERMISSION,
            RENEWALS_PERMISSION,
        ];
        $this->buActivity                           = new BUActivity($this);
        $this->internalDocumentRepository           = new InternalDocumentRepository();
        $this->serviceRequestInternalNoteRepository = new ServiceRequestInternalNotePDORepository();
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
            case self::GET_CALL_ACTIVITY:
                echo json_encode($this->getActivityDetails());
                exit;
            case self::MESSAGE_TO_SALES:
                echo json_encode($this->messageToSales());
                exit;
            case self::UPDATE_ACTIVITY:
                echo json_encode($this->updateCallActivity());
                exit;
            case self::GET_CUSTOMER_CONTACTS:
                echo json_encode($this->getCustomerContacts());
                exit;
            case self::GET_CUSTOMER_SITES:
                echo json_encode($this->getCustomerSites());
                exit;
            case self::GET_PRIORITIES:
                echo json_encode($this->getPriorities());
                exit;
            case self::GET_CUSTOMER_CONTRACTS:
                echo json_encode($this->getCustomerContracts());
                exit;
            case self::GET_ROOT_CAUSES:
                echo json_encode($this->getRootCauses());
                exit;
            case self::CREATE_PROBLEM:
                echo json_encode($this->addNewSR());
                exit;
            case self::GET_CALL_ACTIVITY_TYPE:
                echo json_encode($this->getCallActivityType());
                exit;
            case self::GET_CUSTOMER_RAISED_REQUEST:
                echo json_encode($this->getCustomerRaisedRequest());
                exit;
            case self::GET_CALL_ACTIVITY_BASIC_INFO:
                echo json_encode($this->getCallActivityBasicInfo());
                exit;
            case self::ADD_ADDITIONAL_TIME_REQUEST:
                echo json_encode($this->addAdditionalTimeRequestController());
                exit;
            case self::ADD_INTERNAL_NOTE:
                echo json_encode($this->addInternalNoteController());
                exit;
            case self::GET_DOCUMENTS:
                echo json_encode($this->getActivityDocuments($_REQUEST["callActivityID"], $_REQUEST["problemID"]));
                exit;
            case self::GET_DOCUMENTS_FOR_SERVICE_REQUEST:
                echo json_encode($this->getDocumentsForServiceRequestController());
                exit;
            case self::UPLOAD_INTERNAL_DOCUMENT:
                echo json_encode($this->addServiceRequestsUploadedDocuments());
                exit;
            case self::VIEW_INTERNAL_DOCUMENT:
                $this->viewInternalDocument();
                exit;
            case self::DELETE_INTERNAL_DOCUMENT:
                echo json_encode($this->deleteInternalDocument());
                exit;
            case self::SAVE_FIXED_INFORMATION:
                echo json_encode($this->saveFixedInformation());
                exit;
            case self::GET_INITIAL_ACTIVITY:
                echo json_encode($this->getInitialActivity());
                exit;
            case self::SAVE_MANAGEMENT_REVIEW_DETAILS:
                echo json_encode($this->saveManagementReviewDetails());
                exit;
            case self::CHANGE_PROBLEM_PRIORITY:
                echo json_encode($this->changeProblemPriority());
                exit;
            case self::USED_BUDGET_DATA:
                echo json_encode($this->usedBudgetData());
                exit;
            case self::CHECK_SERVICE_REQUEST_PENDING_CALLBACKS:
                echo json_encode($this->checkServiceRequestPendingCallbacksController());
                exit;
            case self::GET_CUSTOMER_CONTACT_ACTIVITY_DURATION_THRESHOLD_VALUE:
            {
                $buHeader = new BUHeader($this);
                $dsHeader = new DataSet($this);
                $buHeader->getHeader($dsHeader);
                echo json_encode(
                    ["status" => "ok", "data" => $dsHeader->getValue(DBEHeader::customerContactWarnHours)]
                );
                exit;
            }
            case self::GET_REMOTE_SUPPORT_ACTIVITY_DURATION_THRESHOLD_VALUE:
            {
                $buHeader = new BUHeader($this);
                $dsHeader = new DataSet($this);
                $buHeader->getHeader($dsHeader);
                echo json_encode(
                    ["status" => "ok", "data" => $dsHeader->getValue(DBEHeader::remoteSupportWarnHours)]
                );
                exit;
            }
            case "toggleHoldForQAFlag":
                echo json_encode($this->setToggleHoldForQAFlag());
                exit;
            case self::GET_ADDITIONAL_CHARGEABLE_WORK_REQUEST_INFO:
                echo json_encode($this->getAdditionalChargeableWorkRequestInfoController());
                exit;
            case 'getLastActivityInServiceRequest':
                $buActivity       = new BUActivity($this);
                $serviceRequestId = $this->getParam('serviceRequestId');
                if (!$serviceRequestId) {
                    throw new JsonHttpException(250, "Service request Id is required");
                }
                $dbeActivity = $buActivity->getLastActivityInProblem($this->getParam('serviceRequestId'));
                echo json_encode(["status" => "ok", "data" => $dbeActivity->getValue(DBECallActivity::callActivityID)]);
                exit;
            case self::GET_NOT_ATTEMPT_FIRST_TIME_FIX:
                echo json_encode($this->getNotAttemptFirstTimeFix());
                exit;
            case self::SAVE_TASK_LIST:
                echo json_encode($this->saveTaskListController());
                exit;
            default:
                $this->setTemplate();
                break;
        }

    }

    private function getActivityDetails()
    {
        $callActivityID = $_REQUEST["callActivityID"];
        //get call activity
        $dbejCallActivity = new DBEJCallActivity($this);
        $dbeProblem       = new DBEProblem($this);
        $dbejCallActivity->setPKValue($callActivityID);
        $dbejCallActivity->getRow();
        $problemID = $dbejCallActivity->getValue(DBECallActivity::problemID);
        $dbeProblem->setPKValue($problemID);
        $dbeProblem->getRow();
        $customerId      = $dbejCallActivity->getValue(DBEJCallActivity::customerID);
        $contactID       = $dbejCallActivity->getValue(DBEJCallActivity::contactID);
        $siteId          = $dbejCallActivity->getValue(DBEJCallActivity::siteNo);
        $projectLink     = BUProject::getCurrentProjectLink(
            $customerId
        );
        $dbeActivityType = new DBECallActType($this);
        $dbeActivityType->getRow($dbejCallActivity->getValue(DBECallActivity::callActTypeID));
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->setPKValue($customerId);
        $dbeCustomer->getRow();
        $customerNameDisplayClass = $this->getCustomerNameDisplayClass($dbeCustomer);
        $dbeContact               = new DBEContact($this);
        $dbeContact->setPKValue($contactID);
        $dbeContact->getRow();
        $dbeSite = new DBESite($this);
        $dbeSite->setPKValue($siteId);
        $dbeSite->setValue(DBESite::customerID, $customerId);
        $dbeSite->getRowByCustomerIDSiteNo();
        $buActivity      = new BUActivity($this);
        $buUser          = new BUUser($this);
        $dbeLastActivity = $buActivity->getLastActivityInProblem($problemID);
        if ($dbeLastActivity->getValue(DBEJCallActivity::callActTypeID) == 0 && $dbeLastActivity->getValue(
                DBEJCallActivity::userID
            ) != $GLOBALS['auth']->is_authenticated()) {
            $currentUserBgColor = self::GREEN;
            $currentUser        = $dbeLastActivity->getValue(
                    DBEJCallActivity::userName
                ) . ' Is Adding New Activity To This Request Now';
        } else {
            $currentUserBgColor = self::CONTENT;
            $currentUser        = null;
        }
        $expenses        = $this->getActivityExpenses($callActivityID);
        $dbeUserActivity = new DBEUser($this);
        $dbeUserActivity->getRow($dbejCallActivity->getValue(DBEJCallActivity::userID));
        $hdAssignedMinutes                     = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
        $esAssignedMinutes                     = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
        $imAssignedMinutes                     = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
        $projectTeamAssignedMinutes            = $dbeProblem->getValue(DBEProblem::projectTeamLimitMinutes);
        $projectUsedMinutes                    = $buActivity->getUsedTimeForProblemAndTeam($problemID, 5);
        $hdUsedMinutes                         = $buActivity->getHDTeamUsedTime($problemID);
        $esUsedMinutes                         = $buActivity->getESTeamUsedTime($problemID);
        $imUsedMinutes                         = $buActivity->getSPTeamUsedTime($problemID);
        $isProblemClosed                       = $dbejCallActivity->getValue(DBEJCallActivity::problemStatus) == 'C';
        $isManagerUser                         = $this->isSdManager() || $this->isSRQueueManager();
        $isUserManagerAndActivityNotAStatus    = $dbejCallActivity->getValue(
                DBEJCallActivity::status
            ) != 'A' && $isManagerUser;
        $isNotUserManagerAndActivityHasEndTime = !$isManagerUser && !$dbejCallActivity->getValue(
                DBEJCallActivity::endTime
            );
        $requestName                           = '';
        $status                                = $dbeProblem->getValue(DBEProblem::status);
        if ($status == 'I' || $status == 'P') {
            $requestUserID = $dbeProblem->getValue(DBEProblem::userID);
            if (!empty($requestUserID)) {
                $requestUser = new DBEUser($this);
                $requestUser->getRow($requestUserID);
                $requestName = $requestUser->getValue(DBEUser::firstName) . ' ' . substr(
                        $requestUser->getValue(DBEUser::lastName),
                        0,
                        1
                    );
            } else  $requestName = 'Unassigned';

        }
        $serviceRequestInternalNotesRepo = new CNCLTD\ServiceRequestInternalNote\infra\ServiceRequestInternalNotePDORepository(
        );
        $notes                           = $serviceRequestInternalNotesRepo->getServiceRequestInternalNotesForSR(
            $problemID
        );
        $consultants                     = [];
        $mappedNotes                     = array_map(
            function (ServiceRequestInternalNote $note) use ($consultants) {
                $updatedByUserId = $note->getUpdatedBy();
                if (!key_exists($updatedByUserId, $consultants)) {
                    $createdByUser = new DBEUser($this);
                    $createdByUser->getRow($updatedByUserId);
                    $consultants[$updatedByUserId] = "{$createdByUser->getValue(DBEUser::firstName)} {$createdByUser->getValue(DBEUser::lastName)}";
                }
                $createdByUserId = $note->getCreatedBy();
                if (!key_exists($createdByUserId, $consultants)) {
                    $updatedByUser = new DBEUser($this);
                    $updatedByUser->getRow($createdByUserId);
                    $consultants[$createdByUserId] = "{$updatedByUser->getValue(DBEUser::firstName)} {$updatedByUser->getValue(DBEUser::lastName)}";
                }
                $array              = ServiceRequestInternalNotePDOMapper::toJSONArray($note);
                $array['updatedBy'] = $consultants[$updatedByUserId];
                $array['createdBy'] = $consultants[$createdByUserId];
                return $array;
            },
            $notes
        );
        usort(
            $mappedNotes,
            function ($a, $b) {
                if ($a['createdAt'] <= $b['createdAt']) {
                    return 1;
                }
                return -1;
            }
        );
        $chargeableWorkRequestRepo = new ChargeableWorkCustomerRequestMySQLRepository();
        try {
            $chargeableRequest   = $chargeableWorkRequestRepo->getChargeableRequestForServiceRequest(
                new ChargeableWorkCustomerRequestServiceRequestId($problemID)
            );
            $chargeableRequestId = $chargeableRequest->getId()->value();
        } catch (Exception $exception) {
            $chargeableRequestId = null;
        }
        $taskListUpdatedByUserId = $dbeProblem->getValue(DBEProblem::taskListUpdatedBy);
        $taskListUpdatedBy       = null;
        if ($taskListUpdatedByUserId) {
            if (!key_exists($taskListUpdatedByUserId, $consultants)) {
                $taskListUpdatedByUser = new DBEUser($this);
                $taskListUpdatedByUser->getRow($taskListUpdatedByUserId);
                $consultants[$taskListUpdatedByUserId] = "{$taskListUpdatedByUser->getValue(DBEUser::firstName)} {$taskListUpdatedByUser->getValue(DBEUser::lastName)}";
            }
            $taskListUpdatedBy = $consultants[$taskListUpdatedByUserId];
        }
        $currentLoggedInUser = $this->getDbeUser();
        $callback            = new DBECallback($this);
        $pendingCallbacks    = $callback->pendingCallbackCountForServiceRequest($problemID);
        return [
            "callActivityID"                  => $callActivityID,
            "problemID"                       => $problemID,
            "projectLink"                     => $projectLink,
            "customerNameDisplayClass"        => $customerNameDisplayClass,
            'customerId'                      => $customerId,
            "customerName"                    => $dbeCustomer->getValue(DBECustomer::name),
            "contactID"                       => $contactID,
            "contactPhone"                    => $dbeContact->getValue(DBEContact::phone),
            "contactName"                     => $dbeContact->getValue(
                    DBEContact::firstName
                ) . " " . $dbeContact->getValue(
                    DBEContact::lastName
                ),
            "contactMobilePhone"              => $dbeContact->getValue(DBEContact::mobilePhone),
            "contactEmail"                    => $dbeContact->getValue(DBEContact::email),
            "siteNo"                          => $dbeSite->getValue(DBESite::siteNo),
            "sitePhone"                       => $dbeSite->getValue(DBESite::phone),
            "siteAdd1"                        => $dbeSite->getValue(DBESite::add1),
            "siteAdd2"                        => $dbeSite->getValue(DBESite::add2),
            "siteAdd3"                        => $dbeSite->getValue(DBESite::add3),
            "siteTown"                        => $dbeSite->getValue(DBESite::town),
            "sitePostcode"                    => $dbeSite->getValue(DBESite::postcode),
            "linkedSalesOrderID"              => $dbejCallActivity->getValue(DBEJCallActivity::linkedSalesOrderID),
            "activities"                      => $this->getOtherActivity($problemID),
            "internalNotes"                   => $mappedNotes,
            'criticalFlag'                    => $dbejCallActivity->getValue(
                DBEJCallActivity::criticalFlag
            ) == 'Y' ? 1 : 0,
            'monitoringFlag'                  => $this->checkMonitoring($problemID) ? 1 : 0,
            "totalActivityDurationHours"      => $dbeProblem->getValue(DBEProblem::totalActivityDurationHours),
            "chargeableActivityDurationHours" => $dbeProblem->getValue(DBEProblem::chargeableActivityDurationHours),
            "onSiteActivities"                => $this->getOnSiteActivity($callActivityID),
            "problemStatus"                   => $dbejCallActivity->getValue(DBEJCallActivity::problemStatus),
            "serverGuard"                     => $dbejCallActivity->getValue(DBEJCallActivity::serverGuard),
            "problemHideFromCustomerFlag"     => $dbeProblem->getValue(DBEProblem::hideFromCustomerFlag),
            "serviceRequestEmailSubject"      => $dbeProblem->getValue(DBEProblem::emailSubjectSummary),
            "canEdit"                         => $buActivity->checkActivityEditionByProblem(
                $dbejCallActivity,
                $this,
                $dbeProblem
            ),
            "canDelete"                       => !$isProblemClosed && ($isUserManagerAndActivityNotAStatus || $isNotUserManagerAndActivityHasEndTime),
            "hasExpenses"                     => count($expenses) ? true : false,
            "isSDManager"                     => $buUser->isSdManager($this->userID),
            "hideFromCustomerFlag"            => $dbejCallActivity->getValue(DBEJCallActivity::hideFromCustomerFlag),
            "priority"                        => $buActivity->priorityArray[$dbejCallActivity->getValue(
                DBEJCallActivity::priority
            )],
            "problemStatusDetials"            => $buActivity->problemStatusArray[$dbeProblem->getValue(
                DBEProblem::status
            )],
            "awaitingCustomerResponseFlag"    => $dbeProblem->getValue(DBEProblem::awaitingCustomerResponseFlag),
            "activityType"                    => $dbejCallActivity->getValue(DBEJCallActivity::activityType),
            "authorisedBy"                    => $this->getAuthorisedBy($dbeProblem),
            "engineerName"                    => $dbejCallActivity->getValue(DBEJCallActivity::userName),
            "contractType"                    => $this->getContractType($dbejCallActivity),
            "curValue"                        => $dbejCallActivity->getValue(DBEJCallActivity::curValue),
            "serverGuardDetials"              => $this->serverGuardArray[$dbejCallActivity->getValue(
                DBEJCallActivity::serverGuard
            )],
            "date"                            => $dbejCallActivity->getValue(DBEJCallActivity::date),
            "projectDescription"              => $dbejCallActivity->getValue(DBEJCallActivity::projectDescription),
            "startTime"                       => $dbejCallActivity->getValue(DBEJCallActivity::startTime),
            "endTime"                         => $dbejCallActivity->getValue(DBEJCallActivity::endTime),
            "rootCauseDescription"            => $dbejCallActivity->getValue(DBEJCallActivity::rootCauseDescription),
            "completeDate"                    => $dbejCallActivity->getValue(DBEJCallActivity::completeDate),
            "reason"                          => $dbejCallActivity->getValue(DBEJCallActivity::reason),
            "currentUser"                     => $currentUser,
            "currentUserBgColor"              => $currentUserBgColor,
            "documents"                       => $this->getActivityDocuments($callActivityID, $problemID),
            "expenses"                        => $expenses,
            "partsUsed"                       => null,
            'disabledChangeRequest'           => $dbeProblem->getValue(DBEProblem::status) == 'P' ? '' : 'disabled',
            'contactNotes'                    => $dbejCallActivity->getValue(DBEJCallActivity::contactNotes),
            'techNotes'                       => $dbejCallActivity->getValue(DBEJCallActivity::techNotes),
            "callActTypeID"                   => $dbejCallActivity->getValue(DBEJCallActivity::callActTypeID),
            'alarmDate'                       => $dbejCallActivity->getValue(DBEJCallActivity::alarmDate),
            'alarmTime'                       => $dbejCallActivity->getValue(DBEJCallActivity::alarmTime),
            'alarmDateMessage'                => $dbejCallActivity->getValue(DBEJCallActivity::alarmDate),
            'alarmTimeMessage'                => $dbejCallActivity->getValue(DBEJCallActivity::alarmTime),
            'canChangeInitialDateAndTime'     => $currentLoggedInUser->getValue(DBEUser::queueManager) == 'Y',
            "isInitalDisabled"                => $this->isInitalDisabled($dbejCallActivity),
            'contactSupportLevel'             => $dbeContact->getValue(DBEContact::supportLevel),
            'hdRemainMinutes'                 => $hdAssignedMinutes - $hdUsedMinutes,
            'esRemainMinutes'                 => $esAssignedMinutes - $esUsedMinutes,
            'imRemainMinutes'                 => $imAssignedMinutes - $imUsedMinutes,
            'projectRemainMinutes'            => $projectTeamAssignedMinutes - $projectUsedMinutes,
            "canChangePriorityFlag"           => $currentLoggedInUser->getValue(DBEUser::changePriorityFlag) == 'Y',
            "userID"                          => $dbejCallActivity->getValue(DBEJCallActivity::userID),
            "actUserTeamId"                   => $dbeUserActivity->getValue(DBEUser::teamID),
            "contractCustomerItemID"          => $dbejCallActivity->getValue(DBEJCallActivity::contractCustomerItemID),
            "changeSRContractsFlag"           => $currentLoggedInUser->getValue(DBEUser::changeSRContractsFlag) == 'Y',
            "rootCauseID"                     => $dbejCallActivity->getValue(DBEJCallActivity::rootCauseID),
            'submitAsOvertime'                => $dbejCallActivity->getValue(DBECallActivity::submitAsOvertime),
            "siteMaxTravelHours"              => $dbeSite->getValue(DBESite::maxTravelHours),
            "projectId"                       => $dbejCallActivity->getValue(DBEJCallActivity::projectID),
            "projects"                        => BUProject::getCustomerProjects($customerId),
            "cncNextAction"                   => $dbejCallActivity->getValue(DBEJCallActivity::cncNextAction),
            "customerNotes"                   => $dbejCallActivity->getValue(DBEJCallActivity::customerSummary),
            'activityTypeHasExpenses'         => BUActivityType::hasExpenses(
                $dbejCallActivity->getValue(DBEJCallActivity::callActTypeID)
            ),
            'assetName'                       => $dbeProblem->getValue(DBEProblem::assetName),
            'assetTitle'                      => $dbeProblem->getValue(DBEProblem::assetTitle),
            "emptyAssetReason"                => $dbeProblem->getValue(DBEProblem::emptyAssetReason),
            "holdForQA"                       => $dbeProblem->getValue(DBEProblem::holdForQA),
            "isOnSiteActivity"                => $dbeActivityType->getValue(DBECallActType::onSiteFlag) == 'Y',
            "chargeableWorkRequestId"         => $chargeableRequestId,
            "openHours"                       => $dbeProblem->getValue(DBEProblem::openHours),
            "workingHours"                    => $dbeProblem->getValue(DBEProblem::workingHours),
            "requestEngineerName"             => $requestName,
            "emailsubjectsummary"             => $dbeProblem->getValue(DBEProblem::emailSubjectSummary),
            "taskList"                        => $dbeProblem->getValue(DBEProblem::taskList),
            "taskListUpdatedAt"               => $dbeProblem->getValue(DBEProblem::taskListUpdatedAt),
            "taskListUpdatedBy"               => $taskListUpdatedBy,
            'pendingCallbacks'                => $pendingCallbacks
        ];
    }

    /**
     * @param DataSet|DBECustomer $dsCustomer
     * @return string
     */
    function getCustomerNameDisplayClass($dsCustomer)
    {
        if ($dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' && $dsCustomer->getValue(
                DBECustomer::specialAttentionEndDate
            ) >= date('Y-m-d')) {
            return 'specialAttentionCustomer';
        }
        return null;
    }

    private function getActivityExpenses($callActivityID)
    {
        $buExpense = new BUExpense($this);
        $dsExpense = new DataSet($this);
        $buExpense->getExpensesBycallActivityID(
            $callActivityID,
            $dsExpense
        );
        $expenses = array();
        if ($dsExpense->rowCount() > 0) {

            while ($dsExpense->fetchNext()) {

                $expenseID = $dsExpense->getValue(DBEJExpense::expenseID);
                array_push(
                    $expenses,
                    array(
                        'id'          => $expenseID,
                        'expenseType' => $dsExpense->getValue(DBEJExpense::expenseType),
                        'mileage'     => $dsExpense->getValue(DBEJExpense::mileage),
                        'value'       => $dsExpense->getValue(DBEJExpense::value),
                        'vatFlag'     => $dsExpense->getValue(DBEJExpense::vatFlag)
                    )
                );
            }
        }
        return $expenses;
    }

    /**
     * get list of other activities in this problem or project
     */
    function getOtherActivity($problemID)
    {
        $includeTravel             = $_REQUEST["includeTravel"] == 'true' ? true : false;
        $includeOperationalTasks   = $_REQUEST["includeOperationalTasks"] == 'true' ? true : false;
        $includeServerGuardUpdates = $_REQUEST["includeServerGuardUpdates"] == 'true' ? true : false;
        $dbejCallActivity          = new DBEJCallActivity($this);
        $activities                = array();
        $dbejCallActivity->getRowsByproblemID(
            $problemID,
            $includeTravel,
            $includeOperationalTasks,
            false,
            false,
            $includeServerGuardUpdates
        );
        while ($dbejCallActivity->fetchNext()) {
            array_push(
                $activities,
                [
                    "callActivityID" => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID),
                    "dateEngineer"   => $dbejCallActivity->getValue(DBEJCallActivity::dateEngineer),
                    "contactName"    => $dbejCallActivity->getValue(DBEJCallActivity::contactName),
                    "activityType"   => $dbejCallActivity->getValue(DBEJCallActivity::activityType),
                    "date"           => $dbejCallActivity->getValue(DBEJCallActivity::date),
                    "startTime"      => $dbejCallActivity->getValue(DBEJCallActivity::startTime),
                ]
            );
        }
        return $activities;
    }

    private function checkMonitoring($problemID)
    {
        $buActivity = new BUActivity($this);
        return $buActivity->checkMonitoringFlag($problemID);
    }

    private function getOnSiteActivity($callActivityID)
    {
        $buActivity = new BUActivity($this);
        $db         = $buActivity->getOnSiteActivitiesWithinFiveDaysOfActivity($callActivityID);
        $activities = array();
        while ($db->next_record()) {
            array_push(
                $activities,
                [
                    "callActivityID" => $db->Record['caa_callactivityno'],
                    "problemno"      => $db->Record['caa_problemno'],
                    "title"          => $db->Record['cns_name'] . ' on ' . $db->Record['formattedDate'] . ' (' . $db->Record['caa_problemno'] . ')',
                ]
            );
        }
        return $activities;
    }

    private function getAuthorisedBy($dbeJProblem)
    {
        $authorisedByName = "";
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
        $documents = array();
        while ($dbeJCallDocument->fetchNext()) {
            array_push(
                $documents,
                array(
                    'id'             => $dbeJCallDocument->getValue(DBEJCallDocument::callDocumentID),
                    'description'    => $dbeJCallDocument->getValue(DBEJCallDocument::description),
                    'filename'       => $dbeJCallDocument->getValue(DBEJCallDocument::filename),
                    'createUserName' => $dbeJCallDocument->getValue(DBEJCallDocument::createUserName),
                    'createDate'     => $dbeJCallDocument->getValue(DBEJCallDocument::createDate),
                )
            );
        }
        return $documents;
    }

    /**
     * @param DBEJCallActivity $dbejCallActivity
     * @return Boolean
     */
    function isInitalDisabled($dbejCallActivity)
    {
        if (in_array(
            $dbejCallActivity->getValue(DBEJCallActivity::callActTypeID),
            array(
                CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID
            )
        )) {
            return !$this->isSdManager() && !$this->isSRQueueManager();
        }
        return false;
    }

    function messageToSales()
    {
        try {
            $buActivity = new BUActivity($this);
            $this->setMethodName(self::MESSAGE_TO_SALES);
            $body           = file_get_contents('php://input');
            $body           = json_decode($body);
            $message        = $body->message;
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
            return ['error' => true, 'errorDescription' => $ex->getMessage()];
        }
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
        if ($callActivityID) $dbejCallActType->getRow($body->callActTypeID);
        $buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );
        $previousStartTime = $dsCallActivity->getValue(DBECallActivity::startTime);
        $previousEndTime   = $dsCallActivity->getValue(DBECallActivity::endTime);
        $formError         = (!$dsCallActivity->populateFromArray(["1" => $body]));
        if ($formError) {
            http_response_code(400);
            return ["error" => $formError, "type" => "populateFromArray"];
        }
        $dsCallActivity->setUpdateModeUpdate();
        $dsCallActivity->post();
        $dsCallActivity->addColumn('priorityChangeReason', DA_TEXT, true, $body->priorityChangeReason ?? null);
        $dsCallActivity->setValue('priorityChangeReason', $body->priorityChangeReason ?? null);
        $dsCallActivity->addColumn('emptyAssetReason', DA_TEXT, true, $body->emptyAssetReason ?? null);
        $dsCallActivity->setValue('emptyAssetReason', $body->emptyAssetReason ?? null);
        $problemID = $dsCallActivity->getValue(DBECallActivity::problemID);
        $dbeProblem->getRow($problemID);
        if (($previousStartTime != $body->startTime) || ($previousEndTime != $body->endTime) && $dsCallActivity->getValue(
                DBECallActivity::overtimeExportedFlag
            ) == 'N') {

            $dsCallActivity->setValue(DBECallActivity::overtimeDurationApproved, null);
            $dsCallActivity->setValue(DBECallActivity::overtimeApprovedDate, null);
            $dsCallActivity->setValue(DBECallActivity::overtimeApprovedBy, null);
        }
        // if no end time set then set to time now
        if ($body->nextStatus != 'update' && $dbejCallActType->getValue(
                DBEJCallActType::requireCheckFlag
            ) == 'N' && $dbejCallActType->getValue(DBEJCallActType::onSiteFlag) == 'N' && !$body->endTime) {
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
                return ["error" => $timeError];
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
                return ["error" => 'Can not fix, there are open activities on this request'];
            }
            $callback = new DBECallback($this);
            if ($callback->pendingCallbackCountForServiceRequest($problemID)) {
                http_response_code(400);
                return ["error" => 'Can not fix, there are outstanding callbacks on this request'];
            }
            //check Hold all SRs for QA Review
            if ($this->dbeUser->getValue(DBEUser::holdAllSRsforQAReview) == 1) {
                $dsCallActivity->addColumn(DBEProblem::holdForQA, DA_BOOLEAN, false);
                $dsCallActivity->setValue(DBEProblem::holdForQA, 1);
            }
        }
        $dsCallActivity->setUpdateModeUpdate();
        if (isset($body->submitAsOvertime)) {
            $dsCallActivity->setValue(
                DBECallActivity::submitAsOvertime,
                $body->submitAsOvertime
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
            if (!in_array($dbeProblem->getValue(DBEProblem::status), ["I", "F", "C"]) && !$body->escalationReason) {
                http_response_code(400);
                return ["error" => 'Please provide an escalate reason'];
            }
            $dsCallActivity->post();
        }
        if ($updateAwaitingCustomer) {
            $dbeProblem->setValue(
                DBEProblem::awaitingCustomerResponseFlag,
                $dsCallActivity->getValue(DBECallActivity::awaitingCustomerResponseFlag)
            );
            $dbeProblem->updateRow();
        }
        $enteredEndTime = $buActivity->updateCallActivity(
            $dsCallActivity
        );
        /*
            If an end time was entered and this is a chargeable on site activity then see whether to
            create a travel activity automatically OR if one exists for today prompt whether another should be
            added.
            */
        if ($enteredEndTime && $dbejCallActType->getValue(
                DBECallActType::onSiteFlag
            ) == 'Y' && $dbejCallActType->getValue(DBEJCallActType::itemSalePrice) > 0) {
            if ($this->buActivity->travelActivityForCustomerEngineerTodayExists(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID),
                    $dsCallActivity->getValue(DBEJCallActivity::siteNo),
                    $dsCallActivity->getValue(DBEJCallActivity::userID),
                    $dsCallActivity->getValue(DBEJCallActivity::date)
                ) && $body->siteMaxTravelHours > 0    // the site has travel hours
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

    function validTime($body, $dbeProblem, $buActivity, $dbeCallActivity)
    {
        $problemID       = $dbeCallActivity->getValue(DBECallActivity::problemID);
        $callActivityID  = $dbeCallActivity->getValue(DBECallActivity::callActivityID);
        $durationHours   = common_convertHHMMToDecimal(
                $body->endTime
            ) - common_convertHHMMToDecimal($body->startTime);
        $durationMinutes = convertHHMMToMinutes(
                $body->endTime
            ) - convertHHMMToMinutes($body->startTime);
        if (in_array(
            $body->callActTypeID,
            [4, 8, 11, 18]
        )) {
            $userID  = $body->userID;
            $dbeUser = new DBEUser($this);
            $dbeUser->getRow($userID);
            $teamID = $dbeUser->getValue(DBEUser::teamID);
            if ($teamID <= 4) {
                $usedTime      = 0;
                $allocatedTime = 0;
                if ($teamID == 1) {
                    $usedTime      = $buActivity->getHDTeamUsedTime(
                        $problemID,
                        $callActivityID
                    );
                    $allocatedTime = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
                }
                if ($teamID == 2) {
                    $usedTime      = $buActivity->getESTeamUsedTime(
                        $problemID,
                        $callActivityID
                    );
                    $allocatedTime = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
                }
                if ($teamID == 4) {
                    $usedTime      = $buActivity->getSPTeamUsedTime(
                        $problemID,
                        $callActivityID
                    );
                    $allocatedTime = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
                }
                if ($teamID == 5) {
                    $usedTime      = $buActivity->getUsedTimeForProblemAndTeam(
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
        }
        return '';
    }

    /**
     * @param $customerID
     * @param $contactID
     * @param string $templateName
     */
    function getCustomerContacts($templateName = 'ActivityEdit')
    {
        $customerID = $_REQUEST["customerId"];
        $contactID  = $_REQUEST["contactID"];
        if (!isset($customerID)) return [];
        $dbeContact = new DBEContact($this);
        $dbeSite    = new DBESite($this);
        $dbeContact->getRowsByCustomerID(
            $customerID,
            false,
            true,
            true
        );
        $contacts   = array();
        $lastSiteNo = null;
        while ($dbeContact->fetchNext()) {
            $dataDelegate          = "";
            $startMainContactStyle = null;
            $endMainContactStyle   = null;
            if ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelMain) {
                $startMainContactStyle = '*';
                $endMainContactStyle   = '*';
            } elseif ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelDelegate) {
                $startMainContactStyle = '- Delegate';
                $endMainContactStyle   = '- Delegate';
                $dataDelegate          = "data-delegate='true'";
            } elseif ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelSupervisor) {
                $startMainContactStyle = '- Supervisor';
                $endMainContactStyle   = '- Supervisor';
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
            array_push(
                $contacts,
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
        $customerID = $_REQUEST["customerId"];
        if (!isset($customerID)) return [];
        // Site selection
        $dbeSite = new DBESite($this);
        $dbeSite->setValue(
            DBESite::customerID,
            $customerID
        );
        $dbeSite->getRowsByCustomerID();
        $sites = array();
        while ($dbeSite->fetchNext()) {
            $siteDesc = $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                    DBESite::town
                ) . ' ' . $dbeSite->getValue(DBESite::postcode);
            array_push(
                $sites,
                array(
                    'id'   => $dbeSite->getValue(DBESite::siteNo),
                    'name' => $siteDesc
                )
            );
        }
        return $sites;
    }

    function getPriorities()
    {
        $buActivity = new BUActivity($this);
        $priorities = array();
        foreach ($buActivity->priorityArray as $key => $value) {
            array_push(
                $priorities,
                array(
                    'id'   => $key,
                    'name' => $value
                )
            );
        }
        return $priorities;
    }

    function getCustomerContracts()
    {
        $customerID         = $_REQUEST["customerId"];
        $linkedToSalesOrder = $_REQUEST["linkedToSalesOrder"];
        $contracts          = array();
        $buCustomerItem     = new BUCustomerItem($this);
        $dsContract         = new DataSet($this);
        if ($customerID) {
            $buCustomerItem->getContractsByCustomerID(
                $customerID,
                $dsContract,
                null
            );
        }
        while ($dsContract->fetchNext()) {

            $description = $dsContract->getValue(DBEJContract::itemDescription) . ' ' . $dsContract->getValue(
                    DBEJContract::adslPhone
                ) . ' ' . $dsContract->getValue(DBEJContract::notes) . ' ' . $dsContract->getValue(
                    DBEJContract::postcode
                );
            array_push(
                $contracts,
                array(
                    'contractCustomerItemID' => $dsContract->getValue(DBEJContract::customerItemID),
                    'contractDescription'    => $description,
                    'prepayContract'         => $dsContract->getValue(DBEJContract::itemTypeID) == 57,
                    'isDisabled'             => !$dsContract->getValue(
                        DBEJContract::allowSRLog
                    ) || $linkedToSalesOrder == 'true' ? true : false,
                    'renewalType'            => $dsContract->getValue(DBEJContract::renewalType)
                )
            );
        }
        return $contracts;
    }

    function getRootCauses()
    {
        $rootCauses  = array();
        $buRootCause = new BURootCause($this);
        $dsRootCause = new DataSet($this);
        $buRootCause->getAll($dsRootCause);
        while ($dsRootCause->fetchNext()) {
            array_push(
                $rootCauses,
                array(
                    'id'          => $dsRootCause->getValue(DBERootCause::rootCauseID),
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

            $body                = file_get_contents('php://input');
            $body                = json_decode($body);
            $buActivity          = new BUActivity($this);
            $body->date          = date(DATE_MYSQL_DATE);
            $body->startTime     = date('H:i');
            $body->dateRaised    = date(DATE_MYSQL_DATE);
            $body->timeRaised    = date('H:i');
            $body->callActTypeID = CONFIG_INITIAL_ACTIVITY_TYPE_ID;
            $dsCallActivity      = $buActivity->createActivityFromJson($body);
            if (isset($dsCallActivity)) {
                if (isset($body->pendingReopenedID) && isset($body->deletePending) && $body->deletePending == 'true') {
                    //delete pending
                    $dbePendingReopened = new DBEPendingReopened($this);
                    $dbePendingReopened->deleteRow($body->pendingReopenedID);
                }
                $nextURL = "CurrentActivityReport.php";
                if ($body->startWork) {
                    $newActivityID = $buActivity->createFollowOnActivity(
                        $dsCallActivity->getValue(DBEJCallActivity::callActivityID),
                        self::REMOTE_SUPPORT_ACTIVITY_TYPE_ID,
                        false,
                        $this->getParam('reason'),
                        true,
                        false,
                        $GLOBALS['auth']->is_authenticated(),
                        $this->getParam('moveToUsersQueue')
                    );
                    $nextURL       = "SRActivity.php?action=editActivity&callActivityID=" . $newActivityID;
                }
                if (isset($body->customerproblemno) && $body->customerproblemno != null) {
                    $buActivity->deleteCustomerRaisedRequest($body->customerproblemno);
                }
                $problemID  = $dsCallActivity->getValue(DBEJCallActivity::problemID);
                $dbeProblem = new DBEProblem($this);
                $dbeProblem->getRow($problemID);
                return [
                    "status"           => 1,
                    "nextURL"          => $nextURL,
                    "problemID"        => $problemID,
                    "callActivityID"   => $dsCallActivity->getValue(DBEJCallActivity::callActivityID),
                    "raiseTypeId"      => $dbeProblem->getValue(DBEProblem::raiseTypeId),
                    "SLAResponseHours" => $dbeProblem->getValue(DBEProblem::slaResponseHours)
                ];
            } else {
                return ["status" => 0];
            }
        } catch (Exception $exception) {
            return ["status" => 3, "error" => $exception->getMessage()];
        }
    }

    function getCallActivityType()
    {
        $callActivityID = $this->getParam("callActivityID");
        $callActivity   = new DBECallActivity($this);
        $callActivity->getRow($callActivityID);
        return $callActivity->getValue(DBECallActivity::callActTypeID);
    }

    function getCustomerRaisedRequest()
    {
        $Id = $this->getParam("customerproblemno");
        if ($Id) {
            $buActivity = new BUActivity($this);
            return $buActivity->getCustomerRaisedRequest($Id);
        } else return null;
    }

    function getCallActivityBasicInfo()
    {

        $callActivityID = $this->getParam("callActivityID");
        if (isset($callActivityID)) {
            $callActivity = new DBEJCallActivity($this);
            $callActivity->getRow($callActivityID);
            return [
                "callActivityID"              => $callActivity->getValue(DBECallActivity::callActivityID),
                "problemID"                   => $callActivity->getValue(DBECallActivity::problemID),
                "callActTypeID"               => $callActivity->getValue(DBECallActivity::callActTypeID),
                "customerID"                  => $callActivity->getValue(DBEJCallActivity::customerID),
                "customerName"                => $callActivity->getValue(DBEJCallActivity::customerName),
                "contactID"                   => $callActivity->getValue(DBEJCallActivity::contactID),
                "contractCustomerItemID"      => $callActivity->getValue(DBEJCallActivity::contractCustomerItemID),
                "linkedSalesOrderID"          => $callActivity->getValue(DBEJCallActivity::linkedSalesOrderID),
                "problemHideFromCustomerFlag" => $callActivity->getValue(DBEJCallActivity::problemHideFromCustomerFlag),
                "rootCauseID"                 => $callActivity->getValue(DBEJCallActivity::rootCauseID),
                "prePayChargeApproved"        => $callActivity->getValue(DBEJCallActivity::prePayChargeApproved)
            ];
        } else return null;
    }

    function saveFixedInformation()
    {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        if (!isset($body->problemID) || !isset($body->contractCustomerItemID) || !isset($body->rootCauseID) || !isset($body->resolutionSummary)) {
            http_response_code(400);
            return ["error" => $body];
        }
        $buActivity = new BUActivity($this);
        $buActivity->setProblemToFixed(
            $body->problemID,
            false,
            $body->contractCustomerItemID,
            $body->rootCauseID,
            $body->resolutionSummary
        );
        return ["status" => true];
    }

    function getInitialActivity()
    {
        $problemID = $_REQUEST["problemID"];
        if (!isset($problemID)) return null;
        $buActivity       = new BUActivity($this);
        $dbeJCallActivity = $buActivity->getFirstActivityInServiceRequest($problemID, 57);//initial activity
        if ($dbeJCallActivity) return [
            "callActivityID" => $dbeJCallActivity->getValue(DBEJCallActivity::callActivityID),
            "reason"         => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
        ]; else
            return null;

    }

    function saveManagementReviewDetails()
    {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        if (!isset($body->problemID) || !isset($body->description)) {
            http_response_code(400);
            return ["error" => $body];
        }
        $buActivity = new BUActivity($this);
        $buActivity->updateManagementReviewReason(
            $body->problemID,
            $body->description
        );
        return ["status" => true];
    }

    function changeProblemPriority()
    {
        $body = file_get_contents('php://input');
        $body = json_decode($body);
        if (!isset($body->callActivityID) || !isset($body->priorityChangeReason) || !isset($body->priority)) {
            http_response_code(400);
            return ["error" => $body];
        }
        $buActivity = new BUActivity($this);
        return $buActivity->updateCallActivityPriority(
            $body->callActivityID,
            $body->priority,
            $body->priorityChangeReason
        );
    }

    function setTemplate()
    {
        $this->setMethodName('setTemplate');
        $this->setMenuId(102);
        list($title, $header) = $this->getTitle();
        $this->setPageTitle($title, $header);
        $this->setTemplateFiles(
            'Activity',
            'Activity.inc'
        );
        $this->loadReactScript('ActivityComponent.js');
        $this->loadReactCSS('ActivityComponent.css');
        $this->template->parse(
            'CONTENTS',
            'Activity',
            true
        );
        $this->parsePage();
    }

    function getTitle()
    {
        $action         = $this->getAction();
        $problemID      = $this->getParam('serviceRequestId');
        $callActivityID = null;
        if (isset($_REQUEST['callActivityID'])) {
            $dbeCallActivity = new DBECallActivity($this);
            $callActivityID  = $_REQUEST['callActivityID'];
            if (isset($callActivityID)) {
                $dbeCallActivity->setPKValue($callActivityID);
                $dbeCallActivity->getRow();
                $problemID = $dbeCallActivity->getValue(DBECallActivity::problemID);
            }
        }
        $dbeProblem = new DBEProblem($this);
        if ($problemID) {
            $dbeProblem->setPKValue($problemID);
            $dbeProblem->getRow();
        }
        switch ($action) {
            case "displayActivity":
                return [
                    "Service Request $problemID",
                    "Service Request $problemID {$this->getProblemRaiseIcon($dbeProblem)}"
                ];
            case "editActivity":
                return [
                    "Edit Service Request $problemID",
                    "Edit Service Request $problemID {$this->getProblemRaiseIcon($dbeProblem)}"
                ];
            case "gatherFixedInformation":
                return [
                    "Service Request Fix Summary {$problemID}",
                    "Service Request Fix Summary {$problemID} {$this->getProblemRaiseIcon($dbeProblem)}"
                ];
            case "gatherManagementReviewDetails":
                return ["Management Review Reason"];
            default:
                return ["Activity"];
        }
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
                $title  = "";
                switch ($dbeProblemRaiseType->getValue(DBEProblemRaiseType::description)) {
                    case 'Email':
                        $return .= "<i class='fal fa-envelope ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised by email";
                        break;
                    case 'Portal':
                        $return .= "<i class='icon-chrome_icon' style='font-size: 18px; margin:5px; color:#000080 ' ></i>";
                        $title  = "This Service Request was raised by the portal";
                        break;
                    case 'Phone':
                        $return .= "<i class='fal fa-phone ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised by phone";
                        break;
                    case 'On site':
                        $return .= "<i class='fal fa-building ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised by an on site engineer";
                        break;
                    case 'Alert':
                        $return .= "<i class='fal fa-bell ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised by an alert";
                        break;
                    case 'Sales':
                        $return .= "<i class='fal fa-shopping-cart ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised via Sales";
                        break;
                    case 'Manual':
                        $return .= "<i class='fal fa-user-edit ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised manually";
                        break;
                }
                $return .= "<div class='tooltiptext tooltip-bottom' style='width:300px' >$title</div> </div> ";
                return $return;
            }
        }
        return null;
    }

    function usedBudgetData()
    {
        $problemID = $_REQUEST["problemID"];
        $startTime = '08:00';
        $endTime   = '18:00';
        // here we get the information about the inHours and outOfHours time used
        $query = "SELECT 
  ROUND(
      COALESCE(
          SUM(
        IF(
        isBankHoliday (`caa_date`),
          0,
          TIME_TO_SEC(
            IF(
                caa_endtime < '$startTime',
              '$startTime',
              IF(
                  caa_endtime > '$endTime',
                '$endTime',
                caa_endtime
              )
            )
          ) - TIME_TO_SEC(
            IF(
                caa_starttime >= '$startTime',
              IF(
                  caa_starttime > '$endTime',
                '$endTime',
                caa_starttime
              ),
              '$startTime'
            )
          )
        )
      ) / 3600,
      0
    ),
    2
  ) AS inHours,
  ROUND(
      COALESCE(
          SUM(
        IF(
        isBankHoliday (`caa_date`),
          COALESCE(
              TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
              0
          ),
          IF(
              caa_starttime < '$startTime',
            COALESCE(
                TIME_TO_SEC(IF(caa_endtime >  '$startTime',  '$startTime', caa_endtime)) - TIME_TO_SEC(caa_starttime),
                0
            ),
            0
          ) + IF(
        caa_endtime > '$endTime',
            COALESCE(
                TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(IF(caa_starttime < '$endTime', '$endTime', caa_starttime)),
                0
            ),
            0
          )
        )
      ) / 3600,
      0
    ),
    2
  ) AS outHours,
  callactivity.`caa_callacttypeno`,
  callacttype.`cat_desc`,
  callactivity.`caa_consno`,
  consultant.`firstName`,
  consultant.`lastName` 
FROM
  callactivity 
  LEFT JOIN problem 
    ON callactivity.`caa_problemno` = problem.`pro_problemno` 
  LEFT JOIN callacttype 
    ON callactivity.`caa_callacttypeno` = callacttype.`cat_callacttypeno` 
  LEFT JOIN consultant 
    ON `callactivity`.`caa_consno` = consultant.`cns_consno` 
WHERE problem.`pro_problemno` = $problemID and caa_starttime <> '' and caa_starttime is not null and caa_endtime <> '' and caa_endtime is not null 
and callactivity.`caa_callacttypeno` <> 51 and callactivity.`caa_callacttypeno` <> 60 and callactivity.`caa_callacttypeno` <> 35 and caa_consno <> 67
GROUP BY caa_callacttypeno,
  caa_consno";
        global $db;
        $db->query($query);
        $data = [];
        while ($db->next_record(MYSQLI_ASSOC)) {
            $data[] = $db->Record;
        }
        return $data;
    }

    function setToggleHoldForQAFlag()
    {
        $problemID = $this->getParam("problemID");
        if (isset($problemID)) {
            $dbeProblem = new DBEProblem($this);
            $dbeProblem->getRow($problemID);
            $dbeProblem->setValue(DBEProblem::holdForQA, !$dbeProblem->getValue(DBEProblem::holdForQA));
            $dbeProblem->updateRow();
            return ["state" => true];
        }
        return ["state" => false];
    }


    function getNotAttemptFirstTimeFix()
    {
        $problemID  = $_REQUEST["problemID"] ?? null;
        $userID     = $_REQUEST["userID"] ?? null;
        $startDate  = $_REQUEST["startDate"] ?? null;
        $endDate    = $_REQUEST["endDate"] ?? null;
        $customerID = $_REQUEST["customerID"] ?? null;
        $query      = "SELECT
  problem.`pro_problemno` as problemID,
  customer.`cus_name` as customerName,
  engineer.`cns_name` as userName,
  problem.`notFirstTimeFixReason` as reason,
  customer.cus_custno as customerID
FROM
  problem 
  JOIN callactivity initial 
    ON initial.caa_problemno = problem.pro_problemno 
    AND initial.caa_callacttypeno = 51 
  JOIN consultant engineer 
    ON initial.`caa_consno` = engineer.`cns_consno` 
    JOIN customer ON customer.`cus_custno` = problem.`pro_custno`
   JOIN
    (SELECT
      COUNT(item.`itm_itemno`) AS items,
      custitem.`cui_custno`
    FROM
      custitem
      JOIN item
        ON cui_itemno = itm_itemno
    WHERE `itm_itemtypeno` = 56
      AND cui_expiry_date >= NOW()
      AND renewalStatus <> 'D'
      AND declinedFlag <> 'Y'
    GROUP BY cui_custno) a
    ON a.cui_custno = problem.`pro_custno`
    where problem.`pro_custno` <> 282 AND problem.raiseTypeId = 3 and pro_priority < 4 and a.items and notFirstTimeFixReason is not null";
        $params     = [];
        if (isset($problemID) && $problemID != '') {
            $query               .= " and problem.pro_problemno = :problemID";
            $params["problemID"] = $problemID;
        }
        if (isset($customerID) && $customerID != '') {
            $query                .= " and problem.`pro_custno` = :customerID";
            $params["customerID"] = $customerID;
        }
        if (isset($userID) && $userID != '') {
            $query            .= " and engineer.`cns_consno`= :userID";
            $params["userID"] = $userID;
        }
        if (isset($startDate) && $startDate != '') {
            $query               .= " and initial.caa_date >= :startDate";
            $params["startDate"] = $startDate;
        }
        if (isset($endDate) && $endDate != '') {
            $query             .= " and initial.caa_date <= :endDate";
            $params["endDate"] = $endDate;
        }
        return DBConnect::fetchAll($query, $params);
    }

    private function getDocumentsForServiceRequestController()
    {
        $serviceRequestId = $this->getParam('serviceRequestId');
        $documents        = $this->internalDocumentRepository->getServiceRequestsDocuments($serviceRequestId);
        $documentsJSON    = InternalDocumentMapper::fromDomainArrayToJSONDTO(
            $documents
        );
        return ["status" => "ok", "data" => $documentsJSON];
    }

    private function addServiceRequestsUploadedDocuments()
    {
        $data             = $this->getJSONData();
        $serviceRequestId = @$data['serviceRequestId'];
        $filesArray       = @$data['files'];
        $files            = Base64FileDTO::fromArray($filesArray);
        $usecase          = new AddDocumentsToServiceRequest(
            $this->internalDocumentRepository, new DBEProblem($this)
        );
        $usecase->__invoke($serviceRequestId, $files);
        return ["status" => "ok"];
    }

    private function viewInternalDocument()
    {
        $documentId = $this->getParam('documentId');
        if (!$documentId) {
            throw new JsonHttpException(25, "Document Id required");
        }
        try {
            $internalDocument = $this->internalDocumentRepository->getById($documentId);
            header('Content-type: ' . $internalDocument->mimeType());
            header('Content-Disposition: attachment; filename="' . $internalDocument->originalFileName() . '"');
            echo $internalDocument->getFileContents();
        } catch (Exception $exception) {
            throw new JsonHttpException(51, "Document not found");
        }
    }

    private function deleteInternalDocument()
    {
        $documentId = $this->getParam('documentId');
        if (!$documentId) {
            throw new JsonHttpException(25, "Document Id required");
        }
        try {
            $internalDocument = $this->internalDocumentRepository->getById($documentId);
            $this->internalDocumentRepository->deleteDocument($internalDocument);
            return ["status" => "ok"];
        } catch (Exception $exception) {
            throw new JsonHttpException(2215, "Failed to delete document");
        }
    }

    private function addInternalNoteController(): array
    {
        $data             = $this->getJSONData();
        $serviceRequestId = @$data['serviceRequestId'];
        $content          = @$data['content'];
        if (!$serviceRequestId) {
            throw new JsonHttpException(231, "Service request ID required");
        }
        if (!$content) {
            throw new JsonHttpException(321, "Content required");
        }
        $dbeProblem = new DBEProblem($this);
        if (!$dbeProblem->getRow($serviceRequestId)) {
            throw new JsonHttpException(123, "Service Request Not Found!");
        }
        $usecase = new AddServiceRequestInternalNote($this->serviceRequestInternalNoteRepository);
        try {
            $usecase->__invoke($dbeProblem, $this->getDbeUser(), $content);
        } catch (Exception $exception) {
            throw new JsonHttpException(123, $exception->getMessage());
        }
        return ["status" => "ok"];
    }

    private function saveTaskListController()
    {
        $data             = $this->getJSONData();
        $serviceRequestId = @$data['serviceRequestId'];
        $content          = @$data['content'];
        if (!$serviceRequestId) {
            throw new JsonHttpException(231, "Service request ID required");
        }
        if (!$content) {
            throw new JsonHttpException(321, "Content required");
        }
        $dbeProblem = new DBEProblem($this);
        if (!$dbeProblem->getRow($serviceRequestId)) {
            throw new JsonHttpException(123, "Service Request Not Found!");
        }
        $dbeProblem->setValue(DBEProblem::taskList, $content);
        $dbeProblem->setValue(DBEProblem::taskListUpdatedAt, (new DateTimeImmutable())->format(DATE_MYSQL_DATETIME));
        $dbeProblem->setValue(DBEProblem::taskListUpdatedBy, $this->userID);
        $dbeProblem->updateRow();
        return ["status" => "ok"];
    }

    /**
     * @return string[]
     * @throws JsonHttpException
     */
    private function addAdditionalTimeRequestController(): array
    {
        $data = $this->getJSONData();
        try {
            $serviceRequestId  = (int)@$data['serviceRequestId'];
            $reason            = @$data['reason'];
            $timeRequested     = (int)@$data['timeRequested'];
            $selectedContactId = (int)@$data['selectedContactId'];
            $repo              = new ChargeableWorkCustomerRequestMySQLRepository();
            $buActivity        = new BUActivity($this);
            $serviceRequest    = new DBEProblem($this);
            if (!$serviceRequest->getRow($serviceRequestId)) {
                throw new ServiceRequestNotFoundException();
            }
            $usecase = new CreateChargeableWorkCustomerRequest($repo, $buActivity);
            $usecase->__invoke($serviceRequest, $this->dbeUser, $timeRequested, $reason, $selectedContactId);
        } catch (Exception $exception) {
            throw new JsonHttpException(400, $exception->getMessage());
        }
        return ["status" => "ok"];
    }

    /**
     * @return array
     * @throws ServiceRequestNotFoundException
     * @throws ChargeableWorkCustomerRequestNotFoundException
     */
    private function getAdditionalChargeableWorkRequestInfoController(): array
    {
        $chargeableWorkRequestId = @$_REQUEST['id'];
        $repo                    = new ChargeableWorkCustomerRequestMySQLRepository();
        $usecase                 = new GetPendingToProcessChargeableRequestInfo($repo);
        $data                    = $usecase(new ChargeableWorkCustomerRequestTokenId($chargeableWorkRequestId));
        return [
            "status" => "ok",
            "data"   => $data
        ];
    }

    private function checkServiceRequestPendingCallbacksController()
    {
        $data             = $this->getJSONData();
        $serviceRequestId = (int)@$data['serviceRequestId'];
        $dbeCallback      = new DBECallback($this);
        $count            = $dbeCallback->pendingCallbackCountForServiceRequest($serviceRequestId);
        return [
            "status" => "ok",
            "data"   => (bool)$count
        ];
    }
}

?>