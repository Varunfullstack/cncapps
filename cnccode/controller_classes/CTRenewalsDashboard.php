<?php
global $cfg;

use CNCLTD\SDManagerDashboard\ServiceRequestSummaryDTO;

require_once($cfg['path_ct'] . '/CTCurrentActivityReport.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSite.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");

class CTRenewalsDashboard extends CTCurrentActivityReport
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
        $roles = [RENEWALS_PERMISSION, TECHNICAL_PERMISSION];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }        
        $this->setMenuId(601);
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
        $this->setPageTitle('Renewals Dashboard');
        $this->setTemplateFiles(
            array('RenewalsDashboard' => 'RenewalsDashboard.rct')
        );
        $this->loadReactScript('RenewalsDashboardComponent.js');
        $this->loadReactCSS('RenewalsDashboardComponent.css');
        $this->template->parse(
            'CONTENTS',
            'RenewalsDashboard',
            true
        );
        $this->parsePage();
    } 
}
