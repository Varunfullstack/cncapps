<?php
/**
 * Call Stats Export controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUCallStats.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_func'] . '/Common.inc.php');
// Messages
define('CTCALLSTATS_MSG_INV_NOT_FND', 'No Invoices Found');
define('CTCALLSTATS_MSG_PUR_NOT_FND', 'No Purchases Found');
// Actions
define('CTCALLSTATS_ACT_SELECT', 'select');
define('CTCALLSTATS_ACT_GENERATE', 'generate');

class CTCallStats extends CTCNC
{

    var $dsDateRange;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buCallStats = new BUCallStats($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case CTCALLSTATS_ACT_GENERATE:
                $this->generate();
                break;
            case CTCALLSTATS_ACT_SELECT:
                $this->select();
                break;
            default:
                $this->select();
                break;
        }
    }

    /**
     * Display search form
     * @access private
     */
    function select()
    {
        $this->setMethodName('select');

        if (!$this->getFormError()) {
            $this->buCallStats->initialiseDataset($this->dsDateRange);
        }

        $this->setTemplateFiles('CallStats', 'CallStats.inc');

        $this->setPageTitle('Call Statistics');

        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCALLSTATS_ACT_GENERATE
            )
        );

        $this->template->set_var(
            array(
                'startDate' => Controller::dateYMDtoDMY($this->dsDateRange->getValue('startDate')),
                'startDateMessage' => Controller::htmlDisplayText($this->dsDateRange->getMessage('startDate')),
                'endDate' => Controller::dateYMDtoDMY($this->dsDateRange->getValue('endDate')),
                'endDateMessage' => Controller::htmlDisplayText($this->dsDateRange->getMessage('endDate')),
                'urlSubmit' => $urlSubmit
            )
        );

        $this->template->parse('CONTENTS', 'CallStats', true);
        $this->parsePage();
    }

    function generate()
    {
        $this->setMethodName('generate');
        $this->buCallStats->initialiseDataset($this->dsDateRange);
        if (!$this->dsDateRange->populateFromArray($_REQUEST['stats'])) {
            $this->setFormErrorOn();
            $this->select();                    //redisplay with errors
            exit;
        }
        if ($this->dsDateRange->getValue('startDate') .
            $this->dsDateRange->getValue('endDate')
            == ''
        ) {
            $this->setFormErrorMessage('Please use parameters');
            $this->select(); //redisplay with errors
            exit;
        }

        $this->buCallStats->getStatsByDateRange(
            $this->dsDateRange->getValue('startDate'),
            $this->dsDateRange->getValue('endDate'),
            $dsCallStats
        );

        if ($dsCallStats->rowCount() > 0) {
            Header('Content-type: text/plain');
            Header('Content-Disposition: attachment; filename=callstats.csv');
            echo $dsCallStats->getColumnNamesAsString() . "\n";
            while ($dsCallStats->fetchNext()) {
                echo $dsCallStats->getColumnValuesForExcel() . "\n";
            }
            $this->pageClose();
            exit;
        } else {
            $this->setFormErrorMessage('No activities found - try changing the search parameters');
            $this->select(); //redisplay with errors
        }
    }


}// end of class
?>