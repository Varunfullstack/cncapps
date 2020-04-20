<?php
/**
 * Team controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUTeam.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTTEAM_ACT_DISPLAY_LIST', 'teamList');
define('CTTEAM_ACT_CREATE', 'createTeam');
define('CTTEAM_ACT_EDIT', 'editTeam');
define('CTTEAM_ACT_DELETE', 'deleteTeam');
define('CTTEAM_ACT_UPDATE', 'updateTeam');

class CTTeam extends CTCNC
{
    /** @var DSForm */
    public $dsTeam;
    /** @var BUTeam */
    public $buTeam;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(902);
        $this->buTeam = new BUTeam($this);
        $this->dsTeam = new DSForm($this);
        $this->dsTeam->copyColumnsFrom($this->buTeam->dbeTeam);
        $this->dsTeam->setNull(DBETeam::teamID, DA_ALLOW_NULL);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case CTTEAM_ACT_EDIT:

            case CTTEAM_ACT_CREATE:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->edit();
                break;

            case CTTEAM_ACT_DELETE:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->delete();
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

    /**
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsTeam = &$this->dsTeam; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == CTTEAM_ACT_EDIT) {
                $this->buTeam->getTeamByID($this->getParam('teamID'), $dsTeam);
                $teamID = $this->getParam('teamID');
            } else {                                                                    // creating new
                $dsTeam->initialise();
                $dsTeam->setValue(DBETeam::teamID, '0');
                $teamID = '0';
            }
        } else {                                                                        // form validation error
            $dsTeam->initialise();
            $dsTeam->fetchNext();
            $teamID = $dsTeam->getValue(DBETeam::teamID);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() == CTTEAM_ACT_EDIT && $this->buTeam->canDelete($this->getParam('teamID'))) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTTEAM_ACT_DELETE,
                        'teamID' => $teamID
                    )
                );
            $txtDelete = 'Delete';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTTEAM_ACT_UPDATE,
                    'teamID' => $teamID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
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
                'teamID'            => $teamID,
                'name'              => Controller::htmlInputText($dsTeam->getValue(DBETeam::name)),
                'nameMessage'       => Controller::htmlDisplayText($dsTeam->getMessage(DBETeam::name)),
                'level'             => Controller::htmlInputText($dsTeam->getValue(DBETeam::level)),
                'levelMessage'      => Controller::htmlDisplayText($dsTeam->getMessage(DBETeam::level)),
                'activeFlagChecked' => Controller::htmlChecked($dsTeam->getValue(DBETeam::activeFlag)),
                'urlUpdate'         => $urlUpdate,
                'urlDelete'         => $urlDelete,
                'txtDelete'         => $txtDelete,
                'urlDisplayList'    => $urlDisplayList
            )
        );

        $teamRoles = $this->buTeam->getTeamRoles();
        // Role selection
        $this->template->set_block('TeamEdit', 'teamRoleBlock', 'teamRoles');

        foreach ($teamRoles as $teamRole) {
            $teamRoleSelected = ($dsTeam->getValue(
                    DBETeam::teamRoleID
                ) == $teamRole['teamRoleID']) ? CT_SELECTED : null;

            $this->template->set_var(
                array(
                    'teamRoleSelected' => $teamRoleSelected,
                    'teamRoleID'       => $teamRole['teamRoleID'],
                    'teamRoleName'     => $teamRole['name']
                )
            );
            $this->template->parse('teamRoles', 'teamRoleBlock', true);
        }

        $dbeUser = new DBEUser($this);
        $dbeUser->getRows();
        $this->template->set_block('TeamEdit', 'leaderBlock', 'leaders');

        while ($dbeUser->fetchNext()) {
            $leaderSelected = ($dbeUser->getValue(DBEUser::userID) == $dsTeam->getValue(
                    DBETeam::leaderId
                )) ? CT_SELECTED : null;

            $this->template->set_var(
                [
                    "leaderID"       => $dbeUser->getValue(DBEUser::userID),
                    "leaderSelected" => $leaderSelected,
                    "leaderName"     => $dbeUser->getValue(DBEUser::name)
                ]
            );
            $this->template->parse('leaders', 'leaderBlock', true);
        }

        $this->template->parse('CONTENTS', 'TeamEdit', true);
        $this->parsePage();
    }

    /**
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buTeam->deleteTeam($this->getParam('teamID'))) {
            $this->displayFatalError('Cannot delete this Team');
            exit;
        } else {
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTTEAM_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }// end function edit

    /**
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsTeam->populateFromArray($this->getParam('team')));
        if ($this->formError) {
            if (!$this->dsTeam->getValue(DBETeam::teamID)) {
                $this->setAction(CTTEAM_ACT_EDIT);
            } else {
                $this->setAction(CTTEAM_ACT_CREATE);
            }
            $this->edit();
            exit;
        }

        $this->buTeam->updateTeam($this->dsTeam);

        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'teamID' => $this->dsTeam->getValue(DBETeam::teamID),
                'action' => CTCNC_ACT_VIEW
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('User Teams');
        $this->setTemplateFiles(
            array('TeamList' => 'TeamList.inc')
        );

        $teams = $this->buTeam->getAll();

        $urlCreate =
            Controller::buildLink(
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
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTTEAM_ACT_EDIT,
                            'teamID' => $teamID
                        )
                    );
                $txtEdit = '[edit]';

                $urlDelete = null;
                $txtDelete = null;
                if ($this->buTeam->canDelete($teamID)) {
                    $urlDelete =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action' => CTTEAM_ACT_DELETE,
                                'teamID' => $teamID
                            )
                        );
                    $txtDelete = '[delete]';
                }
                $this->template->set_var(
                    array(
                        'teamID'       => $teamID,
                        'name'         => Controller::htmlDisplayText($team['name']),
                        'teamRoleName' => Controller::htmlDisplayText($team['teamRoleName']),
                        'level'        => Controller::htmlDisplayText($team['level']),
                        'activeFlag'   => Controller::htmlDisplayText($team['activeFlag']),
                        'leaderName'   => Controller::htmlDisplayText($team['leaderName']),
                        'urlEdit'      => $urlEdit,
                        'urlDelete'    => $urlDelete,
                        'txtEdit'      => $txtEdit,
                        'txtDelete'    => $txtDelete
                    )
                );

                $this->template->parse('teams', 'TeamBlock', true);

            }//while $dsTeam->fetchNext()
        }
        $this->template->parse('CONTENTS', 'TeamList', true);
        $this->parsePage();
    }
}