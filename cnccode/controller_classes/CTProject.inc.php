<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEOrdhead.inc.php');
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

class CTProject extends CTCNC
{
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
            "sales",
            "technical",
        ];

        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buProject = new BUProject($this);
        $this->dsProject = new DSForm($this);
        $this->dsProject->copyColumnsFrom($this->buProject->dbeProject);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
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
            case 'editLinkedSalesOrder':
                $this->editLinkedSalesOrder();
                break;
        }
    }

    /**
     * Edit/Add Further Action
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsProject = &$this->dsProject; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTPROJECT_ACT_EDIT) {
                $this->buProject->getProjectByID(
                    $_REQUEST['projectID'],
                    $dsProject
                );
                $projectID = $_REQUEST['projectID'];
            } else {                                                                    // creating new
                $dsProject->initialise();
                $dsProject->setValue(
                    DBEProject::projectID,
                    '0'
                );
                $dsProject->setValue(
                    DBEProject::customerID,
                    $_REQUEST['customerID']
                );
                $projectID = '0';
            }
        } else {                                                                        // form validation error
            $dsProject->initialise();
            $dsProject->fetchNext();
            $projectID = $dsProject->getValue(DBEProject::projectID);
        }
        if ($_REQUEST['action'] == CTPROJECT_ACT_EDIT && $this->buProject->canDelete($_REQUEST['projectID'])) {
            $urlDelete =
                $this->buildLink(
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
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTPROJECT_ACT_UPDATE,
                    'projectID' => $projectID
                )
            );
        $urlDisplayCustomer =
            $this->buildLink(
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

        if ($row['createdAt']) {

            $updateDate = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $row['createdAt']
            );

            $formattedDate = $updateDate->format('d/m/Y H:i');
        }


        $historyPopupURL = $this->buildLink(
            'Project.php',
            array(
                'action'    => 'historyPopup',
                'projectID' => $dsProject->getValue('projectID'),
                'htmlFmt'   => CT_HTML_FMT_POPUP
            )
        );

        $urlLinkedSalesOrder =
            $this->buildLink(
                'Project.php',
                array(
                    'action'    => 'editLinkedSalesOrder',
                    'htmlFmt'   => CT_HTML_FMT_POPUP,
                    'projectID' => $dsProject->getValue('projectID')
                )
            );

        $this->template->set_var(
            array(
                'customerID'          => $dsProject->getValue(DBEProject::customerID),
                'projectID'           => $projectID,
                'description'         => Controller::htmlInputText($dsProject->getValue(DBEProject::description)),
                'descriptionMessage'  => Controller::htmlDisplayText($dsProject->getMessage(DBEProject::description)),
                'notes'               => Controller::htmlInputText($dsProject->getValue(DBEProject::notes)),
                'notesMessage'        => Controller::htmlDisplayText($dsProject->getMessage(DBEProject::notes)),
                'startDate'           => Controller::dateYMDtoDMY($dsProject->getValue(DBEProject::openedDate)),
                'startDateMessage'    => Controller::htmlDisplayText($dsProject->getMessage(DBEProject::openedDate)),
                'expiryDate'          => Controller::dateYMDtoDMY($dsProject->getValue(DBEProject::completedDate)),
                'expiryDateMessage'   => Controller::htmlDisplayText($dsProject->getMessage(DBEProject::completedDate)),
                'urlUpdate'           => $urlUpdate,
                'urlDelete'           => $urlDelete,
                'txtDelete'           => $txtDelete,
                'urlDisplayCustomer'  => $urlDisplayCustomer,
                'lastUpdateDate'      => $formattedDate,
                'lastUpdateEngineer'  => $row['createdBy'],
                'lastUpdateComment'   => $row['comment'],
                'historyPopupURL'     => $historyPopupURL,
                'salesOrderLink'      => $this->getSalesOrderLink($dsProject->getValue(DBEProject::ordHeadID)),
                'urlLinkedSalesOrder' => $urlLinkedSalesOrder
            )
        );
        $this->template->parse(
            'CONTENTS',
            'ProjectEdit',
            true
        );
        $this->parsePage();
    }// end function editFurther Action()

    function getSalesOrderLink($linkedOrdheadID)
    {
        if ($linkedOrdheadID) {

            $linkURL =
                $this->buildLink(
                    'SalesOrder.php',
                    array(
                        'action'    => 'displaySalesOrder',
                        'ordheadID' => $linkedOrdheadID
                    )
                );
            return '<a href="' . $linkURL . '" target="_blank" title="Sales Order">Sales Order</a>';
        }
        return ' <a href="#" style="color: red" onclick="linkedSalesOrderPopup()">Sales Order</a>';
    }

    /**
     * Update call Further Action details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsProject = &$this->dsProject;
        $this->formError = (!$this->dsProject->populateFromArray($_REQUEST['project']));
        if ($this->formError) {
            if ($this->dsProject->getValue('projectID') == '') {                    // attempt to insert
                $_REQUEST['action'] = CTPROJECT_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTPROJECT_ACT_ACT;
            }
            $this->edit();
            exit;
        }

        $this->buProject->updateProject($this->dsProject);

        if (!empty($_REQUEST['newComment'])) {

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
                    'value' => (int)$this->dsProject->getValue('projectID')
                ],
                [
                    'type'  => 's',
                    'value' => $_REQUEST['newComment']
                ]
            ];

            $db->preparedQuery(
                "insert into projectUpdates(createdBy,projectID,comment) values (?, ?, ?)",
                $parameters
            );
        }

        $urlNext =
            $this->buildLink(
                'Project.php',
                array(
                    'projectID' => $this->dsProject->getValue(DBEProject::projectID),
                    'action'    => 'edit'
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Further Action
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');

        $this->buProject->getProjectByID(
            $_REQUEST['projectID'],
            $dsProject
        );

        if (!$this->buProject->deleteProject($_REQUEST['projectID'])) {
            $this->displayFatalError('Cannot delete this project');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    'Customer.php',
                    array(
                        'customerID' => $dsProject->getValue('customerID'),
                        'action'     => CTCNC_ACT_DISP_EDIT
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    function popup()
    {
        $this->buProject->getProjectByID(
            $_REQUEST['projectID'],
            $dsProject
        );
        $this->setPageTitle('Project: ' . Controller::htmlDisplayText($dsProject->getValue('description')));
        $this->setTemplateFiles(
            array('ProjectPopup' => 'ProjectPopup.inc')
        );
        $this->template->set_var(
            array(
                'notes'      => Controller::htmlDisplayText(
                    $dsProject->getValue('notes'),
                    1
                ),
                'startDate'  => Controller::dateYMDtoDMY($dsProject->getValue('startDate')),
                'expiryDate' => Controller::dateYMDtoDMY($dsProject->getValue('expiryDate')),
            )
        );
        $this->template->parse(
            'CONTENTS',
            'ProjectPopup',
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

    private function historyPopup()
    {
        $this->setPageTitle('Project Updates History');
        $this->setTemplateFiles(
            array('ProjectPopup' => 'ProjectUpdatesHistoryPopup')
        );

        global $db;

        $result = $db->preparedQuery(
            "select * from projectUpdates where projectID = ? order by createdAt desc",
            [['type' => 'i', 'value' => $_REQUEST['projectID']]]
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

    function editLinkedSalesOrder()
    {
        $this->setMethodName('editLinkedSalesOrder');

        $this->setPageTitle('Linked Sales Order');

        $errorMessage = '';

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
            $projectID = $_REQUEST['projectID'];
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
}// end of class
?>