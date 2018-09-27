<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');

// Actions
class CTCallresolve extends CTCNC
{
    var $buActivity;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buActivity = new BUActivity($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        if ($GLOBALS['auth']->is_authenticated() != 3) {  // graham
            $this->raiseError('You are not authorised to run this program');
            exit;
        }
        switch ($_REQUEST['action']) {
            case 'resolveCalls':
                $this->resolveCalls();
                break;
            case 'displayForm':
            default:
                $this->displayForm();
                break;
        }
    }

    /**
     * Export expenses that have not previously been exported
     * @access private
     */
    function displayForm()
    {
        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'resolveCalls'
            )
        );
        $this->setPageTitle('Resolve Calls');
        $this->setTemplateFiles('CallResolve', 'CallResolve.inc');

        if (!$this->getFormError()) {
            $this->buActivity->initialiseResolveForm($this->dsCallResolve);
        }

        $this->template->set_var(
            array(
                'startDate' => Controller::dateYMDtoDMY($this->dsCallResolve->getValue('startDate')),
                'endDate' => Controller::dateYMDtoDMY($this->dsCallResolve->getValue('endDate')),
                'startDateMessage' => Controller::dateYMDtoDMY($this->dsCallResolve->getMessage('startDate')),
                'endDateMessage' => Controller::dateYMDtoDMY($this->dsCallResolve->getMessage('endDate')),
                'urlSubmit' => $urlSubmit
            )
        );
        $this->template->parse('CONTENTS', 'CallResolve', true);
        $this->parsePage();
    }

    function resolveCalls()
    {
        $this->setMethodName('exportExpenseGenerate');
        $this->buActivity->initialiseResolveForm($this->dsCallResolve);
        if (!$this->dsCallResolve->populateFromArray($_REQUEST['callResolve'])) {
            $this->setFormErrorOn();
            $this->displayForm(); //redisplay with errors
        } else {
            // do the resolving
            $filePath = $this->buActivity->resolveCalls($this->dsCallResolve);
            if ($filePath) {
                $this->setFormErrorMessage('Calls resolved and logged to ' . $filePath);
            } else {
                $this->setFormErrorMessage('No calls to resolve');
            }
            $this->displayForm();
        }
    }

    function parsePage()
    {
        $urlLogo = '';
        $this->template->set_var(
            array(
                'urlLogo' => $urlLogo,
                'txtHome' => 'Home'
            )
        );
        parent::parsePage();
    }
}// end of class
?>