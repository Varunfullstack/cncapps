<?php
global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_dbe"] . "/DBESecondsiteImage.inc.php");
require_once($cfg["path_bu"] . "/BUActivity.inc.php");

class BUSecondsite extends Business
{

    const searchFormCustomerID     = 'customerID';
    const searchFormStartYearMonth = 'startYearMonth';
    const searchFormEndYearMonth   = 'endYearMonth';
    const STATUS_PASSED            = 'PASSED';
    const STATUS_SERVER_NOT_FOUND  = 'SERVER_NOT_FOUND';
    const STATUS_IMAGE_NOT_FOUND   = 'IMAGE_NOT_FOUND';
    const STATUS_BAD_CONFIG        = 'BAD_CONFIG';
    const STATUS_OUT_OF_DATE       = 'OUT_OF_DATE';
    const STATUS_SUSPENDED         = 'SUSPENDED';
    const STATUS_EXCLUDED          = 'EXCLUDED';

    const LOG_TYPE_ERROR_PATH_MISSING = 0;
    const LOG_TYPE_ERROR_INCOMPLETE   = 1;
    const LOG_TYPE_ERROR_NO_IMAGE     = 2;
    const LOG_TYPE_SUCCESS            = 3;
    const LOG_TYPE_SUSPENDED          = 4;
    const START_IMAGE_TIME            = '19:00';

    /** @var DBESecondsiteImage */
    public $dbeSecondsiteImage;
    public $buActivity;
    public $log;
    public $serverCount          = 0;
    public $imageCount           = 0;
    public $suspendedServerCount = 0;
    public $serverErrorCount     = 0;
    public $imageErrorCount      = 0;
    public $imagePassesCount     = 0;
    public $delayedCheckServers  = [];
    public $excludedLocalServers = [];
    /** @var mysqli $db */
    public    $db;
    protected $suspendedCheckServers = [];

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeSecondsiteImage = new DBESecondsiteImage($this);
    }

    public function getDelayedCheckServers()
    {
        return $this->delayedCheckServers;
    }

    public function getSuspendedCheckServers()
    {
        return $this->suspendedCheckServers;
    }

    public function getExcludedLocalServers()
    {
        return $this->excludedLocalServers;
    }

    /**
     * Run validation process for all or just one server
     *
     * If $customerItemID passed (one server), then do not send error emails,
     * raise error SRs or skip suspended server.
     *
     * @param mixed $customerItemID
     * @param bool $testRun
     * @throws Exception
     */
    function validateBackups($customerItemID = false,
                             $testRun = false
    )
    {

        $defaultTimeToLookFrom = strtotime('yesterday ' . self::START_IMAGE_TIME);
        $this->imageCount      = 0;
        $this->serverCount     = 0;
        $this->log             = array();
        $servers               = $this->getServers($customerItemID);
        $this->serverCount     = count($servers);
        foreach ($servers as $server) {
            $error             = false;
            $networkPath       = null;
            $excludeFromChecks = false;
            $isSuspended       = $this->isSuspended($server);
            $images            = [];
            $timeToLookFrom    = null;
            if ($isSuspended) {
                $this->suspendedServerCount++;
            }
            if ($server[DBECustomerItem::secondsiteLocalExcludeFlag] == 'Y') {
                $this->excludedLocalServers[] = $server;
                $excludeFromChecks            = true;
            } else {

                if (!$isSuspended && $server['suspendedUntilDate']) {
                    $this->resetSuspendedUntilDate($server['server_cuino']);
                }
                if ($server['imageDelayDays']) {
                    $timeToLookFrom              = strtotime(
                        '-' . $server['imageDelayDays'] . ' days',
                        $defaultTimeToLookFrom
                    );
                    $this->delayedCheckServers[] = $server;
                } else {
                    $timeToLookFrom = $defaultTimeToLookFrom;
                }
                $images = $this->getImagesByServer($server['server_cuino']);
                if (!$server['secondsiteLocationPath'] or count($images) == 0) {
                    $error = 'Offsite Backup Path Error Or No Images';
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
                        if (!$customerItemID && !$testRun) {
                            $this->getActivityModel()->raiseSecondSiteLocationNotFoundRequest(
                                $server['custno'],
                                $server['serverName'],
                                $server['server_cuino'],
                                $server['cui_cuino'],
                                $networkPath
                            );
                        }
                    }

                } else {

                    $networkPath = $server['secondsiteLocationPath'];
                    if (!file_exists($networkPath)) {
                        $error = 'Location is not available';
                        if (!$isSuspended) {
                            $images           = $this->getImagesByServer($server['server_cuino']);
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
                            if (!$customerItemID && !$testRun) {
                                $this->getActivityModel()->raiseSecondSiteLocationNotFoundRequest(
                                    $server['custno'],
                                    $server['serverName'],
                                    $server['server_cuino'],
                                    $server['cui_cuino'],
                                    $networkPath
                                );
                            }
                        }
                    }
                }
            }
            if ($error && !$customerItemID && !$isSuspended && !$testRun) {
                $this->sendBadConfigurationEmail(
                    $server,
                    $error,
                    $networkPath
                );

            }
            if (!$error && !$excludeFromChecks) {

                $missingImages         = array();
                $missingLetters        = array();
                $allServerImagesPassed = true;      // default assumption
                $totalSize             = 0;
                foreach ($images as $image) {

                    $matchedFiles = $this->getImageFiles($image, $server, $networkPath);
                    $totalSize    += $this->getImageSize($matchedFiles);
                    if (!$isSuspended) {
                        $this->imageCount++;
                    }
                    if (count($matchedFiles) == 0) {

                        $allServerImagesPassed = false;
                        if (!$isSuspended) {
                            $this->imageErrorCount++;
                            /*
                            No matching files of any date
                            */
                            $missingImages[]  = "Could not match any files in {$networkPath}.";
                            $missingLetters[] = $image['imageName'];
                            $errorMessage     = "{$server['cus_name']} {$server['serverName']}: No file in {$networkPath} found.";
                            $this->logMessage(
                                $errorMessage,
                                self::LOG_TYPE_ERROR_NO_IMAGE
                            );
                            $this->setImageStatus(
                                $image['secondSiteImageID'],
                                self::STATUS_IMAGE_NOT_FOUND
                            );
                        }
                    } else {
                        $mostRecentFileName = false;
                        $mostRecentFileTime = 0;
                        foreach ($matchedFiles as $file) {
                            $fileModifyTime = filemtime($file);
                            if ($fileModifyTime > $mostRecentFileTime) {
                                $mostRecentFileTime = $fileModifyTime;
                                $mostRecentFileName = $file;
                            }
                        }
                        if ($mostRecentFileTime < $timeToLookFrom) {

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
                                $status          = self::STATUS_OUT_OF_DATE;
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
                            $imageAgeDays = number_format(
                                (time() - $mostRecentFileTime) / 86400,
                                0
                            );
                            if ($imageAgeDays < 0) {
                                $this->imageErrorCount++;
                                $errorMessage = $errorMessage = $server['cus_name'] . ' ' . $server['serverName'] . ': Image is OUT-OF-DATE: ' . $mostRecentFileName . ' ' . DATE(
                                        'd/m/Y H:i:s',
                                        $mostRecentFileTime
                                    );
                                $this->logMessage(
                                    $errorMessage,
                                    self::STATUS_OUT_OF_DATE
                                );
                                $status = self::STATUS_OUT_OF_DATE;
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

                    }

                }// end drives
                $this->recordServerSize($server['server_cuino'], $totalSize);
                if ($allServerImagesPassed) {
                    $this->resetSuspendedUntilDate($server['server_cuino']);
                }
                if (!$isSuspended && count($missingImages) > 0 && !$customerItemID && !$testRun) {

                    $this->getActivityModel()->raiseSecondSiteMissingImageRequest(
                        $server['custno'],
                        $server['serverName'],
                        $server['server_cuino'],
                        $server['cui_cuino'],
                        $missingLetters,
                        $missingImages
                    );

                }
            } // if not error
        } // end foreach contracts
        if (!$customerItemID && !$testRun) {
            /** @var dbSweetcode $db */
            $db = $GLOBALS['db'];
            //check if we have already stored information for today
            $query = "SELECT created_at FROM backup_performance_log WHERE created_at = date(now()) and not isReplication";
            $db->query($query);
            $db->next_record();
            $data = $db->Record;
            if ($data['created_at']) {
                return;
            }
            $buHeader = new BUHeader($this);
            $dsHeader = new DataSet($this);
            $buHeader->getHeader($dsHeader);
            $query = "INSERT INTO backup_performance_log (
                      created_at,
                      servers,
                      images,
                      server_errors,
                      image_errors,
                      suspended_servers,
                      passes,
                      success_rate,
                                    target
                    ) VALUES (now(), ?, ?, ?, ?, ?, ?, ?, ?)";
            $db->preparedQuery(
                $query,
                [
                    [
                        "type"  => "i",
                        "value" => $this->serverCount
                    ],
                    [
                        "type"  => "i",
                        "value" => $this->imageCount
                    ],
                    [
                        "type"  => "i",
                        "value" => $this->serverErrorCount,
                    ],
                    [
                        "type"  => "i",
                        "value" => $this->imageErrorCount,
                    ],
                    [
                        "type"  => "i",
                        "value" => $this->suspendedServerCount,
                    ],
                    [
                        "type"  => "i",
                        "value" => $this->imagePassesCount,
                    ],
                    [
                        "type"  => "d",
                        "value" => $this->imageCount ? ($this->imagePassesCount / $this->imageCount) * 100 : 0
                    ],
                    [
                        "type"  => "i",
                        "value" => $dsHeader->getValue(DBEHeader::backupTargetSuccessRate)
                    ],
                ]
            );


        }
    }

    public function getServers($customerItemID = false)
    {
        $secondsiteCNCItemTypeId   = CONFIG_2NDSITE_CNC_ITEMTYPEID;
        $secondsiteLocalItemTypeId = CONFIG_2NDSITE_LOCAL_ITEMTYPEID;
        $queryString               = "SELECT
        ci.cui_cuino,
        ci.cui_custno AS custno,
        c.cus_name,
        i.itm_itemtypeno,
        ser.cui_cuino AS server_cuino,
        ser.cui_cust_ref AS serverName,
        ser.secondsiteLocationPath,
        ser.secondsiteValidationSuspendUntilDate as suspendedUntilDate,
        ser.secondsiteImageDelayDays as imageDelayDays,
        ser.secondsiteLocalExcludeFlag,
        delayuser.cns_name AS delayUser,
        ser.secondsiteImageDelayDate as imageDelayDate,
        suspenduser.cns_name AS suspendUser,
        ser.secondsiteSuspendedDate as suspendedDate

      FROM
        custitem ci
        JOIN customer c ON c.cus_custno = ci.cui_custno
        JOIN custitem_contract ON custitem_contract.`cic_contractcuino` = ci.cui_cuino
        JOIN custitem ser ON ser.cui_cuino = custitem_contract.cic_cuino
        JOIN item i ON i.itm_itemno = ci.cui_itemno
        LEFT JOIN consultant delayuser ON delayuser.cns_consno = ser.secondsiteImageDelayUserID
        LEFT JOIN consultant suspenduser ON suspenduser.cns_consno = ser.secondsiteSuspendedByUserID
      WHERE
        i.itm_itemtypeno IN (    $secondsiteCNCItemTypeId , $secondsiteLocalItemTypeId )
        AND ci.declinedFlag <> 'Y'";
        if ($customerItemID) {
            $queryString .= " AND ser.cui_cuino = $customerItemID";
        }
        $queryString .= " ORDER BY c.cus_name, serverName";
        $db          = $GLOBALS['db'];
        $db->query($queryString);
        $servers = array();
        while ($db->next_record()) {
            $servers[] = $db->Record;
        }
        return $servers;
    }

    function isSuspended($server)
    {

        if (!($server['suspendedUntilDate']) || $server['suspendedUntilDate'] <= date(
                'Y-m-d'
            )) {
            return false;
        }
        $message = 'Image validation suspended until ' . $server['suspendedUntilDate'];
        $this->logMessage(
            $server['cus_name'] . ' ' . $server['serverName'] . ' ' . $message,
            self::LOG_TYPE_SUSPENDED
        );
        $this->suspendedCheckServers[] = $server;
        $this->setImageStatusByServer(
            $server['server_cuino'],
            self::STATUS_SUSPENDED
        );
        return true;
    }

    function logMessage($message,
                        $type = self::LOG_TYPE_SUCCESS
    )
    {
        $this->log[] = array('type' => $type, 'message' => $message);
    }

    function setImageStatusByServer($customerItemID,
                                    $status
    )
    {
        $queryString = "UPDATE
        secondsite_image 
      SET
        status = '$status'
      WHERE
        customerItemID = $customerItemID";
        $db          = $GLOBALS['db'];
        $db->query($queryString);
    }

    function resetSuspendedUntilDate($cuino)
    {
        $queryString = "UPDATE
    custitem 
    SET
    secondsiteValidationSuspendUntilDate = NULL,
        secondsiteSuspendedByUserID = null,
        secondsiteSuspendedDate = null
    WHERE
    cui_cuino = $cuino";
        $db          = $GLOBALS['db'];
        $db->query($queryString);

    }

    public function getImagesByServer($customerItemID)
    {
        $queryString = "SELECT
        secondSiteImageID,
        imageName,
        status
      FROM
        secondsite_image

      WHERE
        customerItemID = $customerItemID";
        $db          = $GLOBALS['db'];
        $db->query($queryString);
        $images = array();
        while ($db->next_record()) {
            $images[] = $db->Record;
        }
        return $images;
    }

    function getActivityModel()
    {
        if (!$this->buActivity) {
            $this->buActivity = new BUActivity($this);
        }
        return $this->buActivity;
    }

    function sendBadConfigurationEmail($server,
                                       $errorMessage,
                                       $networkPath = false
    )
    {

        $template = new Template(
            EMAIL_TEMPLATE_DIR, "remove"
        );
        $template->set_file(
            'page',
            'secondsiteBadConfigurationEmail.inc.html'
        );
        $template->setVar(
            array(
                'customerName'    => $server['cus_name'],
                'cuino'           => $server['server_cuino'],
                'serverName'      => $server['serverName'],
                'errorMessage'    => addslashes($errorMessage),
                'networkPath'     => addslashes($networkPath),
                'customerItemURL' => SITE_URL . "/CustomerItem.php?action=displayCI&customerItemID=" . $server['server_cuino']
            )
        );
        $template->parse(
            'output',
            'page',
            true
        );
        $body        = $template->get_var('output');
        $subject     = 'Offsite Site configuration warning - ' . $server['cus_name'] . ' - ' . $server['serverName'];
        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $toEmail     = '2sbadconfig@' . CONFIG_PUBLIC_DOMAIN;
        $hdrs        = array(
            'To'           => $toEmail,
            'From'         => $senderEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        $buMail      = new BUMail($this);
        $buMail->mime->setHTMLBody($body);
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body        = $buMail->mime->get($mime_params);
        $hdrs        = $buMail->mime->headers($hdrs);
        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

    /*
    Get second site images by server
    */
    function preg_ls($path = ".",
                     $pat = "/.*/"
    )
    {
        // it's going to be used repeatedly, ensure we compile it for speed.
        $pat = preg_replace(
            "|(/.*/[^S]*)|s",
            "\\1S",
            $pat
        );
        //Remove trailing slashes from path
        while (substr(
                $path,
                -1,
                1
            ) == "/") $path = substr(
            $path,
            0,
            -1
        );
        //also, make sure that $path is a directory and repair any screw ups
        if (!is_dir($path)) $path = dirname($path);
        //assert either truthy or falsey of $rec, allow no scalars to mean truth
        //get a directory handle
        //initialise the output array
        $ret = array();
        if ($d = dir($path)) {
            //loop, reading until there's no more to read
            while (false !== ($e = $d->read())) {
                //Ignore parent- and self-links
                if (($e == ".") || ($e == "..")) {
                    continue;
                }
                //If it matches, include it
                if (preg_match(
                    $pat,
                    $e
                )) {
                    $ret[] = $path . "/" . $e;
                }
            }
        }
        //finally, return the array
        return $ret;
    }

    /*
    Get second site images by status
    */
    function setImageStatus($secondSiteImageID,
                            $status,
                            $imagePath = null,
                            $imageTime = null
    )
    {
        $queryString = "UPDATE
        secondsite_image 
      SET
        status = ?,
        imagePath = ?,
        imageTime = ?
      WHERE
        secondSiteImageID = ?";
        /** @var dbSweetcode $db */
        $db = $GLOBALS['db'];
        $db->preparedQuery(
            $queryString,
            [
                [
                    'type'  => "s",
                    'value' => $status
                ],
                [
                    'type'  => "s",
                    'value' => $imagePath
                ],
                [
                    'type'  => "s",
                    'value' => $imageTime
                ],
                [
                    'type'  => "i",
                    'value' => $secondSiteImageID
                ],
            ]
        );
    }

    function updateSecondsiteImage(&$dsData)
    {
        $this->setMethodName('updateSecondsiteImage');
        $this->updateDataAccessObject(
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
            DBESecondSiteImage::customerItemID,
            $customerItemID
        );
        $this->dbeSecondsiteImage->getRowsByColumn(
            DBESecondSiteImage::customerItemID,
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


        $queryString = "SELECT
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
        ssi.secondsiteImageID,
        ssi.imageName,
        ssi.status,
        ssi.imagePath,
        ssi.imageTime
      FROM
        custitem ci
        JOIN customer c ON c.cus_custno = ci.cui_custno
        JOIN custitem_contract ON custitem_contract.`cic_contractcuino` = ci.cui_cuino
        JOIN custitem ser ON ser.cui_cuino = custitem_contract.cic_cuino
        JOIN item i ON i.itm_itemno = ci.cui_itemno
        JOIN secondsite_image ssi ON ssi.customerItemID = ser.cui_cuino

      WHERE
        i.itm_itemtypeno IN ( " . CONFIG_2NDSITE_CNC_ITEMTYPEID . "," . CONFIG_2NDSITE_LOCAL_ITEMTYPEID . ")  AND ci.declinedFlag <> 'Y' ";
        if ($status == self::STATUS_EXCLUDED) {
            $queryString .= " AND ser.secondsiteLocalExcludeFlag = 'Y' group by serverName ";
        } else {
            $queryString .= " AND status = '$status' and ser.secondsiteLocalExcludeFlag <> 'Y' ";
        }
        $queryString .= "ORDER BY c.cus_name, serverName, ssi.imageName";
        $db          = $GLOBALS['db'];
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
            self::searchFormCustomerID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            self::searchFormCustomerID,
            null
        );
        $dsData->addColumn(
            self::searchFormStartYearMonth,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            self::searchFormStartYearMonth,
            null
        );
        $dsData->addColumn(
            self::searchFormEndYearMonth,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            self::searchFormEndYearMonth,
            null
        );
    }

    /**
     * @param DSForm $searchForm
     * @return array
     */
    function getResults(&$searchForm)
    {
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $customerID     = $searchForm->getValue(self::searchFormCustomerID);
        $startYearMonth = $searchForm->getValue(self::searchFormStartYearMonth);
        $endYearMonth   = $searchForm->getValue(self::searchFormEndYearMonth);
        $start          = DateTime::createFromFormat('m/Y', $startYearMonth)->modify('first day of this month');
        $dateCondition  = " > '{$start->format(DATE_MYSQL_DATE)}'";
        if ($endYearMonth) {
            $end           = DateTime::createFromFormat('m/Y', $endYearMonth)->modify('last day of this month');
            $dateCondition = "BETWEEN '{$start->format(DATE_MYSQL_DATE)}' AND '{$end->format(DATE_MYSQL_DATE)}'";
        }
        $sql = "SELECT 
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
          caa_date $dateCondition 
          AND caa_secondsite_error_cuino <> 0";
        if ($customerID) {
            $sql .= " AND pro_custno = $customerID";
        }
        $sql     .= " GROUP BY
            pro_custno,
            caa_secondsite_error_cuino,
            YEAR(caa_date),
            MONTH(caa_date)";
        $results = $this->db->query($sql);
        $ret     = array();
        while ($row = $results->fetch_array()) {
            $ret[] = $row;
        }
        return $ret;

    }

    function getPerformanceDataForYear($year = null, $isReplication = FALSE)
    {

        if (!$year) {
            $year = date("Y");
        }
        $query = "SELECT (SUM(passes)/ SUM(images))*100 as successRate, avg(target) as targetRate, MONTH FROM (
            SELECT MONTH(created_at) AS MONTH, images, passes, target FROM backup_performance_log WHERE YEAR(created_at) = '$year' ";
        if (!$isReplication) {
            $query .= " and not isReplication ";
        } else {
            $query .= " and isReplication ";
        }
        $query  .= " ) t GROUP BY t.month";
        $result = $this->db->query($query);
        $data   = [];
        while ($row = $result->fetch_assoc()) {
            $data[$row['MONTH']] = $row;
        }
        return $data;
    }

    function getPerformanceDataAvailableYears()
    {
        $query  = "SELECT  DISTINCT YEAR(created_at) AS YEAR  FROM    backup_performance_log where not isReplication";
        $result = $this->db->query($query);
        return array_map(
            function ($item) {
                return $item[0];
            },
            $result->fetch_all()
        );
    }

    private function getImageSize($matchedFiles)
    {
        $totalSize = 0;
        foreach ($matchedFiles as $file) {
            $totalSize += filesize($file);
        }
        return $totalSize;
    }

    /**
     * @param $image
     * @param $server
     * @param string|null $networkPath
     * @return array
     */
    private function getImageFiles($image, $server, ?string $networkPath): array
    {
        if (strlen($image['imageName']) == 1) {
            $pattern = '/' . $server['serverName'] . '_' . $image['imageName'];
        } else {
            $pattern = '/' . $image['imageName'];
        }
        $pattern .= '.*(-cd\.spi|spf|(?<!-c[w|m|r])\.spi)$/i';
        return self::preg_ls(
            $networkPath,
            $pattern
        );
    }

    private function recordServerSize($serverCustomerItemId, $size)
    {
        $gb        = round($size / 1E+9);
        $db        = DBConnect::instance()->getDB();
        $statement = $db->prepare(
            "insert into  OBRSServerStorage(checkedAt, serverCustomerItemId, sizeInGB ) values (now(),?,?)"
        );
        $statement->execute([$serverCustomerItemId, $gb]);
    }


}