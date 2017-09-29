<?php
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSite.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

class CTSecondSite extends CTCNC
{

    var $dsSecondsiteImage = '';

    var $buSecondsite = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buSecondsite = new buSecondsite($this);
        $this->dsSecondsiteImage = new DSForm($this);
        $this->dsSecondsiteImage->copyColumnsFrom($this->buSecondsite->dbeSecondsiteImage);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case 'edit':
            case 'add':
                $this->edit();
                break;

            case 'delete':
                $this->delete();
                break;

            case 'update':
                $this->update();
                break;

            case 'run':
                $this->run();
                break;

            case 'failureAnalysis':
                $this->failureAnalysis();
                break;

            case 'listAll':
            default:
                $this->listAll();
                break;
        }
    }

    /**
     * Edit/Add Further Action
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsSecondsiteImage = &$this->dsSecondsiteImage; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == 'edit') {
                $this->buSecondsite->getSecondsiteImageByID($_REQUEST['secondsiteImageID'], $dsSecondsiteImage);
                $secondsiteImageID = $_REQUEST['secondsiteImageID'];
            } else {                                                                    // creating new
                $dsSecondsiteImage->initialise();
                $dsSecondsiteImage->setValue('secondsiteImageID', '0');
                $dsSecondsiteImage->setValue('customerItemID', $_REQUEST['customerItemID']);
                $secondsiteImageID = '0';
            }
        } else {                                                                        // form validation error
            $dsSecondsiteImage->initialise();
            $dsSecondsiteImage->fetchNext();
            $secondsiteImageID = $dsSecondsiteImage->getValue('secondsiteImageID');
        }

        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'update',
                    'secondsiteImageID' => $secondsiteImageID
                )
            );
        $urlDisplayCustomerItem =
            $this->buildLink(
                'CustomerItem.php',
                array(
                    'customerItemID' => $this->dsSecondsiteImage->getValue('customerItemID'),
                    'action' => 'displayCI'
                )
            );
        $this->setPageTitle('Edit Secondsite Image');

        $this->setTemplateFiles(
            array('SecondsiteImageEdit' => 'SecondsiteImageEdit.inc')
        );

        $this->template->set_var(
            array(
                'customerItemID' => $dsSecondsiteImage->getValue('customerItemID'),

                'secondsiteImageID' => $secondsiteImageID,

                'imageName' => Controller::htmlInputText($dsSecondsiteImage->getValue('imageName')),

                'imageNameMessage' => Controller::htmlDisplayText($dsSecondsiteImage->getMessage('imageName')),

                'status' => $dsSecondsiteImage->getValue('status'),

                'imagePath' => $dsSecondsiteImage->getValue('imagePath'),

                'imageTime' => $dsSecondsiteImage->getValue('imageTime'),

                'urlUpdate' => $urlUpdate,

                'urlDisplayCustomerItem' => $urlDisplayCustomerItem
            )
        );
        $this->template->parse('CONTENTS', 'SecondsiteImageEdit', true);
        $this->parsePage();
    }

    function update()
    {
        $this->setMethodName('update');

        $dsSecondsiteImage = &$this->dsSecondsiteImage;

        $this->formError = (!$this->dsSecondsiteImage->populateFromArray($_REQUEST['secondsiteImage']));

        if ($this->formError) {
            if ($this->dsSecondsiteImage->getValue('secondsiteImageID') == '') {    // attempt to insert
                $_REQUEST['action'] = CTPROJECT_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTPROJECT_ACT_ACT;
            }
            $this->edit();
            exit;
        }

        $this->buSecondsite->updateSecondsiteImage($this->dsSecondsiteImage);

        $urlNext =
            $this->buildLink(
                'CustomerItem.php',
                array(
                    'customerItemID' => $this->dsSecondsiteImage->getValue('customerItemID'),
                    'action' => 'displayCI'
                )
            );
        header('Location: ' . $urlNext);
    }

    function delete()
    {
        $this->setMethodName('delete');

        $this->buSecondsite->getSecondsiteImageByID($_REQUEST['secondsiteImageID'], $dsSecondsiteImage);

        $this->buSecondsite->deleteSecondsiteImage($_REQUEST['secondsiteImageID']);

        $urlNext =
            $this->buildLink(
                'CustomerItem.php',
                array(
                    'action' => 'displayCI',
                    'customerItemID' => $dsSecondsiteImage->getValue('customerItemID')
                )
            );
        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * List all second site servers with status
     */
    function listAll()
    {
        $this->setMethodName('list');

        $outOfDate = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_OUT_OF_DATE);

        $serverNotFound = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_SERVER_NOT_FOUND);

        $imageNotFound = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_IMAGE_NOT_FOUND);

        $suspended = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_SUSPENDED);

        $badConfig = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_BAD_CONFIG);

        $passed = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_PASSED);

        $this->setPageTitle('2nd Site');

        $this->setTemplateFiles(array('SecondsiteList' => 'SecondsiteList.inc'));

        $this->template->setBlock('SecondsiteList', 'outOfDateBlock', 'outOfDate');

        foreach ($outOfDate as $record) {

            $imageTime = strftime("%d/%m/%Y %H:%M:%S", strtotime($record['imageTime']));

            $imageAgeDays = number_format((time() - strtotime($record['imageTime'])) / 86400, 0);

            $this->template->set_var(

                array(
                    'customerName' => $record['cus_name'],
                    'serverName' => $record['serverName'],
                    'serverPath' => $record['secondsiteLocationPath'],
                    'imageName' => $record['imageName'],
                    'imagePath' => $record['imagePath'],
                    'imageTime' => $imageTime,
                    'imageAgeDays' => $imageAgeDays,
                    'urlServer' => $this->getEditUrl($record['server_cuino']),
                    'urlRunCheck' => $this->getRunUrl($record['server_cuino'])
                )
            );

            $this->template->parse('outOfDate', 'outOfDateBlock', true);

        }

        $this->template->setBlock('SecondsiteList', 'serverNotFoundBlock', 'serverNotFound');

        foreach ($serverNotFound as $record) {

            $this->template->set_var(

                array(
                    'customerName' => $record['cus_name'],
                    'serverName' => $record['serverName'],
                    'serverPath' => $record['secondsiteLocationPath'],
                    'imageName' => $record['imageName'],
                    'urlServer' => $this->getEditUrl($record['server_cuino']),
                    'urlRunCheck' => $this->getRunUrl($record['server_cuino'])
                )
            );

            $this->template->parse('serverNotFound', 'serverNotFoundBlock', true);

        }

        $this->template->setBlock('SecondsiteList', 'imageNotFoundBlock', 'imageNotFound');

        foreach ($imageNotFound as $record) {

            $this->template->set_var(

                array(
                    'customerName' => $record['cus_name'],
                    'serverName' => $record['serverName'],
                    'serverPath' => $record['secondsiteLocationPath'],
                    'imageName' => $record['imageName'],
                    'urlServer' => $this->getEditUrl($record['server_cuino']),
                    'urlRunCheck' => $this->getRunUrl($record['server_cuino'])

                )
            );

            $this->template->parse('imageNotFound', 'imageNotFoundBlock', true);

        }

        $this->template->setBlock('SecondsiteList', 'badConfigBlock', 'badConfig');

        foreach ($badConfig as $record) {

            $this->template->set_var(

                array(
                    'customerName' => $record['cus_name'],
                    'serverName' => $record['serverName'],
                    'serverPath' => $record['secondsiteLocationPath'],
                    'imagePath' => $record['imagePath'],
                    'imageName' => $record['imageName'],
                    'urlServer' => $this->getEditUrl($record['server_cuino']),
                    'urlRunCheck' => $this->getRunUrl($record['server_cuino'])

                )
            );

            $this->template->parse('badConfig', 'badConfigBlock', true);

        }

        $this->template->setBlock('SecondsiteList', 'suspendedBlock', 'suspended');

        foreach ($suspended as $record) {

            if ($record['imageTime'] != '0000-00-00 00:00:00') {
                $imageTime = strftime("%d/%m/%Y %H:%M:%S", strtotime($record['imageTime']));

                $imageAgeDays = number_format((time() - strtotime($record['imageTime'])) / 86400, 0);
            } else {
                $imageTime = 'No Image';

                $imageAgeDays = '';

            }
            if ($record['secondsiteValidationSuspendUntilDate'] != '0000-00-00') {
                $suspendedUntil = strftime("%d/%m/%Y", strtotime($record['secondsiteValidationSuspendUntilDate']));
            } else {
                $suspendedUntil = 'No longer suspended';
            }
            $txtRunCheck = 'Check Now';

            $this->template->set_var(

                array(
                    'customerName' => $record['cus_name'],
                    'serverName' => $record['serverName'],
                    'serverPath' => $record['secondsiteLocationPath'],
                    'imagePath' => $record['imagePath'],
                    'imageName' => $record['imageName'],
                    'suspendedUntil' => $suspendedUntil,
                    'imageTime' => $imageTime,
                    'imageAgeDays' => $imageAgeDays,
                    'urlServer' => $this->getEditUrl($record['server_cuino']),
                    'urlRunCheck' => $this->getRunUrl($record['server_cuino']),
                    'txtRunCheck' => $txtRunCheck
                )
            );

            $this->template->parse('suspended', 'suspendedBlock', true);

        }

        $this->template->setBlock('SecondsiteList', 'passedBlock', 'passed');

        foreach ($passed as $record) {

            $imageTime = strftime("%d/%m/%Y %H:%M:%S", strtotime($record['imageTime']));

            $imageAgeDays = number_format((time() - strtotime($record['imageTime'])) / 86400, 0);

            $this->template->set_var(

                array(
                    'urlServer' => $this->getEditUrl($record['server_cuino']),
                    'customerName' => $record['cus_name'],
                    'serverName' => $record['serverName'],
                    'imageName' => $record['imageName'],
                    'imagePath' => $record['imagePath'],
                    'imageTime' => $imageTime,
                    'imageAgeDays' => $imageAgeDays
                )
            );

            $this->template->parse('passed', 'passedBlock', true);
        }

        $this->template->parse('CONTENTS', 'SecondsiteList', true);
        $this->parsePage();
    }

    /**
     * Run validation
     *
     */
    function run()
    {
        $this->buSecondsite->validateBackups($_REQUEST['customerItemID']);

        $urlNext =
            $this->buildLink(
                'SecondSite.php',
                array()
            );
        header('Location: ' . $urlNext);
        exit;
    }

    function getRunUrl($server_cuino)
    {
        $ret =
            $this->buildLink(
                'SecondSite.php',
                array(
                    'action' => 'run',
                    'customerItemID' => $server_cuino
                )
            );

        return $ret;
    }

    function getEditUrl($server_cuino)
    {
        $ret =
            $this->buildLink(
                'CustomerItem.php',
                array(
                    'action' => 'displayCI',
                    'customerItemID' => $server_cuino
                )
            );

        return $ret;
    }

    /*
    Report of second site validation falures for given customer/date range
    */
    function failureAnalysis()
    {
        global $cfg;

        $this->setMethodName('failureAnalysis');

        $dsSearchForm = new DSForm ($this);
        $dsResults = new DataSet ($this);

        $this->buSecondsite->initialiseSearchForm($dsSearchForm);

        $this->setTemplateFiles(array('SecondsiteFailureAnalysisReport' => 'SecondsiteFailureAnalysisReport.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                set_time_limit(240);

                if ($results = $this->buSecondsite->getResults($dsSearchForm)) {

                    if ($_REQUEST['Search'] == 'Generate CSV') {

                        $template = new Template ($cfg["path_templates"], "remove");

                        $template->set_file('page', 'SecondsiteFailureAnalysisReport.inc.csv');

                        $template->set_block('page', 'rowsBlock', 'rows');

                        foreach ($results as $row) {
                            $template->set_var(
                                array(
                                    'customerName' => $row['customerName'],
                                    'serverName' => $row['serverName'],
                                    'period' => $row['period'],
                                    'errors' => $row['errors']
                                )
                            );
                            $template->parse('rows', 'rowsBlock', true);
                        }
                        $template->parse('output', 'page', true);

                        $output = $template->get_var('output');

                        Header('Content-type: text/plain');
                        Header('Content-Disposition: attachment; filename=SecondsiteFailureAnalysisReport.csv');
                        echo $output;
                        exit;
                    } else { // Screen Report

                        $this->template->set_block('SecondsiteFailureAnalysisReport', 'rowsBlock', 'rows');

                        if (isset($_REQUEST['orderBy'])) {
                            foreach ($results as $key => $row) {
                                $customerName[$key] = $row['customerName'];
                                $serverName[$key] = $row['serverName'];
                                $period[$key] = $row['period'];
                                $errors[$key] = $row['errors'];
                            }

                            if ($_SESSION['secondsiteSortDirection'] == SORT_DESC) {
                                $_SESSION['secondsiteSortDirection'] = SORT_ASC;
                            } else {
                                $_SESSION['secondsiteSortDirection'] = SORT_DESC;

                            }

                            array_multisort($$_REQUEST['orderBy'], $_SESSION['secondsiteSortDirection'], $results);
                        }
                        foreach ($results as $key => $row) {

                            $reportUrl =
                                $this->buildLink(
                                    'SecondSite.php',
                                    array(
                                        'action' => 'failureAnalysis',
                                        'searchForm[1][customerID]' => $_REQUEST ['searchForm'][1]['customerID'],
                                        'searchForm[1][startYearMonth]' => $_REQUEST ['searchForm'][1]['startYearMonth'],
                                        'searchForm[1][endYearMonth]' => $_REQUEST ['searchForm'][1]['endYearMonth'],
                                    )
                                );

                            $this->template->set_var(
                                array(
                                    'customerName' => $row['customerName'],
                                    'serverName' => $row['serverName'],
                                    'period' => $row['period'],
                                    'errors' => $row['errors'],
                                    'reportUrl' => $reportUrl
                                )
                            );
                            $this->template->parse('rows', 'rowsBlock', true);
                        }

                    }

                }// if searchForm

            }

        }
        $urlCustomerPopup = $this->buildLink(CTCNC_PAGE_CUSTOMER, array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP));

        $urlSubmit = $this->buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => 'failureAnalysis')
        );

        $this->setPageTitle('Second Site Failure Analysis Report');

        if ($dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue('name');
        }

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'customerID' => $dsSearchForm->getValue('customerID'),
                'customerString' => $customerString,
                'startYearMonth' => $dsSearchForm->getValue('startYearMonth'),
                'endYearMonth' => $dsSearchForm->getValue('endYearMonth'),
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit' => $urlSubmit,
            )
        );

        $this->template->parse('CONTENTS', 'SecondsiteFailureAnalysisReport', true);
        $this->parsePage();

    }
}// end of class
?>