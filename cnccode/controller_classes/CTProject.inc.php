<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\JcTable;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUSalesOrder.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEOrdhead.inc.php');
require_once($cfg['path_bu'] . '/BUExpense.inc.php');
// Actions
define(
    'CTPROJECT_ACT_DISPLAY_LIST',
    'projectList'
);
define(
    'CTPROJECT_ACT_ACT',
    'add'
);
define(
    'CTPROJECT_ACT_EDIT',
    'edit'
);
define(
    'CTPROJECT_ACT_DELETE',
    'delete'
);
define(
    'CTPROJECT_ACT_UPDATE',
    'update'
);

define('CTPROJECT_MSG_DOCUMENT_NOT_LOADED', 'DOCUMENT NOT LOADED');
define('CTPROJECT_MAX_DOCUMENT_FILE_SIZE', 'MAX DOCUMENT FILE SIZE');
define('CTPROJECT_MSG_DOCUMENT_TOO_BIG', 'DOCUMENT TOO BIG');


class CTProject extends CTCNC
{
    const inHoursQuantity = "inHoursQuantity";
    const inHoursMeasure = "inHoursMeasure";
    const outOfHoursQuantity = "outOfHoursQuantity";
    const outOfHoursMeasure = "outOfHoursMeasure";

    const UPLOAD_PROJECT_PLAN = "uploadProjectPlan";
    const DOWNLOAD_PROJECT_PLAN = "downloadProjectPlan";
    const CALCULATE_BUDGET = "calculateBudget";
    const DAILY_LABOUR_CHARGE = 1502;
    const HOURLY_LABOUR_CHARGE = 2237;
    const DAILY_OOH_LABOUR_CHARGE = 1503;
    const HOURLY_OOH_LABOUR_CHARGE = 16865;
    const GET_BUDGET_DATA = "getBudgetData";
    const CURRENT_PROJECTS_REPORT = "currentProjectsReport";
    var $dsProject = '';
    /** @var BUProject */
    var $buProject;

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
            "technical"
        ];

        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buProject = new BUProject($this);
        $this->dsProject = new DSForm($this);
        $this->dsProject->copyColumnsFrom($this->buProject->dbeProject);
        $this->dsProject->setAddColumnsOn();
        $this->dsProject->addColumn(
            self::inHoursQuantity,
            DA_INTEGER,
            DA_ALLOW_NULL
        );

        $this->dsProject->addColumn(
            self::inHoursMeasure,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->dsProject->addColumn(
            self::outOfHoursQuantity,
            DA_INTEGER,
            DA_ALLOW_NULL
        );

        $this->dsProject->addColumn(
            self::outOfHoursMeasure,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->dsProject->setAddColumnsOff();
        $this->setMenuId(107);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case CTPROJECT_ACT_EDIT:
            case CTPROJECT_ACT_ACT:
                $this->edit();
                break;
            case CTPROJECT_ACT_DELETE:
                $this->delete();
                break;
            case 'popup':
                $this->popup();
                break;
            case CTPROJECT_ACT_UPDATE:
                $this->update();
                break;
            case 'historyPopup':
                $this->historyPopup();
                break;
            case 'lastUpdate':
                $this->historyPopup(true);
                break;
            case 'editLinkedSalesOrder':
                $this->editLinkedSalesOrder();
                break;
            case 'unlinkSalesOrder':
                $this->unlinkSalesOrder();
                break;
            case self::UPLOAD_PROJECT_PLAN:
                $response = [];
                try {
                    $this->uploadProjectPlan();
                    $response['status'] = "ok";
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response['status'] = "error";
                    $response['error'] = $exception->getMessage();
                }
                echo json_encode($response);
                break;
            case self::DOWNLOAD_PROJECT_PLAN:

                if (!$this->getParam('projectID')) {
                    echo 'Project ID missing';
                    http_response_code(400);
                    exit;
                }
                $dbeDocuments = new DBEProject($this);
                $dbeDocuments->getRow($this->getParam('projectID'));

                header('Content-Description: File Transfer');
                header('Content-Type: ' . $dbeDocuments->getValue(DBEProject::planMIMEType));
                header(
                    'Content-Disposition: attachment; filename="' . $dbeDocuments->getValue(
                        DBEProject::planFileName
                    ) . '"'
                );
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . strlen($dbeDocuments->getValue(DBEProject::planFile)));
                echo $dbeDocuments->getValue(DBEProject::planFile);
                exit;
            case self::CALCULATE_BUDGET:
                $this->calculateBudget();
                break;
            case self::GET_BUDGET_DATA:
                $response = [];
                try {
                    $response['data'] = $this->fetchBudgetData();
                    $response['status'] = "ok";
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response['status'] = "error";
                    $response['error'] = $exception->getMessage();
                }
                echo json_encode(
                    $response,
                    JSON_NUMERIC_CHECK
                );
                break;
            case self::CURRENT_PROJECTS_REPORT:
                $this->currentProjectReport();
                break;
            default:
                $this->showList();
        }
    }

    /**
     * Edit/Add Further Action
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsProject = &$this->dsProject; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == CTPROJECT_ACT_EDIT) {
                $this->buProject->getProjectByID(
                    $this->getParam('projectID'),
                    $dsProject
                );
                $projectID = $this->getParam('projectID');
            } else {                                                                    // creating new
                $dsProject->initialise();
                $dsProject->setValue(
                    DBEProject::projectID,
                    '0'
                );
                $dsProject->setValue(
                    DBEProject::customerID,
                    $this->getParam('customerID')
                );
                $projectID = '0';
            }
        } else {                                                                        // form validation error
            $dsProject->initialise();
            $dsProject->fetchNext();
            $projectID = $dsProject->getValue(DBEProject::projectID);
        }
        if ($this->getAction() == CTPROJECT_ACT_EDIT && $this->buProject->canDelete($this->getParam('projectID'))) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTPROJECT_ACT_DELETE,
                        'projectID' => $projectID
                    )
                );
            $txtDelete = 'Delete';
        } else {
            $urlDelete = '';
            $txtDelete = '';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTPROJECT_ACT_UPDATE,
                    'projectID' => $projectID
                )
            );
        $urlDisplayCustomer =
            Controller::buildLink(
                'Customer.php',
                array(
                    'customerID' => $this->dsProject->getValue(DBEProject::customerID),
                    'action'     => CTCNC_ACT_DISP_EDIT
                )
            );
        $this->setPageTitle('Edit Project');
        $this->setTemplateFiles(
            array('ProjectEdit' => 'ProjectEdit.inc')
        );

        $this->renderConsultantBlock(
            'ProjectEdit',
            $dsProject->getValue(DBEProject::projectEngineer)
        );

        global $db;

        $result = $db->preparedQuery(
            "select * from projectUpdates where projectID = ? order by createdAt desc limit 1",
            [['type' => 'i', 'value' => $projectID]]
        );

        $row = $result->fetch_assoc();
        $formattedDate = null;
        if ($row['createdAt']) {
            $updateDate = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $row['createdAt']
            );
            $formattedDate = $updateDate->format('d/m/Y H:i');
        }

        $historyPopupURL = Controller::buildLink(
            'Project.php',
            array(
                'action'    => 'historyPopup',
                'projectID' => $dsProject->getValue(DBEProject::projectID),
                'htmlFmt'   => CT_HTML_FMT_POPUP
            )
        );
        $linkServiceRequest = "";
        if ($dsProject->getValue(DBEProject::ordHeadID)) {
            $buSalesOrder = new BUSalesOrder($this);

            $linkedServiceRequestCount = $buSalesOrder->countLinkedServiceRequests(
                $dsProject->getValue(DBEProject::ordHeadID)
            );

            if ($linkedServiceRequestCount == 1) {

                $problemID = $buSalesOrder->getLinkedServiceRequestID($dsProject->getValue(DBEProject::ordHeadID));

                $urlServiceRequest =
                    Controller::buildLink(
                        'Activity.php',
                        array(
                            'action'    => 'displayFirstActivity',
                            'problemID' => $problemID
                        )
                    );

                $linkServiceRequest = '<a href="' . $urlServiceRequest . '" target="_blank"><div>View SR</div></a>';

            } else {     // many SRs so display search page
                $urlServiceRequest =
                    Controller::buildLink(
                        'Activity.php',
                        array(
                            'action'             => 'search',
                            'linkedSalesOrderID' => $dsProject->getValue(DBEProject::ordHeadID)
                        )
                    );

                $linkServiceRequest = '<a href="' . $urlServiceRequest . '" target="_blank"><div>View SRs</div></a>';

            }
        }


        $urlLinkedSalesOrder =
            Controller::buildLink(
                'Project.php',
                array(
                    'action'    => 'editLinkedSalesOrder',
                    'htmlFmt'   => CT_HTML_FMT_POPUP,
                    'projectID' => $dsProject->getValue(DBEProject::projectID)
                )
            );

        $uploadProjectPlanURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action'    => self::UPLOAD_PROJECT_PLAN,
                'projectID' => $projectID
            ]
        );

        $hasProjectPlan = !!$dsProject->getValue(DBEProject::planFileName);

        $projectPlanDownloadURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                [
                    'action'    => self::DOWNLOAD_PROJECT_PLAN,
                    'projectID' => $projectID
                ]
            );

        $downloadProjectPlanClass = $hasProjectPlan ? '' : 'class="redText"';
        $downloadProjectPlanURL = $hasProjectPlan ? "href='$projectPlanDownloadURL' target='_blank' " : 'href="#"';
        $projectPlanLink = "<a id='projectPlanLink' $downloadProjectPlanClass $downloadProjectPlanURL>Project Plan</a>";

        $projectCalculateBudgetURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                [
                    'action'    => self::CALCULATE_BUDGET,
                    'projectID' => $projectID
                ]
            );

        $projectCalculateBudgetClass = null;
        $projectCalculateBudgetURL = "href='$projectCalculateBudgetURL'";
        $projectCalculateBudgetLinkClick = "onclick='return confirm(\"Are you sure? You can only do this once.\")'";
        $isProjectManager = $this->dbeUser->getValue(DBEUser::projectManagementFlag) === 'Y';

        if ($dsProject->getValue(DBEProject::calculatedBudget) == 'Y' || !$isProjectManager) {
            $projectCalculateBudgetURL = "href='#'";
            $projectCalculateBudgetClass = "class='grayedOut'";
            $projectCalculateBudgetLinkClick = null;
        }

        $projectCalculateBudgetLink = null;


        if ($dsProject->getValue(DBEProject::ordHeadID)) {
            $projectCalculateBudgetLink = "<a  $projectCalculateBudgetURL  $projectCalculateBudgetClass $projectCalculateBudgetLinkClick>Calculate Budget</a>";
        }

        $fetchProjectDataURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action'    => self::GET_BUDGET_DATA,
                'projectID' => $projectID
            ]
        );


        $this->template->set_var(
            array(
                'customerID'              => $dsProject->getValue(DBEProject::customerID),
                'projectID'               => $projectID,
                'description'             => Controller::htmlInputText(
                    $dsProject->getValue(DBEProject::description)
                ),
                'descriptionMessage'      => Controller::htmlDisplayText(
                    $dsProject->getMessage(DBEProject::description)
                ),
                'notes'                   => Controller::htmlInputText($dsProject->getValue(DBEProject::notes)),
                'notesMessage'            => Controller::htmlDisplayText($dsProject->getMessage(DBEProject::notes)),
                'startDate'               => $dsProject->getValue(DBEProject::openedDate),
                'startDateMessage'        => Controller::htmlDisplayText(
                    $dsProject->getMessage(DBEProject::openedDate)
                ),
                'expiryDate'              => $dsProject->getValue(DBEProject::completedDate),
                'expiryDateMessage'       => Controller::htmlDisplayText(
                    $dsProject->getMessage(DBEProject::completedDate)
                ),
                'commenceDate'            => $dsProject->getValue(DBEProject::commenceDate),
                'commenceDateMessage'     => Controller::htmlDisplayText(
                    $dsProject->getMessage(DBEProject::commenceDate)
                ),
                'urlUpdate'               => $urlUpdate,
                'urlDelete'               => $urlDelete,
                'txtDelete'               => $txtDelete,
                'urlDisplayCustomer'      => $urlDisplayCustomer,
                'lastUpdateDate'          => $formattedDate,
                'lastUpdateEngineer'      => $row['createdBy'],
                'lastUpdateComment'       => $row['comment'],
                'historyPopupURL'         => $historyPopupURL,
                'salesOrderLink'          => $this->getSalesOrderLink(
                    $dsProject->getValue(DBEProject::ordHeadID),
                    $dsProject->getValue(DBEProject::projectID)
                ),
                'urlLinkedSalesOrder'     => $urlLinkedSalesOrder,
                'uploadProjectPlanURL'    => $uploadProjectPlanURL,
                'hasProjectPlan'          => $hasProjectPlan ? "true" : "false",
                'projectPlanLink'         => $projectPlanLink,
                'projectPlanDownloadURL'  => $projectPlanDownloadURL,
                'calculateBudgetLink'     => $projectCalculateBudgetLink,
                'getProjectBudgetDataURL' => $fetchProjectDataURL,
                'projectManagementCheck'  => $isProjectManager ? '' : 'readonly disabled',
                'viewSRLink'              => $linkServiceRequest
            )
        );
        $this->template->parse(
            'CONTENTS',
            'ProjectEdit',
            true
        );
        $this->parsePage();
    }

    private function renderConsultantBlock(string $parentPage,
                                           $selectedID
    )
    {
        $this->template->set_block(
            $parentPage,
            'consultantBlock',
            'consultants'
        );

        $dbeConsultant = new DBEUser($this);

        $dbeConsultant->getActiveUsers();

        while ($dbeConsultant->fetchNext()) {

            $this->template->setVar(
                array(
                    'consultantSelected' => $selectedID == $dbeConsultant->getValue(
                        DBEUser::userID
                    ) ? 'selected' : null,
                    'consultantID'       => $dbeConsultant->getValue(DBEUser::userID),
                    'consultantName'     => $dbeConsultant->getValue(
                            DBEUser::firstName
                        ) . ' ' . $dbeConsultant->getValue(DBEUser::lastName)
                )
            );
            $this->template->parse(
                'consultants',
                'consultantBlock',
                true
            );
        }
    }

    /**
     * @param $linkedOrdheadID
     * @param $projectID
     * @return string
     * @throws Exception
     */
    function getSalesOrderLink($linkedOrdheadID, $projectID)
    {
        if ($linkedOrdheadID) {

            $linkURL =
                Controller::buildLink(
                    'SalesOrder.php',
                    array(
                        'action'    => 'displaySalesOrder',
                        'ordheadID' => $linkedOrdheadID
                    )
                );
            return '<a href="?action=unlinkSalesOrder&projectId=' . $projectID . '" onclick="return confirm(\'Are you sure you want to unlink this project to Sales Order ' . $linkedOrdheadID . '?\');">Unlink</a> <a href="' . $linkURL . '" target="_blank" title="Sales Order">Sales Order</a>';
        }
        return ' <a href="#" style="color: red" onclick="linkedSalesOrderPopup()">Sales Order</a>';
    }// end function editFurther Action()

    /**
     * Delete Further Action
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        $dsProject = new DataSet($this);
        $this->buProject->getProjectByID(
            $this->getParam('projectID'),
            $dsProject
        );

        if (!$this->buProject->deleteProject($this->getParam('projectID'))) {
            $this->displayFatalError('Cannot delete this project');
            exit;
        } else {
            $urlNext =
                Controller::buildLink(
                    'Customer.php',
                    array(
                        'customerID' => $dsProject->getValue(DBEProject::customerID),
                        'action'     => CTCNC_ACT_DISP_EDIT
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    /**
     * @throws Exception
     */
    function popup()
    {
        $dsProject = new DataSet($this);
        $this->buProject->getProjectByID(
            $this->getParam('projectID'),
            $dsProject
        );
        $this->setPageTitle('Project: ' . Controller::htmlDisplayText($dsProject->getValue(DBEProject::description)));
        $this->setTemplateFiles(
            array('ProjectPopup' => 'ProjectPopup.inc')
        );
        $this->template->set_var(
            array(
                'notes'      => $dsProject->getValue(DBEProject::notes),
                'startDate'  => Controller::dateYMDtoDMY($dsProject->getValue(DBEProject::openedDate)),
                'expiryDate' => Controller::dateYMDtoDMY($dsProject->getValue(DBEProject::completedDate)),
            )
        );
        $this->template->parse(
            'CONTENTS',
            'ProjectPopup',
            true
        );
        $this->parsePage();

    }

    /**
     * Update call Further Action details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $buHeader = new BUHeader($this);
        $dbeHeader = new DataSet($this);
        $buHeader->getHeader($dbeHeader);

        $dbeProject = new DBEProject($this);
        $projectID = $this->getParam('projectID');

        $dbeProject->getRow($projectID);

        $this->dsProject->replicate($dbeProject);

        $this->formError = (!$this->dsProject->populateFromArray($this->getParam('project')));
        if ($this->formError) {
            if ($this->dsProject->getValue(DBEProject::projectID) == '') {                    // attempt to insert
                $this->setAction(CTPROJECT_ACT_EDIT);
            } else {
                $this->setAction(CTPROJECT_ACT_ACT);
            }
            $this->edit();
            exit;
        }

        if ($this->dsProject->getValue(self::inHoursQuantity)) {
            $toAddDays = null;
            // we need to add the amount of hours or days to the in hours budget
            $currentDays = (float)$this->dsProject->getValue(DBEProject::inHoursBudgetDays);
            switch ($this->dsProject->getValue(self::inHoursMeasure)) {
                case 'hours':
                    $toAddMinutes = (int)$this->dsProject->getValue(self::inHoursQuantity) * 60;
                    $toAddDays = $toAddMinutes / $dbeHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay);
                    break;
                case 'days':
                    $toAddDays = (float)$this->dsProject->getValue(self::inHoursQuantity);
            }

            $this->dsProject->setValue(
                DBEProject::inHoursBudgetDays,
                $currentDays + $toAddDays
            );
            $this->dsProject->setUpdateModeUpdate();
            $this->dsProject->post();
        }

        if ($this->dsProject->getValue(self::outOfHoursQuantity)) {
            $toAddDays = null;
            // we need to add the amount of hours or days to the in hours budget
            $currentDays = (float)$this->dsProject->getValue(DBEProject::outOfHoursBudgetDays);
            switch ($this->dsProject->getValue(self::outOfHoursMeasure)) {
                case'hours':
                    $toAddMinutes = (int)$this->dsProject->getValue(self::outOfHoursQuantity) * 60;
                    $toAddDays = $toAddMinutes / $dbeHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay);
                    break;
                case 'days':
                    $toAddDays = (float)$this->dsProject->getValue(self::outOfHoursQuantity);
            }

            $this->dsProject->setValue(
                DBEProject::outOfHoursBudgetDays,
                $currentDays + $toAddDays
            );
            $this->dsProject->setUpdateModeUpdate();
            $this->dsProject->post();

        }

        $this->buProject->updateProject($this->dsProject);

        if (!empty($this->getParam('newComment'))) {

            global $db;

            $parameters = [
                [
                    'type'  => 's',
                    'value' => $this->dbeUser->getValue(DBEUser::firstName) . " " . $this->dbeUser->getValue(
                            DBEUser::lastName
                        )
                ],
                [
                    'type'  => 'i',
                    'value' => (int)$this->dsProject->getValue(DBEProject::projectID)
                ],
                [
                    'type'  => 's',
                    'value' => $this->getParam('newComment')
                ]
            ];

            $db->preparedQuery(
                "insert into projectUpdates(createdBy,projectID,comment) values (?, ?, ?)",
                $parameters
            );
        }

        $urlNext =
            Controller::buildLink(
                'Project.php',
                array(
                    'projectID' => $this->dsProject->getValue(DBEProject::projectID),
                    'action'    => 'edit'
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * @param bool $lastUpdateOnly
     * @throws Exception
     */
    private function historyPopup($lastUpdateOnly = false)
    {
        $this->setPageTitle('Project Updates History');
        $this->setTemplateFiles(
            array('ProjectPopup' => 'ProjectUpdatesHistoryPopup')
        );

        global $db;
        $query = "select * from projectUpdates where projectID = ? order by createdAt desc";

        if ($lastUpdateOnly) {
            $query .= " limit 1";
        }
        $result = $db->preparedQuery(
            $query,
            [['type' => 'i', 'value' => $this->getParam('projectID')]]
        );


        $this->template->set_block(
            'ProjectPopup',
            'updateBlock',
            'updates'
        );


        while ($row = $result->fetch_assoc()) {

            $updateDate = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $row['createdAt']
            );


            $this->template->setVar(
                array(
                    'createdAt' => $updateDate->format('d/m/Y H:i'),
                    'createdBy' => $row['createdBy'],
                    'comment'   => $row['comment'],
                )
            );
            $this->template->parse(
                'updates',
                'updateBlock',
                true
            );
        }


        $this->template->parse(
            'CONTENTS',
            'ProjectPopup',
            true
        );
        $this->parsePage();
    }

    /**
     * @throws Exception
     */
    function editLinkedSalesOrder()
    {
        $this->setMethodName('editLinkedSalesOrder');

        $this->setPageTitle('Linked Sales Order');

        $errorMessage = '';
        $projectID = null;
        $linkedOrderID = null;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if ($_POST['linkedOrderID']) {
                $projectID = $_POST['projectID'];
                $linkedOrderID = $_POST['linkedOrderID'];
                try {
                    $this->buProject->updateLinkedSalesOrder(
                        $projectID,
                        $linkedOrderID
                    );
                    echo '<script type="text/javascript"> window.opener.location.reload(false); window.close(); </script>';
                } catch (Exception $exception) {
                    $errorMessage = $exception->getMessage();
                }
            } else {
                $errorMessage = "Sales Order ID Required";
            }
        } else {
            $projectID = $this->getParam('projectID');
            $linkedOrderID = '';
        }

        $this->setTemplateFiles(
            array(
                'ProjectEditLinkedSalesOrder' => 'ProjectEditLinkedSalesOrder'
            )
        );

        $this->setHTMLFmt(CT_HTML_FMT_POPUP);

        $this->template->set_var(
            array(
                'projectID'     => $projectID,
                'errorMessage'  => $errorMessage,
                'linkedOrderID' => $linkedOrderID
            )
        );


        $this->template->parse(
            'CONTENTS',
            'ProjectEditLinkedSalesOrder',
            true
        );
        $this->parsePage();
    }

    /**
     * @throws Exception
     */
    private function unlinkSalesOrder()
    {
        $projectId = @$_REQUEST['projectId'];

        if (!$projectId) {
            throw new Exception('Project ID is missing');
        }

        $project = new DBEProject($this);
        $project->getRow($projectId);
        $project->setValue(DBEProject::ordHeadID, null);

        $project->updateRow();
        $urlNext =
            Controller::buildLink(
                'Project.php',
                array(
                    'action'    => CTPROJECT_ACT_EDIT,
                    'projectID' => $projectId
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * @throws Exception
     */
    private function uploadProjectPlan()
    {
        if (!isset($_FILES['files']) || !count($_FILES['files']['name'])) {
            throw new Exception('At least one file must be provided');
        }

        if (!$this->getParam('projectID')) {
            throw new Exception('Project ID is missing');
        }


        $dbeProject = new DBEProject($this);
        $dbeProject->getRow($this->getParam('projectID'));
        foreach ($_FILES['files']['name'] as $fileName) {

            $dbeProject->setUpdateModeUpdate();


            $dbeProject->setValue(
                DBEProject::planFile,
                file_get_contents($_FILES['files']['tmp_name'][0])
            );

            $dbeProject->setValue(
                DBEProject::planFileName,
                $fileName
            );
            $dbeProject->setValue(
                DBEProject::planMIMEType,
                $_FILES['files']['type'][0]
            );

            $dbeProject->updateRow();
        }

    }

    /**
     * @throws Exception
     */
    private function calculateBudget()
    {
        $projectID = @$this->getParam('projectID');

        if (!$projectID) {
            echo 'There is no project ID';
            exit;
        }
        $dbeProject = new DBEProject($this);
        $dbeProject->getRow($this->getParam('projectID'));

        if (!$dbeProject->getValue(DBEProject::ordHeadID)) {
            echo 'The project does not have a linked Sales Order';
            exit;
        }

        if ($dbeProject->getValue(DBEProject::calculatedBudget) == 'Y') {
            echo 'The project budget has already been calculated';
            exit;
        }

        $buSalesOrder = new BUSalesOrder($this);

        $dsOrdHead = new DataSet($this);
        $dsOrdLine = new DataSet($this);

        $buSalesOrder->getOrderByOrdheadID(
            $dbeProject->getValue(DBEProject::ordHeadID),
            $dsOrdHead,
            $dsOrdLine
        );

        $BUHeader = new BUHeader($this);
        $dbeHeader = new DataSet($this);
        $BUHeader->getHeader($dbeHeader);
        $minutesInADay = $dbeHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay);

        $normalMinutes = 0;
        $oohMinutes = 0;

        while ($dsOrdLine->fetchNext()) {

            if ($dsOrdLine->getValue(DBEOrdline::lineType) == 'I') {
                echo "<div>sequence: " . $dsOrdLine->getValue(DBEOrdline::sequenceNo) . " </div>";
                echo "<div>itemID: " . $dsOrdLine->getValue(DBEOrdline::itemID) . "</div>";

                switch ($dsOrdLine->getValue(DBEOrdline::itemID)) {
                    case self::DAILY_LABOUR_CHARGE:
                        $normalMinutes += ((float)$dsOrdLine->getValue(DBEOrdline::qtyOrdered)) * $minutesInADay;
                        break;
                    case self::HOURLY_LABOUR_CHARGE:
                        $normalMinutes += ((float)$dsOrdLine->getValue(DBEOrdline::qtyOrdered)) * 60;
                        break;
                    case self::DAILY_OOH_LABOUR_CHARGE:
                        $oohMinutes += ((float)$dsOrdLine->getValue(DBEOrdline::qtyOrdered)) * $minutesInADay;
                        break;
                    case self::HOURLY_OOH_LABOUR_CHARGE:
                        $oohMinutes += ((float)$dsOrdLine->getValue(DBEOrdline::qtyOrdered)) * 60;
                        break;
                }
                echo "<div>Normal Minutes: $normalMinutes</div><div>Out Of Hours Minutes: $oohMinutes</div>";

            }

        }
        echo "<div> inHours budget days " . ($normalMinutes / $minutesInADay) . "</div>";
        echo "<div> out of Hours budget days " . ($oohMinutes / $minutesInADay) . "</div>";
        $dbeProject->setValue(
            DBEProject::inHoursBudgetDays,
            $normalMinutes / $minutesInADay
        );

        $dbeProject->setValue(
            DBEProject::outOfHoursBudgetDays,
            $oohMinutes / $minutesInADay
        );

        $dbeProject->setValue(
            DBEProject::calculatedBudget,
            'Y'
        );

        $dbeProject->updateRow();

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'projectID' => $projectID,
                    'action'    => CTPROJECT_ACT_EDIT
                )
            );
        header('Location: ' . $urlNext);
        exit;

    }

    /**
     * @return array
     * @throws Exception
     */
    private function fetchBudgetData()
    {
        if (!$this->getParam('projectID')) {
            throw new Exception('Project ID is missing');
        }


        $dbeProject = new DBEProject($this);
        $dbeProject->getRow($this->getParam('projectID'));
        $buHeader = new BUHeader($this);
        $dbeHeader = new DataSet($this);
        $buHeader->getHeader($dbeHeader);


        $data = [
            "salesOrderID"     => (int)$dbeProject->getValue(DBEProject::ordHeadID),
            "calculatedBudget" => $dbeProject->getValue(DBEProject::calculatedBudget) == 'Y',
            "stats"            => [
                "inHoursAllocated" => 'N/A',
                "inHoursUsed"      => 'N/A',
                "ooHoursAllocated" => 'N/A',
                "ooHoursUsed"      => 'N/A',
            ],
            "minutesPerDay"    => $dbeHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay),
            "data"             => []
        ];
        if (!$dbeProject->getValue(DBEProject::ordHeadID)) {
            return $data;
        }

        $salesOrderID = $dbeProject->getValue(DBEProject::ordHeadID);

        $data['data'] = $this->usedBudgetData($salesOrderID);

        $buExpense = new BUExpense($this);

        $data['stats']['expenses'] = $buExpense->getTotalExpensesForSalesOrder($salesOrderID);

        if ($dbeProject->getValue(DBEProject::calculatedBudget) != 'Y') {
            return $data;
        }

        $data['stats']['inHoursAllocated'] = $dbeProject->getValue(DBEProject::inHoursBudgetDays);
        $data['stats']['ooHoursAllocated'] = $dbeProject->getValue(DBEProject::outOfHoursBudgetDays);

        return $data;
    }

    private function usedBudgetData($salesOrderID)
    {
        $startTime = '08:00';
        $endTime = '18:00';

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
WHERE pro_linked_ordno = $salesOrderID and caa_starttime <> '' and caa_starttime is not null and caa_endtime <> '' and caa_endtime is not null 
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

    /**
     * @throws Exception
     */
    private function currentProjectReport()
    {
        $dbeProject = new DBEProject($this);
        $currentProjects = $dbeProject->getCurrentProjects();
        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();

        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(8);

        $sectionStyle = ["orientation" => 'landscape', "marginLeft" => 500];
        $section = $phpWord->addSection($sectionStyle);
        $fancyTableStyleName = 'Fancy Table';
        $fancyTableStyle = array(
            'borderSize'  => 6,
            'borderColor' => '006699',
            'cellMargin'  => 80,
            'alignment'   => JcTable::CENTER,
            'cellSpacing' => 50,
        );
        /** @noinspection SpellCheckingInspection */
        $fancyTableFirstRowStyle = array(
            'borderBottomSize'  => 18,
            'borderBottomColor' => '0000FF',
            'bgColor'           => '66BBFF'
        );
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableFontStyle = array('bold' => true);
        $phpWord->addTableStyle(
            $fancyTableStyleName,
            $fancyTableStyle,
            $fancyTableFirstRowStyle
        );
        $table = $section->addTable($fancyTableStyleName);
        $table->addRow(900);
        $cellWidth = 1600;

        $pStyle = ['align' => 'center'];

        $customerNameWidth = 1450;
        $summaryWidth = 3000;

        $table->addCell(
            $customerNameWidth,
            $fancyTableCellStyle
        )->addText(
            'Customer Name',
            $fancyTableFontStyle,
            $pStyle
        );
        $table->addCell(
            $summaryWidth,
            $fancyTableCellStyle
        )->addText(
            'Summary',
            $fancyTableFontStyle,
            $pStyle
        );
        $table->addCell(
            $cellWidth,
            $fancyTableCellStyle
        )->addText(
            'Commence',
            $fancyTableFontStyle,
            $pStyle
        );
        $table->addCell(
            $cellWidth,
            $fancyTableCellStyle
        )->addText(
            'Engineer',
            $fancyTableFontStyle,
            $pStyle
        );
        $table->addCell(
            $cellWidth,
            $fancyTableCellStyle
        )->addText(
            'Service Request',
            $fancyTableFontStyle,
            $pStyle
        );

        $table->addCell(
            $cellWidth,
            $fancyTableCellStyle
        )->addText(
            'Budget',
            $fancyTableFontStyle,
            $pStyle
        );

        $table->addCell(
            $cellWidth,
            $fancyTableCellStyle
        )->addText(
            'To Date',
            $fancyTableFontStyle,
            $pStyle
        );

        $table->addCell(
            $cellWidth,
            $fancyTableCellStyle
        )->addText(
            'Latest Update',
            $fancyTableFontStyle,
            $pStyle
        );

        $table->addCell(
            $cellWidth,
            $fancyTableCellStyle
        )->addText(
            'Notes',
            $fancyTableFontStyle,
            $pStyle
        );

        foreach ($currentProjects as $project) {
            $table->addRow();

            $table->addCell(200)->addText(
                $project['customerName'],
                null,
                $pStyle
            );

            $table->addCell(200)->addText(
                $project['description'],
                null,
                $pStyle
            );

            $table->addCell(200)->addText(
                $project['commenceDate'],
                null,
                $pStyle
            );

            $table->addCell(200)->addText(
                $project['engineerName'],
                null,
                $pStyle
            );
            $problemsCell = $table->addCell(200);

            if ($project['problemno']) {
                $problems = explode(
                    ',',
                    $project['problemno']
                );

                foreach ($problems as $problemID) {

                    $link = Controller::buildLink(
                        SITE_URL . "/Activity.php",
                        [
                            "action"    => 'displayFirstActivity',
                            "problemID" => $problemID
                        ]
                    );


                    $problemsCell->addLink(
                        $link,
                        $problemID,
                        'Link',
                        'Heading2'
                    );
                }
            }

//
            $budgetText = "No Budget Calculated";
            $usedText = "No Budget Calculated";
            if ($project['calculatedBudget'] == 'Y') {

                $budgetText = "In Hours " . round(
                        $project['inHoursBudgetDays'],
                        2
                    ) . " days \nOut of Hours " . round(
                        $project['outOfHoursBudgetDays'],
                        2
                    ) . " days";

                $hoursData = $this->calculateInHoursOutHoursUsed($project['projectID']);

                $usedText = "In Hours " . round(
                        $hoursData['inHoursUsed'],
                        2
                    ) . " days \nOut of Hours " . round(
                        $hoursData['outHoursUsed'],
                        2
                    ) . " days";
            }
            $table->addCell(200)->addText(
                $budgetText,
                null,
                $pStyle
            );


            $table->addCell(200)->addText(
                $usedText,
                null,
                $pStyle
            );

            $table->addCell(200)->addText(
                $project['comment'],
                null,
                $pStyle
            );

            $table->addCell(200)->addText(
                null,
                null,
                $pStyle
            );
        }
        $date = (new DateTime())->format('d-m-Y');
        // Save file
        $phpWord->save(
            "Project Summary $date.docx",
            'Word2007',
            true
        );
    }

    private function calculateInHoursOutHoursUsed($projectID)
    {
        $dbeProject = new DBEProject($this);
        $dbeProject->getRow($projectID);
        $buHeader = new BUHeader($this);
        $dbeHeader = new DataSet($this);
        $buHeader->getHeader($dbeHeader);


        $data = [
            "inHoursUsed"   => 0,
            "outHoursUsed"  => 0,
            "minutesPerDay" => $dbeHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay)
        ];
        if (!$dbeProject->getValue(DBEProject::ordHeadID)) {
            return $data;
        }

        $salesOrderID = $dbeProject->getValue(DBEProject::ordHeadID);

        $activities = $this->usedBudgetData($salesOrderID);

        $chargeableActivities = [4, 8];

        foreach ($activities as $activity) {
            if (!in_array(
                $activity['caa_callacttypeno'],
                $chargeableActivities
            )) {
                continue;
            }

            $data['inHoursUsed'] += $activity['inHours'];
            $data['outHoursUsed'] += $activity['outHours'];
        }

        $data['inHoursUsed'] = round(
            ($data['inHoursUsed'] * 60) / $data['minutesPerDay'],
            2
        );
        $data['outHoursUsed'] = round(
            ($data['outHoursUsed'] * 60) / $data['minutesPerDay'],
            2
        );
        return $data;
    }

    /**
     * @throws Exception
     */
    private function showList()
    {
        $this->setTemplateFiles(
            array('ProjectList' => 'ProjectList')
        );

        $dbeProject = new DBEProject($this);

        $this->template->set_block(
            'ProjectList',
            'projectBlock',
            'project'
        );
        $currentProjects = $dbeProject->getCurrentProjects();

        $this->setPageTitle('Projects - ' . count($currentProjects));
        foreach ($currentProjects as $project) {
            $hasProjectPlan = !!$project['planFileName'];

            $projectPlanDownloadURL =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    [
                        'action'    => self::DOWNLOAD_PROJECT_PLAN,
                        'projectID' => $project['projectID']
                    ]
                );

            $downloadProjectPlanClass = $hasProjectPlan ? '' : 'class="redText"';
            $downloadProjectPlanURL = $hasProjectPlan ? "href='$projectPlanDownloadURL' target='_blank' " : 'href="#"';
            $projectPlanLink = "<a id='projectPlanLink' $downloadProjectPlanClass $downloadProjectPlanURL>Project Plan</a>";

            $historyPopupURL = Controller::buildLink(
                'Project.php',
                array(
                    'action'    => 'historyPopup',
                    'htmlFmt'   => CT_HTML_FMT_POPUP,
                    'projectID' => $project['projectID']
                )
            );

            $projectEditURL = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                [
                    "action"    => 'edit',
                    "projectID" => $project['projectID']
                ]
            );

            $projectLink = "<a href='$projectEditURL'>$project[description]</a>";

            $lastUpdated = 'No updates';
            $lastUpdatedClass = null;
            if ($project['createdBy']) {
                $createdAtDate = DateTime::createFromFormat(DATE_MYSQL_DATETIME, $project['createdAt']);
                $todayMinus14Days = (new DateTime())->sub(new DateInterval('P14D'));
                if (!$project['commenceDate'] && $createdAtDate <= $todayMinus14Days) {
                    $lastUpdatedClass = "class='redText'";
                }

                $lastUpdated = "<span style='font-weight: bold'>" . $createdAtDate->format(
                        'd-m-Y'
                    ) . " by $project[createdBy]:</span> $project[comment]";
            }

            $inHoursBudget = "Uncalculated";
            $inHoursUsed = "Uncalculated";
            $outHoursBudget = "Uncalculated";
            $outHoursUsed = "Uncalculated";


            if ($project['calculatedBudget'] == 'Y') {
                $hoursUsed = $this->calculateInHoursOutHoursUsed($project['projectID']);
                $inHoursBudget = $project['inHoursBudgetDays'];
                $inHoursUsed = $hoursUsed['inHoursUsed'];
                $outHoursBudget = $project['outOfHoursBudgetDays'];
                $outHoursUsed = $hoursUsed['outHoursUsed'];
            }
            $commencementDate = null;
            if ($project['commenceDate']) {
                $commencementDate = DateTime::createFromFormat(DATE_MYSQL_DATE, $project['commenceDate'])->format(
                    'd-m-Y'
                );
            }

            $this->template->setVar(
                [
                    "description"       => $projectLink,
                    "commenceDate"      => $commencementDate,
                    'customerName'      => $project['customerName'],
                    "projectPlanLink"   => $projectPlanLink,
                    "latestUpdate"      => $lastUpdated,
                    'latestUpdateClass' => $lastUpdatedClass,
                    "historyPopupURL"   => $historyPopupURL,
                    "inHoursBudget"     => $inHoursBudget,
                    "inHoursUsed"       => $inHoursUsed,
                    "inHoursRed"        => $inHoursUsed > $inHoursBudget ? 'class="redText"' : '',
                    "outHoursRed"       => $outHoursUsed > $outHoursBudget ? 'class="redText"' : '',
                    "outHoursBudget"    => $outHoursBudget,
                    "outHoursUsed"      => $outHoursUsed,
                    'assignedEngineer'  => $project['engineerName']
                ]
            );
            $this->template->parse(
                'project',
                'projectBlock',
                true
            );
        }

        $this->template->parse(
            'CONTENTS',
            'ProjectList',
            true
        );

        $this->parsePage();
    }


}
