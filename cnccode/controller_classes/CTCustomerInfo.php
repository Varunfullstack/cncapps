<?php
global $cfg;

require_once($cfg['path_ct'] . '/CTCurrentActivityReport.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSite.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");

class CTCustomerInfo extends CTCurrentActivityReport
{

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
        // if ($action != self::DAILY_STATS_SUMMARY && !self::isSdManager() && !self::isSRQueueManager()) {
        //     Header("Location: /NotAllowed.php");
        //     exit;
        // }
        // $this->setMenuId(201);
    }


    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            
            default:
                $this->setTemplate();
                break;
        }
    }


    function setTemplate()
    {
        $isP5 = isset($_REQUEST['showP5']);
        $this->setPageTitle('Customer Information');
        $this->setTemplateFiles(
            array('CustomerInfo' => 'reactCustomerInfo.rct')
        );
        $this->loadReactScript('CustomerInfoComponent.js');
        $this->loadReactCSS('CustomerInfoComponent.css');
        $this->template->parse(
            'CONTENTS',
            'CustomerInfo',
            true
        );
        $this->parsePage();
    }

}
