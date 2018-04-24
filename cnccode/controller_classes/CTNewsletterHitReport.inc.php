<?php
/**
 * Newsletter hit controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUNewsletterHitReport.inc.php');

class CTNewsletterHitReport extends CTCNC
{

    public $buNewsletterHitReport;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buNewsletterHitReport = new BUNewsletterHitReport ($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
        switch ($_REQUEST['action']) {
            case 'search':
            default:
                $this->searchForm();
                break;
        }
    }

    /**
     * Display search form
     * @access private
     */
    function searchForm()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // validate
            if ($_FILES['pureFile']['name'] == '') {
                $this->setFormErrorMessage('Please enter a file path');
            }
            if (!is_uploaded_file($_FILES['pureFile']['tmp_name'])) {                    // Possible hack?
                $this->setFormErrorMessage('Document not loaded - is it bigger than 6 MBytes?');
            }

            if (!$this->formError) {

                $this->buNewsletterHitReport->uploadFile(
                    $_FILES['pureFile']
                );

                exit;
            }

        }

        $this->setMethodName('searchForm');

        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCNC_ACT_SEARCH
            )
        );

        $this->setPageTitle('newsletter hit report');
        $this->setTemplateFiles('NewsletterHitReport', 'NewsletterHitReport.inc');

        $this->template->parse('CONTENTS', 'NewsletterHitReport', true);


        $this->parsePage();

    }

    function generateCSV()
    {
        $fileName = 'ENGACT.CSV';
        Header('Content-type: text/plain');
        Header('Content-Disposition: attachment; filename=' . $fileName);
        echo $this->dsSearchResults->getColumnNamesAsString() . "\n";
        while ($this->dsSearchResults->fetchNext()) {
            echo $this->dsSearchResults->getColumnValuesForExcel() . "\n";
        }
        $this->pageClose();
        exit;
    }

}// end of class
?>