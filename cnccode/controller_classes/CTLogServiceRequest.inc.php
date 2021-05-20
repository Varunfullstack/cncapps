<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Mustafa Taha
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEPendingReopened.php');

// Actions
class CTLogServiceRequest extends CTCNC
{
    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg,
                         $checkPermissions = true
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        if ($checkPermissions) {

            $roles = [
                "technical",
            ];
            if (!self::hasPermissions($roles)) {
                Header("Location: /NotAllowed.php");
                exit;
            }
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->setTemplate();
    }

    function setTemplate()
    {
        $this->setMethodName('setTemplate');
        $this->setMenuId(101);
        $this->setPageTitle('Log Service Request');
        $this->setTemplateFiles(
            'LogServiceRequest',
            'LogServiceRequest.inc'
        );
        $this->loadReactScript('LogServiceRequestComponent.js');
        $this->loadReactCSS('LogServiceRequestComponent.css');
        $this->template->parse(
            'CONTENTS',
            'LogServiceRequest',
            true
        );
        $this->parsePage();
    }


}
