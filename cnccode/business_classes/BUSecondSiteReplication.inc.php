<?php
global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_dbe"] . "/DBESecondsiteImage.inc.php");
require_once($cfg["path_bu"] . '/BUSecondsite.inc.php');

class BUSecondsiteReplication extends BUSecondsite
{

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
        $this->imageCount  = 0;
        $this->serverCount = 0;
        $this->log         = array();
        $servers           = $this->getServers($customerItemID);
        $this->serverCount = count($servers);
        foreach ($servers as $server) {

            $error             = false;
            $networkPath       = false;
            $excludeFromChecks = false;
            $isSuspended    = $this->isSuspended($server);
            $images         = [];
            $timeToLookFrom = null;
            if ($isSuspended) {
                $this->suspendedServerCount++;
            }
            if ($server[DBECustomerItem::secondSiteReplicationExcludeFlag] == 'Y') {
                $this->excludedLocalServers[] = $server;
                $excludeFromChecks            = true;
            } else {

                if (!$isSuspended && $server['suspendedUntilDate']) {
                    $this->resetSuspendedUntilDate($server['server_cuino']);
                }
                $days     = @$server['imageDelayDays'];
                $dsHeader = new DataSet($this);
                $buHeader = new BUHeader($this);
                $buHeader->getHeader($dsHeader);
                $additionalDays = $dsHeader->getValue(DBEHeader::secondSiteReplicationAdditionalDelayAllowance);
                $days                        += $additionalDays;
                $timeToLookFrom              = strtotime(
                    '-' . $days . ' days',
                    $defaultTimeToLookFrom
                );
                $this->delayedCheckServers[] = $server;
                $images = $this->getImagesByServer($server['server_cuino']);
                if (!$server['secondSiteReplicationPath'] or count($images) == 0) {
                    $error = 'Offsite Backup Replication Path Error Or No Images';
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
                                $networkPath,
                                true
                            );
                        }

                    }
                } else {

                    $networkPath = $server['secondSiteReplicationPath'];
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
                                    $networkPath,
                                    true
                                );
                            }
                        }
                    }
                }
            }
            if (!$error && !$excludeFromChecks) {

                $missingImages  = array();
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
                    $pattern .= '.*(-cd\.spi|spf|(?<!-c[w|m|r])\.spi)$/i';
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
                            $missingImages[]  = 'No file in ' . $networkPath . ' matches pattern: ' . htmlentities(
                                    $pattern
                                );
                            $missingLetters[] = $image['imageName'];
                            $errorMessage = $server['cus_name'] . ' ' . $server['serverName'] . ': No file in ' . $networkPath . ' matches pattern: ' . htmlentities(
                                    $pattern
                                );
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
                        $missingImages,
                        true
                    );

                }

            } // if not error
        } // end foreach contracts
        if (!$customerItemID && !$testRun) {
            /** @var dbSweetcode $db */
            $db = $GLOBALS['db'];
            //check if we have already stored information for today
            $query = "SELECT created_at FROM backup_performance_log WHERE created_at = date(now()) and isReplication";
            $db->query($query);
            $db->next_record();
            $data = $db->Record;
            if ($data['created_at']) {
                return;
            }
            $buHeader = new BUHeader($this);
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
                      isReplication,
                      target
                    ) VALUES (now(), ?, ?, ?, ?, ?, ?, ?, 1, ?)";
            try {
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
                            "type"  => "d",
                            "value" => $dsHeader->getValue(DBEHeader::backupReplicationTargetSuccessRate)
                        ]
                    ]
                );
            } catch (Exception $exception) {
                throw new Exception('Failed to run query: ' . $this->db->error);
            }
        }

    }

    public function getServers($customerItemID = false)
    {
        $queryString = "SELECT
        ci.cui_cuino,
        ci.cui_custno AS custno,
        c.cus_name,
        i.itm_itemtypeno,
        ser.cui_cuino AS server_cuino,
        ser.cui_cust_ref AS serverName,
        ser.secondSiteReplicationPath, 
        ser.offsiteReplicationValidationSuspendedUntilDate as suspendedUntilDate,
        ser.secondsiteImageDelayDays as imageDelayDays,
        ser.secondsiteLocalExcludeFlag,
        ser.secondSiteReplicationExcludeFlag,
        delayuser.cns_name AS delayUser,
        ser.secondsiteImageDelayDate as imageDelayDate,
        suspenduser.cns_name AS suspendUser ,
        ser.offsiteReplicationSuspendedDate as suspendedDate
      FROM
        custitem ci
        JOIN customer c ON c.cus_custno = ci.cui_custno
        JOIN custitem_contract ON custitem_contract.`cic_contractcuino` = ci.cui_cuino
        JOIN custitem ser ON ser.cui_cuino = custitem_contract.cic_cuino
        JOIN item i ON i.itm_itemno = ci.cui_itemno
        LEFT JOIN consultant delayuser ON delayuser.cns_consno = ser.secondsiteImageDelayUserID
        LEFT JOIN consultant suspenduser ON suspenduser.cns_consno = ser.offsiteReplicationSuspendedByUserID
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

    function resetSuspendedUntilDate($cuino)
    {
        $queryString = "UPDATE
    custitem 
    SET
    offsiteReplicationValidationSuspendedUntilDate = NULL,
        offsiteReplicationSuspendedByUserID = null,
        offsiteReplicationSuspendedDate = null
    WHERE
    cui_cuino = $cuino";
        $db = $GLOBALS['db'];
        $db->query($queryString);

    }

    public function getImagesByServer($customerItemID)
    {
        $queryString = "SELECT
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
    Get second site images by server
    */
    function setImageStatusByServer($customerItemID,
                                    $status
    )
    {
        $queryString = "UPDATE
        secondsite_image 
      SET
        replicationStatus = '$status'
      WHERE
        customerItemID = $customerItemID";
        $db = $GLOBALS['db'];
        $db->query($queryString);
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
        replicationStatus = ?,
        replicationImagePath = ?,
        replicationImageTime = ?
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
        AND ci.declinedFlag <> 'Y'";
        if ($status === self::STATUS_EXCLUDED) {
            $queryString .= " AND ser.secondSiteReplicationExcludeFlag = 'Y' group by serverName ";
        } else {
            $queryString .= " AND ser.secondSiteReplicationExcludeFlag <> 'Y' AND replicationStatus = '$status' ";
        }
        $queryString .= " ORDER BY c.cus_name, serverName, ssi.imageName";
        $db = $GLOBALS['db'];
        $db->query($queryString);
        $images = array();
        while ($db->next_record()) {
            $images[] = $db->Record;
        }
        return $images;

    }

    function getPerformanceDataAvailableYears()
    {
        $query  = "SELECT  DISTINCT YEAR(created_at) AS YEAR  FROM backup_performance_log where isReplication";
        $result = $this->db->query($query);
        return array_map(
            function ($item) {
                return $item[0];
            },
            $result->fetch_all()
        );
    }

}