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
// Actions
define('CTPROJECT_ACT_DISPLAY_LIST', 'projectList');
define('CTPROJECT_ACT_ACT', 'add');
define('CTPROJECT_ACT_EDIT', 'edit');
define('CTPROJECT_ACT_DELETE', 'delete');
define('CTPROJECT_ACT_UPDATE', 'update');

class CTProject extends CTCNC
{
    var $dsProject = '';
    var $buProject = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
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
                $this->buProject->getProjectByID($_REQUEST['projectID'], $dsProject);
                $projectID = $_REQUEST['projectID'];
            } else {                                                                    // creating new
                $dsProject->initialise();
                $dsProject->setValue('projectID', '0');
                $dsProject->setValue('customerID', $_REQUEST['customerID']);
                $projectID = '0';
            }
        } else {                                                                        // form validation error
            $dsProject->initialise();
            $dsProject->fetchNext();
            $projectID = $dsProject->getValue('projectID');
        }
        if ($_REQUEST['action'] == CTPROJECT_ACT_EDIT && $this->buProject->canDelete($_REQUEST['projectID'])) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTPROJECT_ACT_DELETE,
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
                    'action' => CTPROJECT_ACT_UPDATE,
                    'projectID' => $projectID
                )
            );
        $urlDisplayCustomer =
            $this->buildLink(
                'Customer.php',
                array(
                    'customerID' => $this->dsProject->getValue('customerID'),
                    'action' => CTCNC_ACT_DISP_EDIT
                )
            );
        $this->setPageTitle('Edit Project');
        $this->setTemplateFiles(
            array('ProjectEdit' => 'ProjectEdit.inc')
        );
        $this->template->set_var(
            array(
                'customerID' => $dsProject->getValue('customerID'),
                'projectID' => $projectID,
                'description' => Controller::htmlInputText($dsProject->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsProject->getMessage('description')),
                'notes' => Controller::htmlInputText($dsProject->getValue('notes')),
                'notesMessage' => Controller::htmlDisplayText($dsProject->getMessage('notes')),
                'startDate' => Controller::dateYMDtoDMY($dsProject->getValue('startDate')),
                'startDateMessage' => Controller::htmlDisplayText($dsProject->getMessage('startDate')),
                'expiryDate' => Controller::dateYMDtoDMY($dsProject->getValue('expiryDate')),
                'expiryDateMessage' => Controller::htmlDisplayText($dsProject->getMessage('expiryDate')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayCustomer' => $urlDisplayCustomer
            )
        );
        $this->template->parse('CONTENTS', 'ProjectEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

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

        $urlNext =
            $this->buildLink(
                'Customer.php',
                array(
                    'customerID' => $this->dsProject->getValue('customerID'),
                    'action' => CTCNC_ACT_DISP_EDIT
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

        $this->buProject->getProjectByID($_REQUEST['projectID'], $dsProject);

        if (!$this->buProject->deleteProject($_REQUEST['projectID'])) {
            $this->displayFatalError('Cannot delete this project');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    'Customer.php',
                    array(
                        'customerID' => $dsProject->getValue('customerID'),
                        'action' => CTCNC_ACT_DISP_EDIT
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    function popup()
    {
        $this->buProject->getProjectByID($_REQUEST['projectID'], $dsProject);
        $this->setPageTitle('Project: ' . Controller::htmlDisplayText($dsProject->getValue('description')));
        $this->setTemplateFiles(
            array('ProjectPopup' => 'ProjectPopup.inc')
        );
        $this->template->set_var(
            array(
                'notes' => Controller::htmlDisplayText($dsProject->getValue('notes'), 1),
                'startDate' => Controller::dateYMDtoDMY($dsProject->getValue('startDate')),
                'expiryDate' => Controller::dateYMDtoDMY($dsProject->getValue('expiryDate')),
            )
        );
        $this->template->parse('CONTENTS', 'ProjectPopup', true);
        $this->parsePage();

    }
}// end of class
?>