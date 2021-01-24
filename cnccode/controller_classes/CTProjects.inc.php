<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\JcTable;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUSalesOrder.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEOrdhead.inc.php');
require_once($cfg['path_bu'] . '/BUExpense.inc.php');
// Actions
 


class CTProjects extends CTCNC
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
        $roles = [
            "technical"
        ];

        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        } 

        $this->setMenuId(107);
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
        }
    }
    function setTemplate()
    {
        $this->setPageTitle('Projects');
        $this->setTemplateFiles(
            array('Projects' => 'Projects.rct')
        );
        $this->loadReactScript('ProjectsComponent.js');
        $this->loadReactCSS('ProjectsComponent.css');
        $this->template->parse(
            'CONTENTS',
            'Projects',
            true
        );
        $this->parsePage();
    }


}
