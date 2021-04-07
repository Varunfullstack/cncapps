<?php
/**
 * Team controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

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
    const CONST_TEAMS='teams';
    const CONST_ROLES='roles';
    /** @var DSForm */
    public $dsTeam;
    /** @var BUTeam */
    public $buTeam;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = SENIOR_MANAGEMENT_PERMISSION;
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
            case self::CONST_TEAMS:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getTeams(),JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo  json_encode($this->addTeam(),JSON_NUMERIC_CHECK);
                        break;
                    case 'PUT':
                        echo  json_encode($this->updateTeam(),JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo  json_encode($this->deleteTeam(),JSON_NUMERIC_CHECK);
                        break;
                    default:
                        # code...
                        break;
                }
                exit;        
            case self::CONST_ROLES:
                echo  json_encode($this->getRoles(),JSON_NUMERIC_CHECK);
                break;
          
            case CTTEAM_ACT_DISPLAY_LIST:
                echo  json_encode($this->getTeams(),JSON_NUMERIC_CHECK);
                break;
            default:
                $this->displayList();
                break;
        }
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
        $this->loadReactScript('TeamComponent.js');
        $this->loadReactCSS('TeamComponent.css');     

        $this->template->parse('CONTENTS', 'TeamList', true);
        $this->parsePage();
    }
    
    //------------------------- new 

    function getTeams(){
        $teams = $this->buTeam->getAll();  
        $data  = [];       
        foreach ($teams as $team) {
            $teamID = $team['teamID'];              
            $canDelete=false;
            if ($this->buTeam->canDelete($teamID)) {
                $canDelete=true;
            }
            $data []= array(
                    'teamID'       => $teamID,
                    'name'         => Controller::htmlDisplayText($team['name']),
                    'teamRoleName' => Controller::htmlDisplayText($team['teamRoleName']),
                    'leaderId'     => Controller::htmlDisplayText($team['leaderId']),
                    'teamRoleID'       => Controller::htmlDisplayText($team['teamRoleID']),
                    'level'        => Controller::htmlDisplayText($team['level']),
                    'activeFlag'   => Controller::htmlDisplayText($team['activeFlag']),
                    'leaderName'   => Controller::htmlDisplayText($team['leaderName']),
                    'canDelete'      => $canDelete,                    
                );
        }
        return  $data;
    }

    function addTeam(){
        $id=DBConnect::fetchOne("SELECT MAX(teamID)+1 id FROM team")["id"];  
        $body=$this->getBody();
        $dbeTeam=new DBETeam($this);        
        if(!$body->name)
            return $this->fail(APIException::badRequest,"Name required");
        $dbeTeam->setValue(DBETeam::teamID,$id);
        $dbeTeam->setValue(DBETeam::activeFlag,$body->activeFlag);
        $dbeTeam->setValue(DBETeam::leaderId,$body->leaderId);
        $dbeTeam->setValue(DBETeam::level,$body->level);
        $dbeTeam->setValue(DBETeam::name,$body->name);
        $dbeTeam->setValue(DBETeam::teamRoleID,$body->teamRoleID);
        $dbeTeam->insertRow();
        return $this->success();
    }

    function updateTeam(){
        $body=$this->getBody();
        $dbeTeam=new DBETeam($this);
        $dbeTeam->getRow($body->teamID);
        if(!$dbeTeam->rowCount)
            return $this->fail(APIException::notFound,"Not found");
        $dbeTeam->setValue(DBETeam::activeFlag,$body->activeFlag);
        $dbeTeam->setValue(DBETeam::leaderId,$body->leaderId);
        $dbeTeam->setValue(DBETeam::level,$body->level);
        $dbeTeam->setValue(DBETeam::name,$body->name);
        $dbeTeam->setValue(DBETeam::teamRoleID,$body->teamRoleID);
        $dbeTeam->updateRow();
        return $this->success();
    }

    function deleteTeam(){
        $teamID=@$_REQUEST["id"];
        $dbeTeam=new DBETeam($this);
        $dbeTeam->getRow($teamID);
        if(!$dbeTeam->rowCount)
            return $this->fail(APIException::notFound,"Not found");
        $dbeTeam->deleteRow();
        return $this->success();
    }
    function getRoles(){
        $teamRoles = $this->buTeam->getTeamRoles();
        // Role selection
        $data=[];
        foreach ($teamRoles as $teamRole) {          
            $data []=
                array(                    
                    'id'       => $teamRole['teamRoleID'],
                    'name'     => $teamRole['name']
                );                        
        }
        return $this->success($data);
    }
}