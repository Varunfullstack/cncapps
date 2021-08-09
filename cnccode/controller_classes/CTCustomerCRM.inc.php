<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 09/01/2018
 * Time: 18:05
 */
global $cfg;
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUSector.inc.php');
require_once($cfg['path_dbe'] . '/DBEJOrdhead.inc.php');
require_once($cfg['path_bu'] . '/BUPortalCustomerDocument.inc.php');
require_once($cfg['path_dbe'] . '/DBEJSite.php');
require_once($cfg['path_ct'] . '/CTCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');


class CTCustomerCRM extends CTCustomer
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
            $cfg
        );
        $roles = ACCOUNT_MANAGEMENT_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(403);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($this->getAction()) {            
            default:
                $this->search();
                break;
        }
    }

    function search()
    {
        $this->setPageTitle('Customer CRM');
        $this->setTemplateFiles(
            'CustomerCRM',
            'CustomerCRM'
        );
        $this->template->parse(
            'CONTENTS',
            'CustomerCRM',
            true
        );
        $this->loadReactScript('CustomerCRMComponent.js');
        $this->loadReactCSS('CustomerCRMComponent.css'); 
        $this->parsePage();     
    }

}