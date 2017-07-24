<?php
/**
 * Team controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUTeam.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTTEAM_ACT_DISPLAY_LIST', 'teamList');
define('CTTEAM_ACT_CREATE', 'createTeam');
define('CTTEAM_ACT_EDIT', 'editTeam');
define('CTTEAM_ACT_DELETE', 'deleteTeam');
define('CTTEAM_ACT_UPDATE', 'updateTeam');

class CTTEAM extends CTCNC
{
    var $dsTeam = '';
    var $buTeam = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buTeam = new BUTeam($this);
        $this->dsTeam = new DSForm($this);
        $this->dsTeam->copyColumnsFrom($this->buTeam->dbeTeam);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case CTTEAM_ACT_EDIT:

            case CTTEAM_ACT_CREATE:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->edit();
                break;

            case CTTEAM_ACT_DELETE:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->delete();
                break;

            case 'performanceReport':
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->performanceReport();
                break;

            case CTTEAM_ACT_UPDATE:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->update();
                break;
            case CTTEAM_ACT_DISPLAY_LIST:
            default:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->displayList();
                break;
        }
    }

    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('User Teams');
        $this->setTemplateFiles(
            array('TeamList' => 'TeamList.inc')
        );

        $teams = $this->buTeam->getAll();

        $urlCreate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTTEAM_ACT_CREATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if (count($teams) > 0) {

            $this->template->set_block(
                'TeamList',
                'TeamBlock',
                'teams'
            );

            foreach ($teams as $team) {

                $teamID = $team['teamID'];

                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTTEAM_ACT_EDIT,
                            'teamID' => $teamID
                        )
                    );
                $txtEdit = '[edit]';

                if ($this->buTeam->canDelete($teamID)) {
                    $urlDelete =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action' => CTTEAM_ACT_DELETE,
                                'teamID' => $teamID
                            )
                        );
                    $txtDelete = '[delete]';
                } else {
                    $urlDelete = '';
                    $txtDelete = '';
                }

                $this->template->set_var(
                    array(
                        'teamID' => $teamID,
                        'name' => Controller::htmlDisplayText($team['name']),
                        'teamRoleName' => Controller::htmlDisplayText($team['teamRoleName']),
                        'level' => Controller::htmlDisplayText($team['level']),
                        'activeFlag' => Controller::htmlDisplayText($team['activeFlag']),
                        'urlEdit' => $urlEdit,
                        'urlDelete' => $urlDelete,
                        'txtEdit' => $txtEdit,
                        'txtDelete' => $txtDelete
                    )
                );

                $this->template->parse('teams', 'TeamBlock', true);

            }//while $dsTeam->fetchNext()
        }
        $this->template->parse('CONTENTS', 'TeamList', true);
        $this->parsePage();
    }

    function edit()
    {
        $this->setMethodName('edit');
        $dsTeam = &$this->dsTeam; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTTEAM_ACT_EDIT) {
                $this->buTeam->getTeamByID($_REQUEST['teamID'], $dsTeam);
                $teamID = $_REQUEST['teamID'];
            } else {                                                                    // creating new
                $dsTeam->initialise();
                $dsTeam->setValue('teamID', '0');
                $teamID = '0';
            }
        } else {                                                                        // form validation error
            $dsTeam->initialise();
            $dsTeam->fetchNext();
            $teamID = $dsTeam->getValue('teamID');
        }
        if ($_REQUEST['action'] == CTTEAM_ACT_EDIT && $this->buTeam->canDelete($_REQUEST['teamID'])) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTTEAM_ACT_DELETE,
                        'teamID' => $teamID
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
                    'action' => CTTEAM_ACT_UPDATE,
                    'teamID' => $teamID
                )
            );
        $urlDisplayList =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTTEAM_ACT_DISPLAY_LIST
                )
            );
        $this->setPageTitle('Edit User Team');
        $this->setTemplateFiles(
            array('TeamEdit' => 'TeamEdit.inc')
        );
        $this->template->set_var(
            array(
                'teamID' => $teamID,
                'name' => Controller::htmlInputText($dsTeam->getValue('name')),
                'nameMessage' => Controller::htmlDisplayText($dsTeam->getMessage('name')),
                'level' => Controller::htmlInputText($dsTeam->getValue('level')),
                'levelMessage' => Controller::htmlDisplayText($dsTeam->getMessage('level')),
                'activeFlagChecked' => Controller::htmlChecked($dsTeam->getValue('activeFlag')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList
            )
        );

        $teamRoles = $this->buTeam->getTeamRoles();
        // Role selection
        $this->template->set_block('TeamEdit', 'teamRoleBlock', 'teamRoles');

        foreach ($teamRoles as $teamRole) {
            $teamRoleSelected = ($dsTeam->getValue('teamRoleID') == $teamRole['teamRoleID']) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'teamRoleSelected' => $teamRoleSelected,
                    'teamRoleID' => $teamRole['teamRoleID'],
                    'teamRoleName' => $teamRole['name']
                )
            );
            $this->template->parse('teamRoles', 'teamRoleBlock', true);
        }

        $this->template->parse('CONTENTS', 'TeamEdit', true);
        $this->parsePage();
    }// end function edit

    function update()
    {
        $this->setMethodName('update');
        $dsTeam = &$this->dsTeam;
        $this->formError = (!$this->dsTeam->populateFromArray($_REQUEST['team']));
        if ($this->formError) {
            if ($this->dsTeam->getValue('teamID') == '') {                    // attempt to insert
                $_REQUEST['action'] = CTTEAM_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTTEAM_ACT_CREATE;
            }
            $this->edit();
            exit;
        }

        $this->buTeam->updateTeam($this->dsTeam);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                array(
                    'teamID' => $this->dsTeam->getValue('teamID'),
                    'action' => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }

    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buTeam->deleteTeam($_REQUEST['teamID'])) {
            $this->displayFatalError('Cannot delete this Team');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTTEAM_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    private function performanceReport()
    {

    }
}// end of class
?>