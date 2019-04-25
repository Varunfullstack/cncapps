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

class CTRootCause extends CTCNC
{
    /** @var DSForm */
    public $dsRootCause;
    /** @var BURootCause */
    public $buRootCause;

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
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($this->getAction()) {
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
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Root Causes');
        $this->setTemplateFiles(
            array('RootCauseList' => 'RootCauseList.inc')
        );
        $dsRootCause = new DataSet($this);
        $this->buRootCause->getAll($dsRootCause);

        $urlCreate = Controller::buildLink(
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

                $rootCauseID = $dsRootCause->getValue(DBERootCause::rootCauseID);

                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'      => CTROOTCAUSE_ACT_EDIT,
                            'rootCauseID' => $rootCauseID
                        )
                    );
                $txtEdit = '[edit]';

                $urlDelete = null;
                $txtDelete = null;
                if ($this->buRootCause->canDelete($rootCauseID)) {
                    $urlDelete =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'      => CTROOTCAUSE_ACT_DELETE,
                                'rootCauseID' => $rootCauseID
                            )
                        );
                    $txtDelete = '[delete]';
                }
                $this->template->set_var(
                    array(
                        'rootCauseID' => $rootCauseID,
                        'description' => Controller::htmlDisplayText($dsRootCause->getValue(DBERootCause::description)),
                        'urlEdit'     => $urlEdit,
                        'urlDelete'   => $urlDelete,
                        'txtEdit'     => $txtEdit,
                        'txtDelete'   => $txtDelete
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
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsRootCause = &$this->dsRootCause; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == CTROOTCAUSE_ACT_EDIT) {
                $this->buRootCause->getRootCauseByID($this->getParam('rootCauseID'), $dsRootCause);
                $rootCauseID = $this->getParam('rootCauseID');
            } else {                                                                    // creating new
                $dsRootCause->initialise();
                $dsRootCause->setValue(DBERootCause::rootCauseID, '0');
                $rootCauseID = '0';
            }
        } else {                                                                        // form validation error
            $dsRootCause->initialise();
            $dsRootCause->fetchNext();
            $rootCauseID = $dsRootCause->getValue(DBERootCause::rootCauseID);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() == CTROOTCAUSE_ACT_EDIT && $this->buRootCause->canDelete($this->getParam('rootCauseID'))) {
            $urlDelete = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'      => CTROOTCAUSE_ACT_DELETE,
                    'rootCauseID' => $rootCauseID
                )
            );
            $txtDelete = 'Delete';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'      => CTROOTCAUSE_ACT_UPDATE,
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
                'rootCauseID'            => $rootCauseID,
                'description'            => Controller::htmlInputText(
                    $dsRootCause->getValue(DBERootCause::description)
                ),
                'descriptionMessage'     => Controller::htmlDisplayText(
                    $dsRootCause->getMessage(DBERootCause::description)
                ),
                'longDescription'        => Controller::htmlInputText(
                    $dsRootCause->getValue(DBERootCause::longDescription)
                ),
                'longDescriptionMessage' => Controller::htmlDisplayText(
                    $dsRootCause->getMessage(DBERootCause::longDescription)
                ),
                'urlUpdate'              => $urlUpdate,
                'urlDelete'              => $urlDelete,
                'txtDelete'              => $txtDelete,
                'urlDisplayList'         => $urlDisplayList
            )
        );
        $this->template->parse('CONTENTS', 'RootCauseEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

    /**
     * Update call Further Action details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsRootCause->populateFromArray($this->getParam('rootCause')));
        if ($this->formError) {
            if ($this->dsRootCause->getValue(DBERootCause::rootCauseID)) {
                $this->setAction(CTROOTCAUSE_ACT_EDIT);
            } else {
                $this->setAction(CTROOTCAUSE_ACT_CREATE);
            }
            $this->edit();
            exit;
        }

        $this->buRootCause->updateRootCause($this->dsRootCause);

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'rootCauseID' => $this->dsRootCause->getValue(DBERootCause::rootCauseID),
                    'action'      => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }

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
        if (!$this->buRootCause->deleteRootCause($this->getParam('rootCauseID'))) {
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
}
