<?php

/**
 * Domain renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUTeam.inc.php');


class CTMySettings extends CTCNC
{
    /** @var DSForm */
    public $dsUser;
    function __construct(
        $requestMethod,
        $postVars,
        $getVars,
        $cookieVars,
        $cfg
    ) {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        // $roles = [
        //     "technical",
        // ];
        // if (!self::hasPermissions($roles)) {
        //     Header("Location: /NotAllowed.php");
        //     exit;
        // }
        $this->setMenuId(1001);
        //$this->buPassword = new BUPassword($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {

        switch ($this->getAction()) {
            case "getMySettings":
                echo json_encode($this->getMyData());
                exit;
            default:
                $this->getTemplate();
        }
    }

    function getTemplate()
    {
        $this->setMethodName('displayMySettings');

        $this->setTemplateFiles(
            array(
                'MySettings' => 'MySettings.inc'
            )
        );
        $this->setPageTitle('My Account');

        $this->template->parse(
            'CONTENTS',
            'MySettings',
            true
        );

        $this->parsePage();
    }

    function getMyData()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5);
        $BUUser = new BUUser($this);
        $dsUser = new DataSet($this);

       

        $BUUser->getUserByID(139, $dsUser);
        $teamId=$dsUser->getValue(DBEJUser::teamID);
        $managerId=$dsUser->getValue(DBEJUser::managerID);
        $userId=$dsUser->getValue(DBEJUser::userID);
        $result = array(
            'name' => $dsUser->getValue(DBEJUser::name),
            'jobTitle' => $dsUser->getValue(DBEJUser::jobTitle),
            'team' => $this->getTeamName($teamId),
            'manager' => $this->getMangerName($managerId),
            'employeeNo' => $dsUser->getValue(DBEJUser::employeeNo),
            'startDate' => $dsUser->getValue(DBEJUser::startDate),            
            'userLog' =>$this->getUserTimeLog($userId)
        );
        return $result;
    }
    function getTeamName($teamId)
    {
        $buTeam = new BUTeam($this);
        $dsTeam = new DataSet($this);
        $buTeam->getTeamByID($teamId,$dsTeam);
        return  $dsTeam->getValue(DBETeam::name);
    }
    function getMangerName($mangerId)
    {
        $BUUser = new BUUser($this);
        $dsUser = new DataSet($this);
        $BUUser->getUserByID($mangerId,$dsUser);
        return  $dsUser->getValue(DBEJUser::name);
    }
    function getUserTimeLog($userId)
    {
        $sql="select * from user_time_log where userID=:userId 
                ORDER BY  `loggedDate` DESC  
                limit 5";        
        return $this->fetchAll($sql,['userId'=>139]);
    }
    
    public  function fetchAll($query,$params)
    {         
        $db = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
            DB_USER,
            DB_PASSWORD
        );
        $stmt=$db->prepare($query,$params);
        foreach($params as $key=>$value)
        {
            if(($params[ $key]!=null||$params[ $key]=='0')&&is_numeric($params[ $key]))
            {
                $params[ $key]=(int)$params[ $key];
                $stmt->bindParam($key,  $params[ $key],PDO::PARAM_INT);
            }
            else
                $stmt->bindParam($key,  $params[ $key]);
        }        
        $stmt->execute();
        $result=$stmt->fetchAll(PDO::FETCH_ASSOC);        
        return $result;
    }
}
