<?php
/**
 * Security Application controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUSecurityApp.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTSECURITYAPP_ACT_DISPLAY_LIST', 'securityAppList');
define('CTSECURITYAPP_ACT_CREATE', 'createSecurityApp');
define('CTSECURITYAPP_ACT_EDIT', 'editSecurityApp');
define('CTSECURITYAPP_ACT_DELETE', 'deleteSecurityApp');
define('CTSECURITYAPP_ACT_UPDATE', 'updateSecurityApp');

class CTSecurityApp extends CTCNC
{
    public $buSecurityApp;
    public $dsSecurityApp;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buSecurityApp = new BUSecurityApp($this);
        $this->dsSecurityApp = new DSForm($this);
        $this->dsSecurityApp->copyColumnsFrom($this->buSecurityApp->dbeSecurityApp);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case CTSECURITYAPP_ACT_EDIT:
            case CTSECURITYAPP_ACT_CREATE:
                $this->edit();
                break;
            case CTSECURITYAPP_ACT_DELETE:
                $this->delete();
                break;
            case CTSECURITYAPP_ACT_UPDATE:
                $this->update();
                break;
            case CTSECURITYAPP_ACT_DISPLAY_LIST:
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
        $this->setPageTitle('Security Applications');
        $this->setTemplateFiles(
            array('SecurityAppList' => 'SecurityAppList.inc')
        );

        $this->buSecurityApp->getAllRows($dsSecurityApp);

        $urlCreate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSECURITYAPP_ACT_CREATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsSecurityApp->rowCount() > 0) {
            $this->template->set_block('SecurityAppList', 'securityAppBlock', 'apps');
            while ($dsSecurityApp->fetchNext()) {
                $securityAppID = $dsSecurityApp->getValue('securityAppID');
                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTSECURITYAPP_ACT_EDIT,
                            'securityAppID' => $securityAppID
                        )
                    );
                $txtEdit = '[edit]';

                $urlDelete =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTSECURITYAPP_ACT_DELETE,
                            'securityAppID' => $securityAppID
                        )
                    );
                $txtDelete = '[delete]';

                $this->template->set_var(
                    array(
                        'securityAppID' => $securityAppID,
                        'description' => Controller::htmlDisplayText($dsSecurityApp->getValue('description')),
                        'urlEdit' => $urlEdit,
                        'urlDelete' => $urlDelete,
                        'txtEdit' => $txtEdit,
                        'txtDelete' => $txtDelete
                    )
                );
                $this->template->parse('apps', 'securityAppBlock', true);
            }//while $dsSecurityApp->fetchNext()
        }
        $this->template->parse('CONTENTS', 'SecurityAppList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Activity
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsSecurityApp = &$this->dsSecurityApp; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTSECURITYAPP_ACT_EDIT) {
                $this->buSecurityApp->getSecurityAppByID($_REQUEST['securityAppID'], $dsSecurityApp);
                $securityAppID = $_REQUEST['securityAppID'];
            } else {                                                                    // creating new
                $dsSecurityApp->initialise();
                $dsSecurityApp->setValue('securityAppID', '0');
                $securityAppID = '0';
            }
        } else {                                                                        // form validation error
            $dsSecurityApp->initialise();
            $dsSecurityApp->fetchNext();
            $securityAppID = $dsSecurityApp->getValue('securityAppID');
        }
        if ($_REQUEST['action'] == CTSECURITYAPP_ACT_EDIT) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSECURITYAPP_ACT_DELETE,
                        'securityAppID' => $securityAppID
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
                    'action' => CTSECURITYAPP_ACT_UPDATE,
                    'securityAppID' => $securityAppID
                )
            );
        $urlDisplayList =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSECURITYAPP_ACT_DISPLAY_LIST
                )
            );
        $this->setPageTitle('Edit Security Application');
        $this->setTemplateFiles(
            array('SecurityAppEdit' => 'SecurityAppEdit.inc')
        );
        $this->template->set_var(
            array(
                'securityAppID' => $securityAppID,
                'description' => Controller::htmlInputText($dsSecurityApp->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsSecurityApp->getMessage('description')),
                'backupFlagChecked' => Controller::htmlChecked($dsSecurityApp->getValue('backupFlag')),
                'emailAVFlagChecked' => Controller::htmlChecked($dsSecurityApp->getValue('emailAVFlag')),
                'serverAVFlagChecked' => Controller::htmlChecked($dsSecurityApp->getValue('serverAVFlag')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList
            )
        );
        $this->template->parse('CONTENTS', 'SecurityAppEdit', true);
        $this->parsePage();
    }// end function editActivity()

    /**
     * Update call activity type details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsSecurityApp = &$this->dsSecurityApp;
        $this->formError = (!$this->dsSecurityApp->populateFromArray($_REQUEST['securityApp']));
        if ($this->formError) {
            if ($this->dsSecurityApp->getValue('securityAppID') == '') {                    // attempt to insert
                $_REQUEST['action'] = CTSECURITYAPP_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTSECURITYAPP_ACT_CREATE;
            }
            $this->edit();
            exit;
        }

        $this->buSecurityApp->updateSecurityApp($this->dsSecurityApp);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                array(
                    'securityAppID' => $this->dsSecurityApp->getValue('securityAppID'),
                    'action' => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Activity
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buSecurityApp->deleteSecurityApp($_REQUEST['securityAppID'])) {
            $this->displayFatalError('Cannot delete this activity type');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSECURITYAPP_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}// end of class
?>