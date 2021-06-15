<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Data\DBConnect;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEProject.inc.php');
require_once($cfg['path_dbe'] . '/DBEProjectIssues.inc.php');
require_once($cfg['path_bu'] . '/BUExpense.inc.php');


class CTReports extends CTCNC
{
    const CONST_REPORT_CATEGORIES = 'reportCategories';
    const CONST_CATEGORY_REPORTS  = 'categoryReports';
    const CONST_REPORT_PARAMTERS  = 'reportParamters';

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
        //     "technical"
        // ];
        // if (!self::hasPermissions($roles)) {
        //     Header("Location: /NotAllowed.php");
        //     exit;
        // }
        //$this->setMenuId(107);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($this->getAction()) {
            case self::CONST_REPORT_CATEGORIES:
                switch ($method) {
                    case 'GET':
                        echo json_encode($this->getReportsCategories(), JSON_NUMERIC_CHECK);
                        break;
                }
                break;
            case self::CONST_CATEGORY_REPORTS:
                echo json_encode($this->getCategoryReports(), JSON_NUMERIC_CHECK);
                break;
            case self::CONST_REPORT_PARAMTERS:
                echo json_encode($this->getReportParamters(), JSON_NUMERIC_CHECK);
                break;
            default:
                $this->setTemplate();
        }
    }

    function setTemplate()
    {
        if (isset($_REQUEST["hideMenu"])) $this->hideMenu(); else
            $this->setPageTitle('Reports');
        $this->setTemplateFiles(
            array('Reports' => 'Reports.rct')
        );
        $this->loadReactScript('ReportsComponent.js');
        $this->loadReactCSS('ReportsComponent.css');
        $this->template->parse(
            'CONTENTS',
            'Reports',
            true
        );
        $this->parsePage();
    }

    function getReportsCategories()
    {
        $active = @$_REQUEST['active'] ?? 1;
        $query  = "select id,title,active from reports_categories";
        if ($active) $query .= " where active=1";
        return DBConnect::fetchAll($query);
    }

    function getCategoryReports()
    {
        $categoryID = @$_REQUEST['categoryID'];
        if (!$categoryID) throw new Exception("category id is missing", 400);
        $query = "
        SELECT 
        r.id,
        r.title,
        r.component,
        r.active
        from report_categories rc join reports r on rc.reportID=r.id
        where rc.categoryID=:categoryID
        ";
        return DBConnect::fetchAll($query, ["categoryID" => $categoryID]);
    }

    function getReportParamters()
    {
        $reportID = @$_REQUEST["reportID"];
        if (!$reportID) throw new Exception("report id is missing", 400);
        $query  = "select rp.id,
                rp.reportID,
                rp.paramterID,
                rp.title,
                rp.required,
                rp.paramterOrder,
                p.title defaultTitle,                
                p.name
                from report_paramters rp 
                join reports_paramters p
                    on rp.paramterID=p.id
                where reportID=:reportID ";
        $params = DBConnect::fetchAll($query, ["reportID" => $reportID]);
        return $params;
    }
}
