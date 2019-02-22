<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BURootCause.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTROOTCAUSE_ACT_DISPLAY_LIST', 'rootCauseList');
define('CTROOTCAUSE_ACT_CREATE', 'createRootCause');
define('CTROOTCAUSE_ACT_EDIT', 'editRootCause');
define('CTROOTCAUSE_ACT_DELETE', 'deleteRootCause');
define('CTROOTCAUSE_ACT_UPDATE', 'updateRootCause');

class CTROOTCAUSE extends CTCNC
{
    var $dsRootCause = '';
    var $buRootCause = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "maintenance",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buRootCause = new BURootCause($this);
        $this->dsRootCause = new DSForm($this);
        $this->dsRootCause->copyColumnsFrom($this->buRootCause->dbeRootCause);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case CTROOTCAUSE_ACT_EDIT:
            case CTROOTCAUSE_ACT_CREATE:
                $this->edit();
                break;
            case CTROOTCAUSE_ACT_DELETE:
                $this->delete();
                break;
            case CTROOTCAUSE_ACT_UPDATE:
                $this->update();
                break;
            case CTROOTCAUSE_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of types
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Root Causes');
        $this->setTemplateFiles(
            array('RootCauseList' => 'RootCauseList.inc')
        );

        $this->buRootCause->getAll($dsRootCause);

        $urlCreate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTROOTCAUSE_ACT_CREATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsRootCause->rowCount() > 0) {

            $this->template->set_block(
                'RootCauseList',
                'RootCauseBlock',
                'rootCauses'
            );

            while ($dsRootCause->fetchNext()) {

                $rootCauseID = $dsRootCause->getValue('rootCauseID');

                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTROOTCAUSE_ACT_EDIT,
                            'rootCauseID' => $rootCauseID
                        )
                    );
                $txtEdit = '[edit]';

                if ($this->buRootCause->canDelete($rootCauseID)) {
                    $urlDelete =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action' => CTROOTCAUSE_ACT_DELETE,
                                'rootCauseID' => $rootCauseID
                            )
                        );
                    $txtDelete = '[delete]';
                } else {
                    $urlDelete = '';
                    $txtDelete = '';
                }

                $this->template->set_var(
                    array(
                        'rootCauseID' => $rootCauseID,
                        'description' => Controller::htmlDisplayText($dsRootCause->getValue('description')),
                        'urlEdit' => $urlEdit,
                        'urlDelete' => $urlDelete,
                        'txtEdit' => $txtEdit,
                        'txtDelete' => $txtDelete
                    )
                );

                $this->template->parse('rootCauses', 'RootCauseBlock', true);

            }//while $dsRootCause->fetchNext()
        }
        $this->template->parse('CONTENTS', 'RootCauseList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Further Action
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsRootCause = &$this->dsRootCause; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTROOTCAUSE_ACT_EDIT) {
                $this->buRootCause->getRootCauseByID($_REQUEST['rootCauseID'], $dsRootCause);
                $rootCauseID = $_REQUEST['rootCauseID'];
            } else {                                                                    // creating new
                $dsRootCause->initialise();
                $dsRootCause->setValue('rootCauseID', '0');
                $rootCauseID = '0';
            }
        } else {                                                                        // form validation error
            $dsRootCause->initialise();
            $dsRootCause->fetchNext();
            $rootCauseID = $dsRootCause->getValue('rootCauseID');
        }
        if ($_REQUEST['action'] == CTROOTCAUSE_ACT_EDIT && $this->buRootCause->canDelete($_REQUEST['rootCauseID'])) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTROOTCAUSE_ACT_DELETE,
                        'rootCauseID' => $rootCauseID
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
                    'action' => CTROOTCAUSE_ACT_UPDATE,
                    'rootCauseID' => $rootCauseID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTROOTCAUSE_ACT_DISPLAY_LIST
                )
            );
        $this->setPageTitle('Edit Further Action');
        $this->setTemplateFiles(
            array('RootCauseEdit' => 'RootCauseEdit.inc')
        );
        $this->template->set_var(
            array(
                'rootCauseID' => $rootCauseID,
                'description' => Controller::htmlInputText($dsRootCause->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsRootCause->getMessage('description')),
                'longDescription' => Controller::htmlInputText($dsRootCause->getValue('longDescription')),
                'longDescriptionMessage' => Controller::htmlDisplayText($dsRootCause->getMessage('longDescription')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList
            )
        );
        $this->template->parse('CONTENTS', 'RootCauseEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

    /**
     * Update call Further Action details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsRootCause = &$this->dsRootCause;
        $this->formError = (!$this->dsRootCause->populateFromArray($_REQUEST['rootCause']));
        if ($this->formError) {
            if ($this->dsRootCause->getValue('rootCauseID') == '') {                    // attempt to insert
                $_REQUEST['action'] = CTROOTCAUSE_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTROOTCAUSE_ACT_CREATE;
            }
            $this->edit();
            exit;
        }

        $this->buRootCause->updateRootCause($this->dsRootCause);

        $urlNext =
            Controller::buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'rootCauseID' => $this->dsRootCause->getValue('rootCauseID'),
                                 'action' => CTCNC_ACT_VIEW
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
        if (!$this->buRootCause->deleteRootCause($_REQUEST['rootCauseID'])) {
            $this->displayFatalError('Cannot delete this row');
            exit;
        } else {
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTROOTCAUSE_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}// end of class
?>