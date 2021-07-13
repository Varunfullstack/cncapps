<?php
/**
 * My Account controller class
 * CNC Ltd
 *
 * @access public
 * @authors Mustafa Taha
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUTeam.inc.php');


class CTMySettings extends CTCNC
{
    /** @var DSForm */
    public $dsUser;

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
        // $roles = [
        //     "technical",
        // ];
        // if (!self::hasPermissions($roles)) {
        //     Header("Location: /NotAllowed.php");
        //     exit;
        // }
        $this->setMenuId(1002);
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
            case "mySettings":
                echo json_encode($this->saveMySettings());
                exit;
            case "sendEmailAssignedService":
                echo $this->setSendEmailAssignedService();
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
        $this->loadReactScript('MySettingsComponent.js');
        $this->loadReactCSS('MySettingsComponent.css');
        $this->template->parse(
            'CONTENTS',
            'MySettings',
            true
        );
        $this->parsePage();
    }

    function getMyData()
    {
        $BUUser = new BUUser($this);
        $dsUser = new DataSet($this);
        $BUUser->getUserByID($this->userID, $dsUser);
        $teamId    = $dsUser->getValue(DBEJUser::teamID);
        $managerId = $dsUser->getValue(DBEJUser::managerID);
        $userId    = $dsUser->getValue(DBEJUser::userID);
        $result    = array(
            'name'                     => $dsUser->getValue(DBEJUser::name),
            'jobTitle'                 => $dsUser->getValue(DBEJUser::jobTitle),
            'team'                     => !$teamId ? '' : $this->getTeamName($teamId),
            'manager'                  => !$managerId ? '' : $this->getMangerName($managerId),
            'employeeNo'               => $dsUser->getValue(DBEJUser::employeeNo),
            'startDate'                => $dsUser->getValue(DBEJUser::startDate),
            'sendEmailAssignedService' => $dsUser->getValue(DBEJUser::sendEmailWhenAssignedService),
            'userLog'                  => $this->getUserTimeLog($userId),
            "bccOnCustomerEmails"      => $dsUser->getValue(DBEUser::bccOnCustomerEmails),
            "callBackEmail"            => $dsUser->getValue(DBEUser::callBackEmail)
        );
        return $result;
    }

    function getTeamName($teamId)
    {
        $buTeam = new BUTeam($this);
        $dsTeam = new DataSet($this);
        $buTeam->getTeamByID($teamId, $dsTeam);
        return $dsTeam->getValue(DBETeam::name);
    }

    function getMangerName($mangerId)
    {
        $BUUser = new BUUser($this);
        $dsUser = new DataSet($this);
        $BUUser->getUserByID($mangerId, $dsUser);
        return $dsUser->getValue(DBEJUser::name);
    }

    function getUserTimeLog($userId)
    {
        $sql = "select * from user_time_log where userID=:userId 
                ORDER BY  `loggedDate` DESC  
                limit 5";
        return $this->fetchAll($sql, ['userId' => $userId]);
    }

    public function fetchAll($query, $params)
    {
        $db   = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASSWORD
        );
        $stmt = $db->prepare($query, $params);
        foreach ($params as $key => $value) {
            if (($value != null || $value == '0') && is_numeric($value)) {
                $params[$key] = (int)$value;
                $stmt->bindParam($key, $params[$key], PDO::PARAM_INT);
            } else
                $stmt->bindParam($key, $params[$key]);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function setSendEmailAssignedService()
    {
        $newVal = $this->getParam('sendEmailAssignedService');
        if (!isset($newVal)) return false;
        $dbeUser = new DBEUser($this);
        $dbeUser->setValue(DBEJUser::userID, $this->userID);
        $dbeUser->getRow();
        $dbeUser->setValue(DBEJUser::sendEmailWhenAssignedService, $newVal);
        $dbeUser->updateRow();
        return true;
    }

    function saveMySettings()
    {
        $body    = json_decode(file_get_contents('php://input'));
        $dbeUser = new DBEUser($this);
        $dbeUser->setValue(DBEJUser::userID, $this->userID);
        $dbeUser->getRow();
        $dbeUser->setValue(DBEJUser::sendEmailWhenAssignedService, $body->sendEmailAssignedService);
        $dbeUser->setValue(DBEJUser::bccOnCustomerEmails, $body->bccOnCustomerEmails);
        $dbeUser->setValue(DBEJUser::callBackEmail, $body->callBackEmail);
        $dbeUser->updateRow();
        return ["status" => true];
    }
}
