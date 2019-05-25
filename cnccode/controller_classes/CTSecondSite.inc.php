<?php
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSite.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

class CTSecondSite extends CTCNC
{
    /** @var DSForm */
    public $dsSecondsiteImage;
    /** @var buSecondsite */
    public $buSecondsite;

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

        if (!$this->isUserSDManager()) {
            $roles = [
                "technical",
                "reports"
            ];
            if (!self::hasPermissions($roles)) {
                Header("Location: /NotAllowed.php");
                exit;
            }
        }
        $this->buSecondsite = new BUSecondsite($this);
        $this->dsSecondsiteImage = new DSForm($this);
        $this->dsSecondsiteImage->copyColumnsFrom($this->buSecondsite->dbeSecondsiteImage);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
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
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsSecondsiteImage = &$this->dsSecondsiteImage; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == 'edit') {
                $this->buSecondsite->getSecondsiteImageByID(
                    $this->getParam('secondsiteImageID'),
                    $dsSecondsiteImage
                );
                $secondsiteImageID = $this->getParam('secondsiteImageID');
            } else {                                                                    // creating new
                $dsSecondsiteImage->initialise();
                $dsSecondsiteImage->setValue(
                    DBESecondSiteImage::secondsiteImageID,
                    '0'
                );
                $dsSecondsiteImage->setValue(
                    DBESecondSiteImage::customerItemID,
                    $this->getParam('customerItemID')
                );
                $secondsiteImageID = '0';
            }
        } else {                                                                        // form validation error
            $dsSecondsiteImage->initialise();
            $dsSecondsiteImage->fetchNext();
            $secondsiteImageID = $dsSecondsiteImage->getValue(DBESecondSiteImage::secondsiteImageID);
        }

        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'            => 'update',
                    'secondsiteImageID' => $secondsiteImageID
                )
            );
        $urlDisplayCustomerItem =
            Controller::buildLink(
                'CustomerItem.php',
                array(
                    'customerItemID' => $this->dsSecondsiteImage->getValue(DBESecondSiteImage::customerItemID),
                    'action'         => 'displayCI'
                )
            );
        $this->setPageTitle('Edit Secondsite Image');

        $this->setTemplateFiles(
            array('SecondsiteImageEdit' => 'SecondsiteImageEdit.inc')
        );

        $this->template->set_var(
            array(
                'customerItemID' => $dsSecondsiteImage->getValue(DBESecondSiteImage::customerItemID),

                'secondsiteImageID' => $secondsiteImageID,

                'imageName' => Controller::htmlInputText($dsSecondsiteImage->getValue(DBESecondSiteImage::imageName)),

                'imageNameMessage' => Controller::htmlDisplayText(
                    $dsSecondsiteImage->getMessage(DBESecondSiteImage::imageName)
                ),

                'status' => $dsSecondsiteImage->getValue(DBESecondSiteImage::status),

                'imagePath' => $dsSecondsiteImage->getValue(DBESecondSiteImage::imagePath),

                'imageTime' => $dsSecondsiteImage->getValue(DBESecondSiteImage::imageTime),

                'urlUpdate' => $urlUpdate,

                'urlDisplayCustomerItem' => $urlDisplayCustomerItem
            )
        );
        $this->template->parse(
            'CONTENTS',
            'SecondsiteImageEdit',
            true
        );
        $this->parsePage();
    }

    /**
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsSecondsiteImage->populateFromArray($this->getParam('secondsiteImage')));

        if ($this->formError) {
            if ($this->dsSecondsiteImage->getValue(DBESecondSiteImage::secondsiteImageID)) {
                $this->setAction(CTPROJECT_ACT_EDIT);
            } else {
                $this->setAction(CTPROJECT_ACT_ACT);
            }
            $this->edit();
            exit;
        }

        $this->buSecondsite->updateSecondsiteImage($this->dsSecondsiteImage);

        $urlNext =
            Controller::buildLink(
                'CustomerItem.php',
                array(
                    'customerItemID' => $this->dsSecondsiteImage->getValue(DBESecondSiteImage::customerItemID),
                    'action'         => 'displayCI'
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        $dsSecondsiteImage = new DataSet($this);
        $this->buSecondsite->getSecondsiteImageByID(
            $this->getParam('secondsiteImageID'),
            $dsSecondsiteImage
        );

        $this->buSecondsite->deleteSecondsiteImage($this->getParam('secondsiteImageID'));

        $urlNext =
            Controller::buildLink(
                'CustomerItem.php',
                array(
                    'action'         => 'displayCI',
                    'customerItemID' => $dsSecondsiteImage->getValue(DBESecondSiteImage::customerItemID)
                )
            );
        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * List all second site servers with status
     * @throws Exception
     */
    function listAll()
    {
        $selectedYear = @$this->getParam('searchYear');

        if (!$selectedYear) {
            $selectedYear = date('Y');
        }

        $this->setMethodName('list');

        $performanceData = $this->buSecondsite->getPerformanceDataForYear($selectedYear);

        $outOfDate = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_OUT_OF_DATE);

        $serverNotFound = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_SERVER_NOT_FOUND);

        $imageNotFound = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_IMAGE_NOT_FOUND);

        $suspended = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_SUSPENDED);

        $badConfig = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_BAD_CONFIG);

        $passed = $this->buSecondsite->getImagesByStatus(BUSecondsite::STATUS_PASSED);

        $this->setPageTitle('Offsite Backup Status');

        $this->setTemplateFiles(array('SecondsiteList' => 'SecondsiteList.inc'));

        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);

        $target = $dsHeader->getValue(DBEHeader::backupTargetSuccessRate);

        $this->template->set_var(
            [
                "backupTargetSuccessRate" => $target,
                "monthSuccessRate1Class"  => $performanceData[1] >= $target ? 'success' : 'fail',
                "monthSuccessRate1"       => $this->validateAndRound($performanceData[1]),
                "monthSuccessRate2Class"  => $performanceData[2] >= $target ? 'success' : 'fail',
                "monthSuccessRate2"       => $this->validateAndRound($performanceData[2]),
                "monthSuccessRate3Class"  => $performanceData[3] >= $target ? 'success' : 'fail',
                "monthSuccessRate3"       => $this->validateAndRound($performanceData[3]),
                "monthSuccessRate4Class"  => $performanceData[4] >= $target ? 'success' : 'fail',
                "monthSuccessRate4"       => $this->validateAndRound($performanceData[4]),
                "monthSuccessRate5Class"  => $performanceData[5] >= $target ? 'success' : 'fail',
                "monthSuccessRate5"       => $this->validateAndRound($performanceData[5]),
                "monthSuccessRate6Class"  => $performanceData[6] >= $target ? 'success' : 'fail',
                "monthSuccessRate6"       => $this->validateAndRound($performanceData[6]),
                "monthSuccessRate7Class"  => $performanceData[7] >= $target ? 'success' : 'fail',
                "monthSuccessRate7"       => $this->validateAndRound($performanceData[7]),
                "monthSuccessRate8Class"  => $performanceData[8] >= $target ? 'success' : 'fail',
                "monthSuccessRate8"       => $this->validateAndRound($performanceData[8]),
                "monthSuccessRate9Class"  => $performanceData[9] >= $target ? 'success' : 'fail',
                "monthSuccessRate9"       => $this->validateAndRound($performanceData[9]),
                "monthSuccessRate10Class" => $performanceData[10] >= $target ? 'success' : 'fail',
                "monthSuccessRate10"      => $this->validateAndRound($performanceData[10]),
                "monthSuccessRate11Class" => $performanceData[11] >= $target ? 'success' : 'fail',
                "monthSuccessRate11"      => $this->validateAndRound($performanceData[11]),
                "monthSuccessRate12Class" => $performanceData[12] >= $target ? 'success' : 'fail',
                "monthSuccessRate12"      => $this->validateAndRound($performanceData[12])
            ]
        );

        $this->template->setBlock(
            'SecondsiteList',
            'availableYearsBlock',
            'availableYears'
        );

        $years = $this->buSecondsite->getPerformanceDataAvailableYears();

        foreach ($years as $year) {
            $this->template->set_var(
                [
                    "year"         => $year,
                    "selectedYear" => $year == $selectedYear ? 'selected' : null
                ]
            );
            $this->template->parse(
                'availableYears',
                'availableYearsBlock',
                true
            );
        };


        $this->template->setBlock(
            'SecondsiteList',
            'outOfDateBlock',
            'outOfDate'
        );

        foreach ($outOfDate as $record) {

            $imageTime = strftime(
                "%d/%m/%Y %H:%M:%S",
                strtotime($record['imageTime'])
            );

            $imageAgeDays = number_format(
                (time() - strtotime($record['imageTime'])) / 86400,
                0
            );

            $this->template->set_var(

                array(
                    'customerName' => $record['cus_name'],
                    'serverName'   => $record['serverName'],
                    'serverPath'   => $record['secondsiteLocationPath'],
                    'imageName'    => $record['imageName'],
                    'imagePath'    => $record['imagePath'],
                    'imageTime'    => $imageTime,
                    'imageAgeDays' => $imageAgeDays,
                    'urlServer'    => $this->getEditUrl($record['server_cuino']),
                    'urlRunCheck'  => $this->getRunUrl($record['server_cuino'])
                )
            );

            $this->template->parse(
                'outOfDate',
                'outOfDateBlock',
                true
            );

        }

        $this->template->setBlock(
            'SecondsiteList',
            'serverNotFoundBlock',
            'serverNotFound'
        );

        foreach ($serverNotFound as $record) {

            $this->template->set_var(

                array(
                    'customerName' => $record['cus_name'],
                    'serverName'   => $record['serverName'],
                    'serverPath'   => $record['secondsiteLocationPath'],
                    'imageName'    => $record['imageName'],
                    'urlServer'    => $this->getEditUrl($record['server_cuino']),
                    'urlRunCheck'  => $this->getRunUrl($record['server_cuino'])
                )
            );

            $this->template->parse(
                'serverNotFound',
                'serverNotFoundBlock',
                true
            );

        }

        $this->template->setBlock(
            'SecondsiteList',
            'imageNotFoundBlock',
            'imageNotFound'
        );

        foreach ($imageNotFound as $record) {

            $this->template->set_var(

                array(
                    'customerName' => $record['cus_name'],
                    'serverName'   => $record['serverName'],
                    'serverPath'   => $record['secondsiteLocationPath'],
                    'imageName'    => $record['imageName'],
                    'urlServer'    => $this->getEditUrl($record['server_cuino']),
                    'urlRunCheck'  => $this->getRunUrl($record['server_cuino'])

                )
            );

            $this->template->parse(
                'imageNotFound',
                'imageNotFoundBlock',
                true
            );

        }

        $this->template->setBlock(
            'SecondsiteList',
            'badConfigBlock',
            'badConfig'
        );

        foreach ($badConfig as $record) {

            $this->template->set_var(

                array(
                    'customerName' => $record['cus_name'],
                    'serverName'   => $record['serverName'],
                    'serverPath'   => $record['secondsiteLocationPath'],
                    'imagePath'    => $record['imagePath'],
                    'imageName'    => $record['imageName'],
                    'urlServer'    => $this->getEditUrl($record['server_cuino']),
                    'urlRunCheck'  => $this->getRunUrl($record['server_cuino'])

                )
            );

            $this->template->parse(
                'badConfig',
                'badConfigBlock',
                true
            );

        }

        $this->template->setBlock(
            'SecondsiteList',
            'suspendedBlock',
            'suspended'
        );

        foreach ($suspended as $record) {

            $imageTime = 'No Image';
            $imageAgeDays = null;
            if ($record['imageTime']) {
                $imageTime = strftime(
                    "%d/%m/%Y %H:%M:%S",
                    strtotime($record['imageTime'])
                );

                $imageAgeDays = number_format(
                    (time() - strtotime($record['imageTime'])) / 86400,
                    0
                );
            }
            $suspendedUntil = 'No longer suspended';
            if ($record['secondsiteValidationSuspendUntilDate']) {
                $suspendedUntil = strftime(
                    "%d/%m/%Y",
                    strtotime($record['secondsiteValidationSuspendUntilDate'])
                );
            }
            $txtRunCheck = 'Check Now';

            $this->template->set_var(

                array(
                    'customerName'   => $record['cus_name'],
                    'serverName'     => $record['serverName'],
                    'serverPath'     => $record['secondsiteLocationPath'],
                    'imagePath'      => $record['imagePath'],
                    'imageName'      => $record['imageName'],
                    'suspendedUntil' => $suspendedUntil,
                    'imageTime'      => $imageTime,
                    'imageAgeDays'   => $imageAgeDays,
                    'urlServer'      => $this->getEditUrl($record['server_cuino']),
                    'urlRunCheck'    => $this->getRunUrl($record['server_cuino']),
                    'txtRunCheck'    => $txtRunCheck
                )
            );

            $this->template->parse(
                'suspended',
                'suspendedBlock',
                true
            );

        }

        $this->template->setBlock(
            'SecondsiteList',
            'passedBlock',
            'passed'
        );

        foreach ($passed as $record) {

            $imageTime = strftime(
                "%d/%m/%Y %H:%M:%S",
                strtotime($record['imageTime'])
            );

            $imageAgeDays = number_format(
                (time() - strtotime($record['imageTime'])) / 86400,
                0
            );

            $this->template->set_var(

                array(
                    'urlServer'    => $this->getEditUrl($record['server_cuino']),
                    'customerName' => $record['cus_name'],
                    'serverName'   => $record['serverName'],
                    'imageName'    => $record['imageName'],
                    'imagePath'    => $record['imagePath'],
                    'imageTime'    => $imageTime,
                    'imageAgeDays' => $imageAgeDays
                )
            );

            $this->template->parse(
                'passed',
                'passedBlock',
                true
            );
        }

        $this->template->parse(
            'CONTENTS',
            'SecondsiteList',
            true
        );
        $this->parsePage();
    }

    private function validateAndRound($value)
    {
        if (!is_numeric($value)) {
            return $value;
        }

        return round(
            $value,
            1
        );
    }

    /**
     * Run validation
     *
     * @throws Exception
     */
    function run()
    {
        $this->buSecondsite->validateBackups($this->getParam('customerItemID'));

        $urlNext =
            Controller::buildLink(
                'OffsiteBackupStatus.php',
                array()
            );
        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * @param $server_cuino
     * @return mixed|string
     * @throws Exception
     */
    function getRunUrl($server_cuino)
    {
        return Controller::buildLink(
            'OffsiteBackupStatus.php',
            array(
                'action'         => 'run',
                'customerItemID' => $server_cuino
            )
        );
    }

    /**
     * @param $server_cuino
     * @return mixed|string
     * @throws Exception
     */
    function getEditUrl($server_cuino)
    {
        return Controller::buildLink(
            'CustomerItem.php',
            array(
                'action'         => 'displayCI',
                'customerItemID' => $server_cuino
            )
        );
    }

    /*
    Report of second site validation failures for given customer/date range
    */
    /**
     * @throws Exception
     */
    function failureAnalysis()
    {
        global $cfg;

        if (!$this->isUserSDManager()) {
            $roles = [
                "reports"
            ];
            if (!self::hasPermissions($roles)) {
                Header("Location: /NotAllowed.php");
                exit;
            }
        }
        $this->setMethodName('failureAnalysis');

        $dsSearchForm = new DSForm ($this);

        $this->buSecondsite->initialiseSearchForm($dsSearchForm);

        $this->setTemplateFiles(array('SecondsiteFailureAnalysisReport' => 'SecondsiteFailureAnalysisReport.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                set_time_limit(240);

                if ($results = $this->buSecondsite->getResults($dsSearchForm)) {

                    if ($this->getParam('Search') == 'Generate CSV') {

                        $template = new Template (
                            $cfg["path_templates"],
                            "remove"
                        );

                        $template->set_file(
                            'page',
                            'SecondsiteFailureAnalysisReport.inc.csv'
                        );

                        $template->set_block(
                            'page',
                            'rowsBlock',
                            'rows'
                        );

                        foreach ($results as $row) {
                            $template->set_var(
                                array(
                                    'customerName' => $row['customerName'],
                                    'serverName'   => $row['serverName'],
                                    'period'       => $row['period'],
                                    'errors'       => $row['errors']
                                )
                            );
                            $template->parse(
                                'rows',
                                'rowsBlock',
                                true
                            );
                        }
                        $template->parse(
                            'output',
                            'page',
                            true
                        );

                        $output = $template->get_var('output');

                        Header('Content-type: text/plain');
                        Header('Content-Disposition: attachment; filename=SecondsiteFailureAnalysisReport.csv');
                        echo $output;
                        exit;
                    } else { // Screen Report

                        $this->template->set_block(
                            'SecondsiteFailureAnalysisReport',
                            'rowsBlock',
                            'rows'
                        );

                        if ($this->getParam('orderBy')) {
                            foreach ($results as $key => $row) {
                                $customerName[$key] = $row['customerName'];
                                $serverName[$key] = $row['serverName'];
                                $period[$key] = $row['period'];
                                $errors[$key] = $row['errors'];
                            }

                            if ($this->getSessionParam('secondsiteSortDirection') == SORT_DESC) {
                                $this->setSessionParam('secondsiteSortDirection', SORT_ASC);
                            } else {
                                $this->setSessionParam('secondsiteSortDirection', SORT_DESC);

                            }

                            array_multisort(
                                $$this->getParam('orderBy'),
                                $_SESSION['secondsiteSortDirection'],
                                $results
                            );
                        }
                        foreach ($results as $key => $row) {

                            $reportUrl =
                                Controller::buildLink(
                                    'OffsiteBackupStatus.php',
                                    array(
                                        'action'                        => 'failureAnalysis',
                                        'searchForm[1][customerID]'     => $_REQUEST ['searchForm'][1]['customerID'],
                                        'searchForm[1][startYearMonth]' => $_REQUEST ['searchForm'][1]['startYearMonth'],
                                        'searchForm[1][endYearMonth]'   => $_REQUEST ['searchForm'][1]['endYearMonth'],
                                    )
                                );

                            $this->template->set_var(
                                array(
                                    'customerName' => $row['customerName'],
                                    'serverName'   => $row['serverName'],
                                    'period'       => $row['period'],
                                    'errors'       => $row['errors'],
                                    'reportUrl'    => $reportUrl
                                )
                            );
                            $this->template->parse(
                                'rows',
                                'rowsBlock',
                                true
                            );
                        }

                    }

                }// if searchForm

            }

        }
        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP)
        );

        $urlSubmit = Controller::buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => 'failureAnalysis')
        );

        $this->setPageTitle('Second Site Failure Analysis Report');
        $customerString = null;
        if ($dsSearchForm->getValue(BUSecondSite::searchFormCustomerID) != 0) {
            $buCustomer = new BUCustomer ($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(BUSecondSite::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $this->template->set_var(
            array(
                'formError'        => $this->formError,
                'customerID'       => $dsSearchForm->getValue(BUSecondSite::searchFormCustomerID),
                'customerString'   => $customerString,
                'startYearMonth'   => $dsSearchForm->getValue(BUSecondSite::searchFormStartYearMonth),
                'endYearMonth'     => $dsSearchForm->getValue(BUSecondSite::searchFormEndYearMonth),
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit'        => $urlSubmit,
            )
        );

        $this->template->parse(
            'CONTENTS',
            'SecondsiteFailureAnalysisReport',
            true
        );
        $this->parsePage();

    }

    protected function getImageTime($time)
    {
        if (!$time) {
            return 'N/A';
        }
        return strftime(
            "%d/%m/%Y %H:%M:%S",
            strtotime($time)
        );
    }

    protected function getImageAge($time)
    {
        if (!$time) {
            return 'N/A';
        }
        return number_format(
            (time() - strtotime($time)) / 86400,
            0
        );
    }
}
