<?php
require_once($cfg['path_ct'] . '/CTSecondSite.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSiteReplication.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

class CTSecondSiteReplication extends CTSecondSite
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
        $this->buSecondsite = new BUSecondsiteReplication($this);
        $this->dsSecondsiteImage->copyColumnsFrom($this->buSecondsite->dbeSecondsiteImage);
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
                'customerItemID'         => $dsSecondsiteImage->getValue(DBESecondSiteImage::customerItemID),
                'secondsiteImageID'      => $secondsiteImageID,
                'imageName'              => Controller::htmlInputText(
                    $dsSecondsiteImage->getValue(DBESecondSiteImage::imageName)
                ),
                'imageNameMessage'       => Controller::htmlDisplayText(
                    $dsSecondsiteImage->getMessage(DBESecondSiteImage::imageName)
                ),
                'status'                 => $dsSecondsiteImage->getValue(DBESecondSiteImage::status),
                'imagePath'              => $dsSecondsiteImage->getValue(DBESecondSiteImage::imagePath),
                'imageTime'              => $dsSecondsiteImage->getValue(DBESecondSiteImage::imageTime),
                'replicationStatus'      => $dsSecondsiteImage->getValue(DBESecondSiteImage::replicationStatus),
                'replicationImagePath'   => $dsSecondsiteImage->getValue(DBESecondSiteImage::replicationImagePath),
                'replicationImageTime'   => $dsSecondsiteImage->getValue(DBESecondSiteImage::replicationImageTime),
                'urlUpdate'              => $urlUpdate,
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
     * List all second site servers with status
     * @throws Exception
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

        $this->setPageTitle('Offsite Backup Replication Status');

        $this->setTemplateFiles(array('SecondsiteList' => 'SecondsiteReplicationList.inc'));

        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $this->template->setBlock(
            'SecondsiteList',
            'outOfDateBlock',
            'outOfDate'
        );

        foreach ($outOfDate as $record) {

            $imageTime = $record['replicationImageTime'] ? strftime(
                "%d/%m/%Y %H:%M:%S",
                strtotime($record['replicationImageTime'])
            ) : 'N/A';

            $imageAgeDays = $record['replicationImageTime'] ? number_format(
                (time() - strtotime($record['replicationImageTime'])) / 86400,
                0
            ) : 'N/A';

            $this->template->set_var(


                array(
                    'customerName' => $record['cus_name'],
                    'serverName'   => $record['serverName'],
                    'serverPath'   => $record['secondSiteReplicationPath'],
                    'imageName'    => $record['imageName'],
                    'imagePath'    => $record['replicationImagePath'],
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
                    'serverPath'   => $record['secondSiteReplicationPath'],
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
                    'serverPath'   => $record['secondSiteReplicationPath'],
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
                    'serverPath'   => $record['secondSiteReplicationPath'],
                    'imagePath'    => $record['replicationImagePath'],
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

            if ($record['replicationImageTime']) {
                $imageTime = strftime(
                    "%d/%m/%Y %H:%M:%S",
                    strtotime($record['replicationImageTime'])
                );

                $imageAgeDays = number_format(
                    (time() - strtotime($record['replicationImageTime'])) / 86400,
                    0
                );
            } else {
                $imageTime = 'No Image';
                $imageAgeDays = null;
            }
            if ($record['secondsiteValidationSuspendUntilDate']) {
                $suspendedUntil = strftime(
                    "%d/%m/%Y",
                    strtotime($record['secondsiteValidationSuspendUntilDate'])
                );
            } else {
                $suspendedUntil = 'No longer suspended';
            }
            $txtRunCheck = 'Check Now';

            $this->template->set_var(

                array(
                    'customerName'   => $record['cus_name'],
                    'serverName'     => $record['serverName'],
                    'serverPath'     => $record['secondSiteReplicationPath'],
                    'imagePath'      => $record['replicationImagePath'],
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

            $imageTime = $this->getImageTime($record['replicationImageTime']);

            $imageAgeDays = $this->getImageAge($record['replicationImageTime']);

            $this->template->set_var(

                array(
                    'urlServer'    => $this->getEditUrl($record['server_cuino']),
                    'customerName' => $record['cus_name'],
                    'serverName'   => $record['serverName'],
                    'imageName'    => $record['imageName'],
                    'imagePath'    => $record['replicationImagePath'],
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
                'OffsiteBackupReplicationStatus.php',
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
        $ret =
            Controller::buildLink(
                'OffsiteBackupReplicationStatus.php',
                array(
                    'action'         => 'run',
                    'customerItemID' => $server_cuino
                )
            );

        return $ret;
    }
}