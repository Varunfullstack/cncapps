<?php
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSite.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

class CTSDManagerDashboard extends CTCNC
{

    var $dsSecondsiteImage = '';

    var $buSecondsite = '';

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
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            default:
                $this->display();
                break;
        }
    }

    function display()
    {

        $this->setPageTitle('SD Manager Dashboard');

        $this->setTemplateFiles(
            array('SDManagerDashboard' => 'SDManagerDashboard')
        );

        $this->template->set_var(
            array(
                'test' => 'yes'
            )
        );
        $this->template->parse(
            'CONTENTS',
            'SDManagerDashboard',
            true
        );
        $this->parsePage();
    }
}// end of class
?>