<?php
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");

class CTProjectOptions extends CTCNC
{
    const CONS_PROJECT_STAGES='projectStages';
    const CONS_PROJECT_TYPES='projectTypes';

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
            $cfg,
            false
        );
        // $action = @$_REQUEST['action'];
        // if ($action != self::DAILY_STATS_SUMMARY && !self::isSdManager() ) {
        //     Header("Location: /NotAllowed.php");
        //     exit;
        // }
        $this->setMenuId(813);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $method=$_SERVER['REQUEST_METHOD'] ;
        switch ($this->getAction()) {             
            case self::CONS_PROJECT_STAGES:
                if($method=='GET')
                    echo json_encode($this->getProjectStages(),JSON_NUMERIC_CHECK);
                else if($method=='POST')
                    echo json_encode($this->addProjectStage());
                else if($method=='DELETE')
                    echo json_encode($this->deleteProjectStage());
                else if($method=='PUT')
                    echo json_encode($this->updateProjectStage());
                break;
            case self::CONS_PROJECT_TYPES:
                if($method=='GET')
                    echo json_encode($this->getProjectTypes(),JSON_NUMERIC_CHECK);
                else if($method=='POST')
                    echo json_encode($this->addProjectType());
                else if($method=='DELETE')
                    echo json_encode($this->deleteProjectType());
                else if($method=='PUT')
                    echo json_encode($this->updateProjectType());
                break;
            default:
                $this->setTemplate();
                break;
        }
    }

    function setTemplate()
    {        
        $this->setPageTitle('Project Options');
        $this->setTemplateFiles(
            array('ProjectOptions' => 'ProjectOptions.rct')
        );
        $this->loadReactScript('ProjectOptionsComponent.js');
        $this->loadReactCSS('ProjectOptionsComponent.css');
        $this->template->parse(
            'CONTENTS',
            'ProjectOptions',
            true
        );
        $this->parsePage();
    }

    //------------------start ProjectStages
    function getProjectStages(){
        return DBConnect::fetchAll("select id, name,stageOrder from projectstages order by stageOrder");
    }

    function addProjectStage(){
        $body=$this->getBody();
        if($body->name!='')
        {
            $stageOrder=DBConnect::fetchOne("select max(stageOrder)+1 stageOrder from projectstages")["stageOrder"];
            $status=DBConnect::execute(" insert into projectstages(name,stageOrder) values(:name,:stageOrder)",
            ["name"=>$body->name,"stageOrder"=>$stageOrder]);
            return ["status"=>$status];
        }
        else return ["status"=>false];
    }

    function updateProjectStage(){
        $id=@$_REQUEST['id'];
        $body=$this->getBody();
        $type=DBConnect::fetchOne("select * from projectstages where id=:id",["id"=>$id]);
        if(!$type)
            throw new Exception("Not found",404);
        return ["status"=>DBConnect::execute("update projectstages set name=:name,stageOrder=:stageOrder where id=:id",
                ["id"=>$id,"name"=>$body->name,"stageOrder"=>$body->stageOrder])];
    }

    function deleteProjectStage(){
        $id=@$_REQUEST['id'];
        $type=DBConnect::fetchOne("select * from projectstages where id=:id",["id"=> $id]);
        if(!$type)
            throw new Exception("Not found",404);
        
        return DBConnect::execute("delete from projectstages  where id=:id",
                ["id"=>$id]);
    }
    //------------------End ProjectStages

    //------------------start ProjectTypes
    function getProjectTypes(){
        return DBConnect::fetchAll("select id, name, includeInWeeklyReport,notes from ProjectTypes",[]);
    }

    function addProjectType(){
        $body=$this->getBody();
        if($body->name!='')
        return ["status"=>DBConnect::execute(" insert into ProjectTypes(name,includeInWeeklyReport,notes) values(:name,:includeInWeeklyReport,:notes)",
                                            ["name"=>$body->name,"includeInWeeklyReport"=>$body->includeInWeeklyReport,"notes"=>$body->notes])];
        else return ["status"=>false];
    }

    function updateProjectType(){
        $id=@$_REQUEST['id'];
        $body=$this->getBody();
        $type=DBConnect::fetchOne("select * from ProjectTypes where id=:id",["id"=>$id]);
        if(!$type)
            throw new Exception("Not found",404);

        return ["status"=>DBConnect::execute("update ProjectTypes set name=:name,includeInWeeklyReport=:includeInWeeklyReport,notes=:notes where id=:id",
                ["id"=>$id,"name"=>$body->name,"includeInWeeklyReport"=>$body->includeInWeeklyReport,"notes"=>$body->notes])];
    }

    function deleteProjectType(){
        $id=@$_REQUEST['id'];
        $type=DBConnect::fetchOne("select * from ProjectTypes where id=:id",["id"=> $id]);
        if(!$type)
            throw new Exception("Not found",404);
        
        return DBConnect::execute("delete from ProjectTypes  where id=:id",
                ["id"=>$id]);
    }
    //------------------End ProjectTypes

}
