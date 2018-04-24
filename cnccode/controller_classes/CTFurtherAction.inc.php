<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUFurtherAction.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTFURTHERACTION_ACT_DISPLAY_LIST', 'furtherActionList');
define('CTFURTHERACTION_ACT_CREATE', 'createFurtherAction');
define('CTFURTHERACTION_ACT_EDIT', 'editFurtherAction');
define('CTFURTHERACTION_ACT_DELETE', 'deleteFurtherAction');
define('CTFURTHERACTION_ACT_UPDATE', 'updateFurtherAction');

class CTFURTHERACTION extends CTCNC
{
    var $dsFurtherAction = '';
    var $buFurtherAction = '';

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
        $this->buFurtherAction = new BUFurtherAction($this);
        $this->dsFurtherAction = new DSForm($this);
        $this->dsFurtherAction->copyColumnsFrom($this->buFurtherAction->dbeFurtherAction);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case CTFURTHERACTION_ACT_EDIT:
            case CTFURTHERACTION_ACT_CREATE:
                $this->edit();
                break;
            case CTFURTHERACTION_ACT_DELETE:
                $this->delete();
                break;
            case CTFURTHERACTION_ACT_UPDATE:
                $this->update();
                break;
            case CTFURTHERACTION_ACT_DISPLAY_LIST:
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
        $this->setPageTitle('Further Actions');
        $this->setTemplateFiles(
            array('FurtherActionList' => 'FurtherActionList.inc')
        );

        $this->buFurtherAction->getAllTypes($dsFurtherAction);

        $urlCreate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTFURTHERACTION_ACT_CREATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsFurtherAction->rowCount() > 0) {
            $this->template->set_block('FurtherActionList', 'furtherActionBlock', 'furtherActions');
            while ($dsFurtherAction->fetchNext()) {
                $furtherActionID = $dsFurtherAction->getValue('furtherActionID');
                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTFURTHERACTION_ACT_EDIT,
                            'furtherActionID' => $furtherActionID
                        )
                    );
                $txtEdit = '[edit]';

                if ($this->buFurtherAction->canDelete($furtherActionID)) {
                    $urlDelete =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action' => CTFURTHERACTION_ACT_DELETE,
                                'furtherActionID' => $furtherActionID
                            )
                        );
                    $txtDelete = '[delete]';
                } else {
                    $urlDelete = '';
                    $txtDelete = '';
                }

                $this->template->set_var(
                    array(
                        'furtherActionID' => $furtherActionID,
                        'description' => Controller::htmlDisplayText($dsFurtherAction->getValue('description')),
                        'urlEdit' => $urlEdit,
                        'urlDelete' => $urlDelete,
                        'txtEdit' => $txtEdit,
                        'txtDelete' => $txtDelete
                    )
                );
                $this->template->parse('furtherActions', 'furtherActionBlock', true);
            }//while $dsFurtherAction->fetchNext()
        }
        $this->template->parse('CONTENTS', 'FurtherActionList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Further Action
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsFurtherAction = &$this->dsFurtherAction; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTFURTHERACTION_ACT_EDIT) {
                $this->buFurtherAction->getFurtherActionByID($_REQUEST['furtherActionID'], $dsFurtherAction);
                $furtherActionID = $_REQUEST['furtherActionID'];
            } else {                                                                    // creating new
                $dsFurtherAction->initialise();
                $dsFurtherAction->setValue('furtherActionID', '0');
                $furtherActionID = '0';
            }
        } else {                                                                        // form validation error
            $dsFurtherAction->initialise();
            $dsFurtherAction->fetchNext();
            $furtherActionID = $dsFurtherAction->getValue('furtherActionID');
        }
        if ($_REQUEST['action'] == CTFURTHERACTION_ACT_EDIT && $this->buFurtherAction->canDelete($_REQUEST['furtherActionID'])) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTFURTHERACTION_ACT_DELETE,
                        'furtherActionID' => $furtherActionID
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
                    'action' => CTFURTHERACTION_ACT_UPDATE,
                    'furtherActionID' => $furtherActionID
                )
            );
        $urlDisplayList =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTFURTHERACTION_ACT_DISPLAY_LIST
                )
            );
        $this->setPageTitle('Edit Further Action');
        $this->setTemplateFiles(
            array('FurtherActionEdit' => 'FurtherActionEdit.inc')
        );
        $this->template->set_var(
            array(
                'furtherActionID' => $furtherActionID,
                'description' => Controller::htmlInputText($dsFurtherAction->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsFurtherAction->getMessage('description')),
                'emailAddress' => Controller::htmlInputText($dsFurtherAction->getValue('emailAddress')),
                'emailAddressMessage' => Controller::htmlDisplayText($dsFurtherAction->getMessage('emailAddress')),
                'requireDateChecked' => Controller::htmlChecked($dsFurtherAction->getValue('requireDate')),
                'emailBody' => Controller::htmlInputText($dsFurtherAction->getValue('emailBody')),
                'emailBodyMessage' => Controller::htmlDisplayText($dsFurtherAction->getMessage('emailBody')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList
            )
        );
        $this->template->parse('CONTENTS', 'FurtherActionEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

    /**
     * Update call Further Action details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsFurtherAction = &$this->dsFurtherAction;
        $this->formError = (!$this->dsFurtherAction->populateFromArray($_REQUEST['furtherAction']));
        if ($this->formError) {
            if ($this->dsFurtherAction->getValue('furtherActionID') == '') {                    // attempt to insert
                $_REQUEST['action'] = CTFURTHERACTION_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTFURTHERACTION_ACT_CREATE;
            }
            $this->edit();
            exit;
        }

        $this->buFurtherAction->updateFurtherAction($this->dsFurtherAction);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'furtherActionID' => $this->dsFurtherAction->getValue('furtherActionID'),
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
        if (!$this->buFurtherAction->deleteFurtherAction($_REQUEST['furtherActionID'])) {
            $this->displayFatalError('Cannot delete this Further Action');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTFURTHERACTION_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}// end of class
?>