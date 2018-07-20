<?php
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_dbe"] . "/DBESecondsiteImage.inc.php");
require_once($cfg["path_bu"] . "/BUActivity.inc.php");
require_once($cfg["path_bu"] . '/BUSecondsite.inc.php');

class BUSecondsiteReplication extends BUSecondsite
{
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeSecondsiteImage = new DBESecondsiteImage($this);
    }

    /**
     * Run validation process for all or just one server
     *
     * If $customerItemID passed (one server), then do not send error emails,
     * raise error SRs or skip suspended server.
     *
     * @param mixed $customerItemID
     * @param bool $testRun
     */
    function validateBackups($customerItemID = false,
                             $testRun = false
    )
    {

        $defaultTimeToLookFrom = strtotime('yesterday ' . self::START_IMAGE_TIME);

        $this->imageCount = 0;
        $this->serverCount = 0;
        $this->log = array();

        $servers = $this->getServers($customerItemID);

        $this->serverCount = count($servers);

        foreach ($servers as $server) {

            $error = false;
            $networkPath = false;
            $excludeFromChecks = false;

            $isSuspended = $this->isSuspended($server);

            if ($isSuspended) {
                $this->suspendedServerCount++;
            }

            if (
                $server['itm_itemtypeno'] == CONFIG_2NDSITE_LOCAL_ITEMTYPEID &&
                $server['secondsiteLocalExcludeFlag'] == 'Y'
            ) {
                $this->excludedLocalServers[] = $server;

                $excludeFromChecks = true;
            } else {

                if (!$isSuspended && $server['secondsiteValidationSuspendUntilDate']) {
                    $this->resetSuspendedUntilDate($server['server_cuino']);
                }


                $days = @$server['secondsiteImageDelayDays'];
                $dsHeader = new DataSet($this);
                $buHeader = new BUHeader($this);
                $buHeader->getHeader($dsHeader);

                $additionalDays = $dsHeader->getValue(DBEHeader::secondSiteReplicationAdditionalDelayAllowance);

                $days += $additionalDays;
                $timeToLookFrom = strtotime(
                    '-' . $days . ' days',
                    $defaultTimeToLookFrom
                );
                $this->delayedCheckServers[] = $server;

                $images = $this->getImagesByServer($server['server_cuino']);

                if (
                    !$server['secondSiteReplicationPath'] OR
                    count($images) == 0
                ) {
                    $error = 'Incomplete 2nd Site replication contract information';
                    if (!$isSuspended) {
                        $this->imageCount += count($images);
                        $this->serverErrorCount++;

                        $this->logMessage(
                            $server['cus_name'] . ' ' . $server['serverName'] . ' ' . $error,
                            self::LOG_TYPE_ERROR_INCOMPLETE
                        );

                        $this->setImageStatusByServer(
                            $server['server_cuino'],
                            self::STATUS_BAD_CONFIG
                        );
                    }

                } else {

                    $networkPath = $server['secondSiteReplicationPath'];
                    if (!file_exists($networkPath)) {
                        $error = 'Location is not available';

                        if (!$isSuspended) {
                            $images = $this->getImagesByServer($server['server_cuino']);
                            $this->imageCount += count($images);
                            $this->serverErrorCount++;

                            $this->logMessage(
                                $server['cus_name'] . ' ' . $networkPath . ' ' . $error,
                                self::LOG_TYPE_ERROR_PATH_MISSING
                            );

                            $this->setImageStatusByServer(
                                $server['server_cuino'],
                                self::STATUS_SERVER_NOT_FOUND
                            );
                        }
                    }
                }
            }

//            if ($error && !$customerItemID && !$isSuspended && !$testRun) {
//                $this->sendBadConfigurationEmail(
//                    $server,
//                    $error,
//                    $networkPath
//                );
//
//            }

            if (!$error && !$excludeFromChecks) {

                $missingImages = array();
                $missingLetters = array();

                $allServerImagesPassed = true;      // default assumption

                foreach ($images as $image) {

                    if (!$isSuspended) {
                        $this->imageCount++;
                    }
                    if (strlen($image['imageName']) == 1) {

                        $pattern = '/' . $server['serverName'] . '_' . $image['imageName'];
                    } else {
                        $pattern = '/' . $image['imageName'];
                    }

                    $pattern .= '.*(-cd.spi|spf)$/i';

                    $matchedFiles = self::preg_ls(
                        $networkPath,
                        $pattern
                    );

                    if (count($matchedFiles) == 0) {

                        $allServerImagesPassed = false;

                        if (!$isSuspended) {
                            $this->imageErrorCount++;
                            /*
                            No matching files of any date
                            */
                            $missingImages[] = 'No file in ' . $networkPath . ' matches pattern: ' . $pattern;
                            $missingLetters[] = $image['imageName'];

                            $errorMessage = $server['cus_name'] . ' ' . $server['serverName'] . ': No file in ' . $networkPath . ' matches pattern: ' . $pattern;

                            $this->logMessage(
                                $errorMessage,
                                self::LOG_TYPE_ERROR_NO_IMAGE
                            );

                            $this->setImageStatus(
                                $image['secondSiteImageID'],
                                self::STATUS_IMAGE_NOT_FOUND
                            );

                            echo $pattern . " NOT FOUND<br/>";
                        }
                    } else {
                        /*
                        Got some matched patterns. Ensure one is up-to-date
                        */
                        $currentFileFound = false;

                        $mostRecentFileName = false;

                        $mostRecentFileTime = 0;

                        foreach ($matchedFiles as $file) {

                            $fileModifyTime = filemtime($file);

                            if ($fileModifyTime > $mostRecentFileTime) {
                                $mostRecentFileTime = $fileModifyTime;
                                $mostRecentFileName = $file;
                            }

                            if ($fileModifyTime >= $timeToLookFrom) {
                                $currentFileFound = true;
                                break;      // got it
                            }
                        }

                        if (!$currentFileFound) {

                            $allServerImagesPassed = false;

                            if (!$isSuspended) {
                                $this->imageErrorCount++;

                                $errorMessage = $server['cus_name'] . ' ' . $server['serverName'] . ': Image is OUT-OF-DATE: ' . $mostRecentFileName . ' ' . DATE(
                                        'd/m/Y H:i:s',
                                        $mostRecentFileTime
                                    );
                                $this->logMessage(
                                    $errorMessage,
                                    self::LOG_TYPE_ERROR_NO_IMAGE
                                );

                                $missingImages[] = 'OUT-OF-DATE image found: ' . $mostRecentFileName . ' ' . DATE(
                                        'd/m/Y H:i:s',
                                        $mostRecentFileTime
                                    );
                                $missingLetters[] = $driveLetter;

                                $status = self::STATUS_OUT_OF_DATE;
                            } else {
                                $status = self::STATUS_SUSPENDED;

                            }

                            $this->setImageStatus(
                                $image['secondSiteImageID'],
                                $status,
                                $mostRecentFileName,
                                date(
                                    'Y-m-d H:i:s',
                                    $mostRecentFileTime
                                )
                            );

                        } else {
                            if (!$isSuspended) {
                                $this->imagePassesCount++;
                            }
                            /*
                            Passed all verification checks.
                            */
                            $this->logMessage(
                                $server['cus_name'] . ' ' . $server['serverName'] . ' Up-to-date image ' . $mostRecentFileName . ' ' . DATE(
                                    'd/m/Y H:i:s',
                                    $mostRecentFileTime
                                ),
                                self::LOG_TYPE_SUCCESS
                            );

                            $status = self::STATUS_PASSED;

                            $this->setImageStatus(
                                $image['secondSiteImageID'],
                                $status,
                                $mostRecentFileName,
                                date(
                                    'Y-m-d H:i:s',
                                    $mostRecentFileTime
                                )
                            );
                            /*
                            Note: If this server is suspended then it's status will now be set back to passed
                            and the suspended date reset.
                            */
                        }

                    }

                }// end drives

                if ($allServerImagesPassed) {
                    $this->resetSuspendedUntilDate($server['server_cuino']);
                }

//                if (!$isSuspended && count($missingImages) > 0 && !$customerItemID && !$testRun) {
//
//                    $buActivity = $this->getActivityModel()->raiseSecondSiteMissingImageRequest(
//                        $server['custno'],
//                        $server['serverName'],
//                        $server['server_cuino'],
//                        $server['cui_cuino'],
//                        $missingLetters,
//                        $missingImages
//                    );
//
//                }
            } // if not error

        } // end foreach contracts
    }

    function sendBadConfigurationEmail($server,
                                       $errorMessage,
                                       $networkPath = false
    )
    {

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'secondsiteBadConfigurationEmail.inc.html'
        );

        $template->setVar(
            array(
                'customerName' => $server['cus_name'],
                'cuino'        => $server['server_cuino'],
                'serverName'   => $server['serverName'],
                'errorMessage' => addslashes($errorMessage),
                'networkPath'  => addslashes($networkPath)
            )
        );
        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $subject = '2nd Site configuration warning - ' . $server['cus_name'] . ' - ' . $server['serverName'];

        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $senderName = 'CNC Support Department';

        $toEmail = '2sbadconfig@' . CONFIG_PUBLIC_DOMAIN;


        $hdrs = array(
            'To'           => $toEmail,
            'From'         => $senderEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail = new BUMail($this);

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body,
            true
        );

    }


    function setImageStatus($secondSiteImageID,
                            $status,
                            $imagePath = '',
                            $imageTime = null
    )
    {
        $queryString =
            "UPDATE
        secondsite_image 
      SET
        replicationStatus = '$status',
        replicationImagePath = '" . addslashes($imagePath) . "',
        replicationImageTime = '$imageTime'
      WHERE
        secondSiteImageID = $secondSiteImageID";

        $db = $GLOBALS['db'];

        $db->query($queryString);
    }

    function setImageStatusByServer($customerItemID,
                                    $status
    )
    {
        $queryString =
            "UPDATE
        secondsite_image 
      SET
        replicationStatus = '$status'
      WHERE
        customerItemID = $customerItemID";

        $db = $GLOBALS['db'];

        $db->query($queryString);
    }

    /*
    Get second site images by server
    */
    public function getImagesByServer($customerItemID)
    {
        $queryString =
            "SELECT
        secondSiteImageID,
        imageName,
        replicationStatus
      FROM
        secondsite_image
      WHERE
        customerItemID = $customerItemID";

        $db = $GLOBALS['db'];

        $db->query($queryString);

        $images = array();
        while ($db->next_record()) {
            $images[] = $db->Record;
        }

        return $images;
    }

    /*
    Get second site images by status
    */
    public function getServers($customerItemID = false)
    {
        $queryString =
            "SELECT
        ci.cui_cuino,
        ci.cui_custno AS custno,
        c.cus_name,
        i.itm_itemtypeno,
        ser.cui_cuino AS server_cuino,
        ser.cui_cust_ref AS serverName,
        ser.secondSiteReplicationPath,
        ser.secondsiteValidationSuspendUntilDate,
        ser.secondsiteImageDelayDays,
        ser.secondsiteLocalExcludeFlag,
        delayuser.cns_name AS delayUser,
        ser.secondsiteImageDelayDate,
        suspenduser.cns_name AS suspendUser,
        ser.secondsiteSuspendedDate
      FROM
        custitem ci
        JOIN customer c ON c.cus_custno = ci.cui_custno
        JOIN custitem_contract ON custitem_contract.`cic_contractcuino` = ci.cui_cuino
        JOIN custitem ser ON ser.cui_cuino = custitem_contract.cic_cuino
        JOIN item i ON i.itm_itemno = ci.cui_itemno
        LEFT JOIN consultant delayuser ON delayuser.cns_consno = ser.secondsiteImageDelayUserID
        LEFT JOIN consultant suspenduser ON suspenduser.cns_consno = ser.secondsiteSuspendedByUserID

      WHERE
        i.itm_itemtypeno IN ( " . CONFIG_2NDSITE_CNC_ITEMTYPEID . "," . CONFIG_2NDSITE_LOCAL_ITEMTYPEID . ")
        AND ci.declinedFlag <> 'Y'";

        if ($customerItemID) {
            $queryString .= " AND ser.cui_cuino = $customerItemID";
        }

        $queryString .= " ORDER BY c.cus_name, serverName";

        $db = $GLOBALS['db'];

        $db->query($queryString);

        $servers = array();
        while ($db->next_record()) {
            $servers[] = $db->Record;
        }

        return $servers;
    }

    function updateSecondsiteImage(&$dsData)
    {
        $this->setMethodName('updateSecondsiteImage');
        $this->updateDataaccessObject(
            $dsData,
            $this->dbeSecondsiteImage
        );
        return TRUE;
    }

    function getSecondsiteImageByID($ID,
                                    &$dsResults
    )
    {
        $this->dbeSecondsiteImage->setPKValue($ID);
        $this->dbeSecondsiteImage->getRow();
        return ($this->getData(
            $this->dbeSecondsiteImage,
            $dsResults
        ));
    }

    function getSecondsiteImagesByCustomerItemID($customerItemID,
                                                 &$dsResults
    )
    {
        $this->dbeSecondsiteImage->setValue(
            'customerItemID',
            $customerItemID
        );
        $this->dbeSecondsiteImage->getRowsByColumn(
            'customerItemID',
            'imageName'
        );
        return ($this->getData(
            $this->dbeSecondsiteImage,
            $dsResults
        ));
    }


    function deleteSecondsiteImage($ID)
    {
        $this->setMethodName('deleteSecondsiteImage');
        return $this->dbeSecondsiteImage->deleteRow($ID);
    }

    function getImagesByStatus($status)
    {
        $queryString =
            "SELECT
        ci.cui_cuino,
        ci.cui_custno as custno,
        c.cus_name,
        i.itm_itemtypeno,
        ser.cui_cuino as server_cuino,
        ser.cui_cust_ref AS serverName,
        ser.secondsiteLocationPath,
        ser.secondsiteValidationSuspendUntilDate,
        ser.secondsiteImageDelayDays,
        ser.secondsiteLocalExcludeFlag,
        ser.secondSiteReplicationPath,
        ssi.secondsiteImageID,
        ssi.imageName,
        ssi.replicationStatus ,
        ssi.replicationImagePath ,
        ssi.replicationImageTime
      FROM
        custitem ci
        JOIN customer c ON c.cus_custno = ci.cui_custno
        JOIN custitem_contract ON custitem_contract.`cic_contractcuino` = ci.cui_cuino
        JOIN custitem ser ON ser.cui_cuino = custitem_contract.cic_cuino
        JOIN item i ON i.itm_itemno = ci.cui_itemno
        JOIN secondsite_image ssi ON ssi.customerItemID = ser.cui_cuino

      WHERE
        i.itm_itemtypeno IN ( " . CONFIG_2NDSITE_CNC_ITEMTYPEID . "," . CONFIG_2NDSITE_LOCAL_ITEMTYPEID . ")
        AND ci.declinedFlag <> 'Y'
        AND replicationStatus = '$status'
      
      ORDER BY c.cus_name, serverName, ssi.imageName";

        $db = $GLOBALS['db'];

        $db->query($queryString);

        $images = array();
        while ($db->next_record()) {
            $images[] = $db->Record;
        }

        return $images;

    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(
            'customerID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            'customerID',
            ''
        );
        $dsData->addColumn(
            'startYearMonth',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            'startYearMonth',
            ''
        );
        $dsData->addColumn(
            'endYearMonth',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            'endYearMonth',
            ''
        );
    }

    function getResults(&$searchForm)
    {
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $customerID = $searchForm->getValue('customerID');

        $startYearMonth = $searchForm->getValue('startYearMonth');
        $endYearMonth = $searchForm->getValue('endYearMonth');

        $sql =
            "SELECT 
          pro_custno,
          cus_name as customerName,
          custitem.cui_cust_ref as serverName,
          CONCAT( YEAR(caa_date), '-' , LPAD( MONTH(caa_date), 2, '0' ) ) as period,
          COUNT(*) AS `errors`
        FROM
          callactivity
          JOIN problem ON pro_problemno = caa_problemno
          JOIN customer ON cus_custno = pro_custno
          JOIN custitem ON caa_secondsite_error_cuino = custitem.cui_cuino
        WHERE
          caa_date BETWEEN '$startYearMonth-01' AND '$endYearMonth-31'
          AND caa_secondsite_error_cuino <> 0";

        if ($customerID) {
            $sql .= " AND pro_custno = $customerID";
        }

        $sql .=
            " GROUP BY
            pro_custno,
            caa_secondsite_error_cuino,
            YEAR(caa_date),
            MONTH(caa_date)";

        $results = $this->db->query($sql);

        $ret = array();
        while ($row = $results->fetch_array()) {
            $ret[] = $row;
        }

        return $ret;

    }

    function getPerformanceDataForYear($year = null)
    {

        if (!$year) {
            $year = date("Y");
        }

        $query = "SELECT SUM(passes)/ SUM(images) as successRate, MONTH FROM (
            SELECT MONTH(created_at) AS MONTH, images, passes FROM backup_performance_log WHERE YEAR(created_at) = '$year'
) t GROUP BY t.month";

        $result = $this->db->query($query);

        $data = [
        ];

        for ($i = 0; $i < 12; $i++) {
            $data[$i + 1] = "N/A";
        }

        while ($row = $result->fetch_assoc()) {
            $data[$row['MONTH']] = $row['successRate'] * 100;
        }

        return $data;
    }

    function getPerformanceDataAvailableYears()
    {
        $query = "SELECT  DISTINCT YEAR(created_at) AS YEAR  FROM    backup_performance_log";
        $result = $this->db->query($query);

        return array_map(
            function ($item) {
                return $item[0];
            },
            $result->fetch_all()
        );
    }


}//End of class
?>