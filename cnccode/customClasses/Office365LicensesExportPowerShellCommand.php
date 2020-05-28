<?php


namespace CNCLTD;
global $cfg;
require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocument.php");
require_once($cfg["path_dbe"] . "/DBEOSSupportDates.php");
require_once($cfg["path_dbe"] . "/DBEHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/DBEOffice365License.php");
require_once($cfg["path_dbe"] . "/DBEPasswordService.inc.php");
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');

use BUActivity;
use BUCustomer;
use BUHeader;
use BUMail;
use BUPassword;
use DataSet;
use DateInterval;
use DateTime;
use DBECallActivity;
use DBEContact;
use DBECustomer;
use DBEHeader;
use DBEJCallActivity;
use DBEJProblem;
use DBEOffice365License;
use DBEPassword;
use DBEPasswordService;
use DBEPortalCustomerDocument;
use DBEProblem;
use Exception;
use Mail_mime;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Template;
use Twig\TwigFilter;
use UnexpectedValueException;


class Office365LicensesExportPowerShellCommand extends PowerShellCommandRunner
{
    private $user;
    private $password;
    private $dbeCustomer;
    private $dbeHeader;
    private $warningMailboxes = [];
    /**
     * @var bool
     */
    private $alertMode;

    public function __construct($dbeCustomer, LoggerCLI $logger, $debugMode = false, $alertMode = false)
    {
        $this->debugMode = $debugMode;
        $this->dbeCustomer = $dbeCustomer;
        $this->alertMode = $alertMode;
        $customerID = $dbeCustomer->getValue(DBECustomer::customerID);
        $customerName = $dbeCustomer->getValue(DBECustomer::name);
        $buCustomer = new BUCustomer($this);

        $logger->info('Getting A Office 365 Data for Customer: ' . $customerID . ' - ' . $customerName);
        // we have to pull from passwords.. the service 10
        $dbePassword = $buCustomer->getOffice365PasswordItem($customerID);
        $dbePasswordService = new DBEPasswordService($this);
        $dbePasswordService->getRow(10);

        if (!$dbePassword->rowCount) {
            $passwordServiceCheck = $buCustomer->getPasswordItemByPasswordServiceId($customerID, 8);
            if ($passwordServiceCheck->rowCount()) {
                $message = "This customer has an Office 365 login but does not have a {$dbePasswordService->getValue(DBEPasswordService::description)} set, please correct.";
                $logger->error($message);
                $this->createFailedSR($dbeCustomer, $message);
                return;
            }

            $message = 'This customer does not have a Office 365 Admin Portal service password';
            $logger->warning($message);
            return;
        }

        if ($dbePassword->rowCount > 1) {
            $this->createFailedSR(
                $dbeCustomer,
                "There are multiple {$dbePasswordService->getValue(DBEPasswordService::description)} and there must only be one, please correct"
            );
        }

        $buPassword = new BUPassword($this);
        $userName = $buPassword->decrypt($dbePassword->getValue(DBEPassword::username));
        $password = $buPassword->decrypt($dbePassword->getValue(DBEPassword::password));
        $this->outputFilePath = __DIR__ . '\office365Output.json';
        $this->user = $userName;
        $this->password = $password;
        $this->logger = $logger;
        $this->commandName = "365OfficeLicensesExport";
        $data = $this->run();

        $mailboxes = $data['mailboxes'];
        $licenses = $data['licenses'];
        $devices = $data['devices'];
        $BUHeader = new BUHeader($thing);
        $this->dbeHeader = new DataSet($thing);
        $BUHeader->getHeader($this->dbeHeader);
        $dbeOffice365Licenses = new DBEOffice365License($this);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        if (count($mailboxes)) {
            try {
                $this->processMailboxes(
                    $spreadsheet,
                    $mailboxes,
                    $dbeCustomer,
                    $dbeOffice365Licenses
                );

                if ($this->alertMode) {
                    $logger->notice(
                        'Alert mode is enabled, will notify of any mailboxes that are on or over the limit'
                    );
                    $primaryMainContactId = $this->dbeCustomer->getValue(DBECustomer::primaryMainContactID);
                    if ($primaryMainContactId && count($this->warningMailboxes)) {

                        $dbeContact = new DBEContact($this);
                        $dbeContact->getRow($primaryMainContactId);
                        $subject = "Warning - Some Mailboxes Are Almost Full";
                        $emailTo = $dbeContact->getValue(DBEContact::email);
                        $hdrs = array(
                            'From'         => CONFIG_SUPPORT_EMAIL,
                            'To'           => $emailTo,
                            'Subject'      => $subject,
                            'Date'         => date("r"),
                            'Content-Type' => 'text/html; charset=UTF-8'
                        );

                        $mime = new Mail_mime();
                        global $twig;

                        usort(
                            $this->warningMailboxes,
                            function ($a, $b) {
                                return $b['TotalItemSize'] - $a['TotalItemSize'];
                            }
                        );
                        $twig->addFilter(
                            new TwigFilter(
                                'MBtoGB',
                                function ($string) {
                                    if (!is_numeric($string)) {
                                        return;
                                    }
                                    return number_format($string) . 'GB';
                                }
                            )
                        );
                        $body = $twig->render(
                            "@internal/emailAlmostFullAlertEmail.html.twig",
                            [
                                "contactFirstName" => $dbeContact->getValue(DBEContact::firstName),
                                "mailboxes"        => $this->warningMailboxes
                            ]
                        );

                        $mime->setHTMLBody($body);

                        $mime_params = array(
                            'text_encoding' => '7bit',
                            'text_charset'  => 'UTF-8',
                            'html_charset'  => 'UTF-8',
                            'head_charset'  => 'UTF-8'
                        );

                        $body = $mime->get($mime_params);

                        $hdrs = $mime->headers($hdrs);

                        $buMail = new BUMail($this);

                        $buMail->putInQueue(
                            CONFIG_SUPPORT_EMAIL,
                            $emailTo,
                            $hdrs,
                            $body
                        );
                    }


                }

            } catch (Exception $exception) {
                $logger->error('Failed to process mailboxes for customer: ' . $exception->getMessage());
            }
        }

        if (count($licenses)) {
            try {
                $this->processLicenses(
                    $spreadsheet,
                    $licenses,
                    $dbeCustomer,
                    $dbeOffice365Licenses
                );
            } catch (Exception $exception) {
                $logger->error('Failed to process licenses for customer: ' . $exception->getMessage());
            }
        }

        if (count($devices)) {
            try {
                $this->processDevices(
                    $spreadsheet,
                    $devices,
                );
            } catch (Exception $exception) {
                $logger->error('Failed to process devices for customer: ' . $exception->getMessage());
            }
        }

        if (!count($mailboxes) && !count($licenses) && !count($devices)) {
            $message = 'This customer does not have a licences nor mailboxes nor devices';
            $logger->warning($message);
            throw new UnexpectedValueException($message);
        }
        global $db;

        $statement = $db->preparedQuery(
            "insert into customerOffice365StorageStats values (?,?,?,?) on duplicate key update totalOneDriveStorageUsed = ?, totalEmailStorageUsed = ?",
            [
                [
                    "type"  => 'i',
                    "value" => $customerID
                ],
                [
                    "type"  => 's',
                    "value" => (new DateTime())->format(DATE_MYSQL_DATE)
                ],
                [
                    "type"  => 'd',
                    "value" => $data['totalOneDriveStorageUsed']
                ],
                [
                    "type"  => 'd',
                    "value" => $data['totalEmailStorageUsed']
                ],
                [
                    "type"  => 'd',
                    "value" => $data['totalOneDriveStorageUsed']
                ],
                [
                    "type"  => 'd',
                    "value" => $data['totalEmailStorageUsed']
                ]
            ]
        );

        $spreadsheet->removeSheetByIndex(0);
        $writer = new Xlsx($spreadsheet);
        $customerFolder = $buCustomer->getCustomerFolderPath($customerID);
        $folderName = $customerFolder . "\Review Meetings\\";
        if (!file_exists($folderName)) {
            mkdir(
                $folderName,
                0777,
                true
            );
        }
        $fileName = "Current Mailbox Extract.xlsx";
        $filePath = $folderName . $fileName;
        try {

            $writer->save(
                $filePath
            );
            $dbeCustomerDocument = new DBEPortalCustomerDocument($thing);
            $dbeCustomerDocument->getCurrentOffice365Licenses($customerID);

            $dbeCustomerDocument->setValue(
                DBEPortalCustomerDocument::file,
                file_get_contents($filePath)
            );

            if (!$dbeCustomerDocument->getValue(
                    DBEPortalCustomerDocument::createdDate
                ) || $dbeCustomerDocument->getValue(DBEPortalCustomerDocument::createdDate) == '0000-00-00 00:00:00') {

                $dbeCustomerDocument->setValue(
                    DBEPortalCustomerDocument::createdDate,
                    (new DateTime())->format(DATE_MYSQL_DATETIME)
                );
            }

            if (!$dbeCustomerDocument->rowCount) {
                $dbeCustomerDocument->setValue(
                    DBEPortalCustomerDocument::customerID,
                    $customerID
                );
                $dbeCustomerDocument->setValue(
                    DBEPortalCustomerDocument::description,
                    'Current Mailbox List'
                );
                $dbeCustomerDocument->setValue(
                    DBEPortalCustomerDocument::filename,
                    $fileName
                );
                $dbeCustomerDocument->setValue(
                    DBEPortalCustomerDocument::fileMimeType,
                    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                );
                $dbeCustomerDocument->setValue(
                    DBEPortalCustomerDocument::mainContactOnlyFlag,
                    'Y'
                );

                $dbeCustomerDocument->insertRow();
            } else {
                $dbeCustomerDocument->updateRow();
            }

            $logger->info('All good!!. Creating file ' . $fileName);
        } catch (Exception $exception) {
            print_r(
                Calculation::getInstance(
                    $spreadsheet
                )->getDebugLog()
                    ->getLog()
            );
            var_dump($exception->getTraceAsString());
            $logger->error('Failed to save file, possibly file open: ' . $exception->getMessage());
        }

    }

    /**
     * @param DBECustomer $dbeCustomer
     * @param $errorMsg
     * @param null $stackTrace
     * @param null $position
     */
    function createFailedSR(DBECustomer $dbeCustomer, $errorMsg, $stackTrace = null, $position = null)
    {
        $customerID = $dbeCustomer->getValue(DBECustomer::customerID);
        $buActivity = new BUActivity($thing);
        $buCustomer = new BUCustomer($thing);
        $primaryContact = $buCustomer->getPrimaryContact($customerID);
        $buHeader = new BUHeader($thing);
        $dsHeader = new DataSet($thing);
        $buHeader->getHeader($dsHeader);


        $slaResponseHours = $buActivity->getSlaResponseHours(
            4,
            $customerID,
            $primaryContact->getValue(DBEContact::contactID)
        );

        $dbeProblem = new DBEProblem($thing);
        $dbeProblem->setValue(DBEProblem::problemID, null);
        $siteNo = $primaryContact->getValue(DBEContact::siteNo);
        $dbeProblem->setValue(
            DBEProblem::hdLimitMinutes,
            $dsHeader->getValue(DBEHeader::hdTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::esLimitMinutes,
            $dsHeader->getValue(DBEHeader::esTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::smallProjectsTeamLimitMinutes,
            $dsHeader->getValue(DBEHeader::smallProjectsTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::projectTeamLimitMinutes,
            $dsHeader->getValue(DBEHeader::projectTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::slaResponseHours,
            $slaResponseHours
        );
        $dbeProblem->setValue(
            DBEProblem::customerID,
            $customerID
        );
        $dbeProblem->setValue(
            DBEProblem::status,
            'I'
        );
        $dbeProblem->setValue(
            DBEProblem::priority,
            4
        );
        $dbeProblem->setValue(
            DBEProblem::dateRaised,
            date(DATE_MYSQL_DATETIME)
        ); // default
        $dbeProblem->setValue(
            DBEProblem::contactID,
            $primaryContact->getValue(DBEContact::contactID)
        );
        $dbeProblem->setValue(
            DBEJProblem::hideFromCustomerFlag,
            'Y'
        );
        $dbeProblem->setValue(
            DBEJProblem::queueNo,
            2
        );

        $dbeProblem->setValue(
            DBEJProblem::rootCauseID,
            83
        );
        $dbeProblem->setValue(
            DBEJProblem::userID,
            null
        );        // not allocated
        $dbeProblem->insertRow();

        $dbeCallActivity = new DBECallActivity($thing);

        $dbeCallActivity->setValue(
            DBEJCallActivity::callActivityID,
            null
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $siteNo
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::contactID,
            $primaryContact->getValue(DBEContact::contactID)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_INITIAL_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $startTime = date('H:i');
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            $startTime
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            $startTime
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::serverGuard,
            'N'
        );

        $details = "Office 365 License Export Failed: " . $errorMsg;
        if ($position) {
            $details .= " " . $position;
        }

        if ($stackTrace) {
            $details .= " " . $stackTrace;
        }

        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            $details
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getPKValue()
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            USER_SYSTEM
        );

        $dbeCallActivity->insertRow();
    }

    public function run()
    {
        try {
            $data = parent::run();
        } catch (PowerShellScriptFailedToProcessException $exception) {
            $this->logger->error('Failed to parse for customer: ' . $exception->getOutput());
            $this->createFailedSR($this->dbeCustomer, "Could not parse Powershell response: {$exception->getOutput()}");
            throw new Exception('Failed');
        }
        if (isset($data['error'])) {
            $this->logger->error(
                'Failed to pull data for customer: ' . $data['errorMessage'] . ' ' . $data['stackTrace']
            );
            $this->createFailedSR($this->dbeCustomer, $data['errorMessage'], $data['stackTrace'], $data['position']);
            throw new Exception('Errors detected');
        }

        if (count($data['errors'])) {
            foreach ($data['errors'] as $error) {
                $this->logger->warning(
                    "Error received from powershell output, but the execution was not stopped:  " . $error
                );
            }
        }
        return $data;
    }

    /**
     * @param Spreadsheet $spreadSheet
     * @param $mailboxes
     * @param DBECustomer $dbeCustomer
     * @param DBEOffice365License $dbeOffice365Licenses
     * @param LoggerCLI $logger
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    function processMailboxes(Spreadsheet $spreadSheet,
                              $mailboxes,
                              DBECustomer $dbeCustomer,
                              DBEOffice365License $dbeOffice365Licenses
    )
    {
        $dateTime = new DateTime();
        $mailboxLimits = [];
        $licensedUsers = 0;
        $otherLicenses = 0;
        $totalizationRow = [
            "Total"         => "Total",
            "TotalMailBox"  => 0,
            "Empty"         => null,
            "LicensedUsers" => 0,
            1               => null,
            2               => null,
            3               => "Total",
            "TotalOneDrive" => 0
        ];

        foreach ($mailboxes as $key => $datum) {
            $mailboxLimit = null;
            $licenseValue = null;
            if ($datum['Licenses']) {
                if (!is_array($datum['Licenses'])) {
                    $datum['Licenses'] = explode(" ", $datum['Licenses']);
                }
                $licenseValue = implode(", ", $datum['Licenses']);

                if ($licenseValue && strpos(
                        strtolower($datum['DisplayName']),
                        'leaver'
                    ) !== false && $datum['RecipientTypeDetails'] == 'SharedMailbox') {
                    $this->logger->warning('Raising a Customer Leaver with License SR while processing Mailboxes');
                    $this->raiseCustomerLeaverWithLicenseSR($dbeCustomer, $datum['DisplayName']);
                }

                foreach ($datum['Licenses'] as $license) {
                    $dbeOffice365Licenses->getRowForLicense($license);
                    if ($dbeOffice365Licenses->rowCount()) {
                        $licenseValue = str_replace(
                            $license,
                            $dbeOffice365Licenses->getValue(DBEOffice365License::replacement),
                            $licenseValue
                        );
                        if (!$mailboxLimit && $dbeOffice365Licenses->getValue(DBEOffice365License::mailboxLimit)) {
                            $mailboxLimit = $dbeOffice365Licenses->getValue(DBEOffice365License::mailboxLimit);
                        }
                    } else {
                        $this->logger->warning('Raising a License not found SR while processing Mailboxes:' . $license);
                        $this->raiseCNCRequest($license, $dbeCustomer, $datum['DisplayName']);
                    }
                }
            }
            $licensesArray = explode(", ", $licenseValue);
            sort($licensesArray);
            $licenseValue = implode(", ", $licensesArray);

            switch ($mailboxes[$key]['RecipientTypeDetails']) {
                case "SharedMailbox":
                    $mailboxes[$key]['RecipientTypeDetails'] = "Shared";
                    if (!$mailboxes[$key]['IsLicensed']) {
                        $mailboxLimit = 51200;
                    }
                    $otherLicenses++;
                    break;
                case "UserMailbox":
                    $mailboxes[$key]['RecipientTypeDetails'] = "User";
                    $licensedUsers++;
                    break;
                case 'RoomMailbox':
                    $mailboxes[$key]['RecipientTypeDetails'] = "Room";
                    break;
                case 'EquipmentMailbox':
                    $mailboxes[$key]['RecipientTypeDetails'] = "Equipment";
                    break;
                default:
                    $mailboxes[$key]['RecipientTypeDetails'] = "Unknown";
            }

            if ($mailboxes[$key]['IsLicensed']) {
                if ($mailboxes[$key]['RecipientTypeDetails'] == "User") {
                    $licensedUsers++;
                } elseif ($mailboxes[$key]['RecipientTypeDetails'] == "Shared") {
                    $otherLicenses++;
                }
            }

            $mailboxes[$key]['Licenses'] = $licenseValue;
            $mailboxes[$key]['IsLicensed'] = $mailboxes[$key]['IsLicensed'] ? 'Yes' : 'No';
            $totalizationRow['TotalMailBox'] += $datum['TotalItemSize'];
            $mailboxes[$key]['TotalItemSize'] = $datum['TotalItemSize'];
            $totalizationRow['TotalOneDrive'] += $datum['OneDriveStorageUsed'];
            if ($this->debugMode) {
                $mailboxes[$key][] = $mailboxLimit;
            }
            $mailboxLimits[] = $mailboxLimit;
        }

        $mailboxesSheet = $spreadSheet->createSheet();
        $mailboxesSheet->setTitle('Mailboxes');
        $mailboxesSheet->fromArray(
            [
                "Display Name",
                "Mailbox Size (MB)",
                "Mailbox Type",
                "Is Licensed",
                "Licenses",
                "Webmail Enabled",
                "MFA Enabled",
                "OneDrive Size(MB)"
            ],
            null,
            'A1'
        );
        $mailboxesSheet->fromArray(
            $mailboxes,
            null,
            'A2',
            true
        );
        $highestRow = count($mailboxes) + 2;
        if ($licensedUsers + $otherLicenses) {
            $updateCustomer = new DBECustomer($thing);
            $updateCustomer->getRow($dbeCustomer->getValue(DBECustomer::customerID));
            $updateCustomer->setValue(DBECustomer::licensedOffice365Users, $licensedUsers + $otherLicenses);
            $updateCustomer->updateRow();
        }
        $mailboxesSheet->fromArray(
            $totalizationRow,
            null,
            'A' . $highestRow,
            true
        );

        $mailboxesSheet->setCellValue(
            "B$highestRow",
            '=sum(B2:B' . ($highestRow - 1) . ')'
        );
        $formula = '=countifs(D2:D' . ($highestRow - 1) . ', "yes",C2:C' . ($highestRow - 1) . ',"User" ) & " Licensed Users | " & countifs(D2:D' . ($highestRow - 1) . ', "yes",C2:C' . ($highestRow - 1) . ',"Shared" ) & " Other Licenses"';
        $mailboxesSheet->setCellValue(
            "D$highestRow",
            $formula
        );


        $legendRowStart = $highestRow + 2;
        $mailboxesSheet->fromArray(
            [
                ["User is at {$this->dbeHeader->getValue(DBEHeader::office365MailboxRedWarningThreshold)}% of mailbox size limit"],
                ["User is at {$this->dbeHeader->getValue(DBEHeader::office365MailboxYellowWarningThreshold)}% of mailbox size limit"],
                ["Report generated at " . $dateTime->format("d-m-Y H:i:s")],
            ],
            null,
            'A' . $legendRowStart
        );
        $mailboxesSheet->getStyle("A{$legendRowStart}:A$legendRowStart")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB("FFFFC7CE");
        $mailboxesSheet->getStyle("A" . ($legendRowStart + 1) . ":A" . ($legendRowStart + 1))
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB("FFFFEB9C");

        $mailboxesSheet->getStyle("A$highestRow:H$highestRow")->getFont()->setBold(true);

        $mailboxesSheet->getStyle("A1:H1")->getFont()->setBold(true);

        $mailboxesSheet->getStyle("A1:H$highestRow")->getAlignment()->setHorizontal('center');

        for ($i = 0; $i < count($mailboxes); $i++) {
            $currentRow = 2 + $i;

            if ($mailboxLimits[$i]) {
                $usage = $mailboxes[$i]['TotalItemSize'] / $mailboxLimits[$i] * 100;
                $color = null;
                if ($usage >= $this->dbeHeader->getValue(DBEHeader::office365MailboxYellowWarningThreshold)) {
                    $color = "FFFFEB9C";
                }

                if ($usage >= $this->dbeHeader->getValue(DBEHeader::office365MailboxRedWarningThreshold)) {
                    $color = "FFFFC7CE";
                    $mailboxes[$i]['Limit'] = $mailboxLimits[$i];
                    $this->warningMailboxes[] = $mailboxes[$i];
                }

                if ($color) {
                    $mailboxesSheet->getStyle("A$currentRow:" . $mailboxesSheet->getHighestDataColumn() . "$currentRow")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB($color);
                }
            }
        }

        $mailboxColumn = $mailboxesSheet->getStyle("B2:B$highestRow");
        $mailboxColumn->getNumberFormat()->setFormatCode("#,##0");
        $mailboxColumn->getAlignment()->setHorizontal('right');

        $oneDriveSizeColumn = $mailboxesSheet->getStyle("H2:H$highestRow");
        $oneDriveSizeColumn->getNumberFormat()->setFormatCode("#,##0");
        $oneDriveSizeColumn->getAlignment()->setHorizontal('right');


        foreach (range('A', $mailboxesSheet->getHighestDataColumn()) as $col) {
            $mailboxesSheet->getColumnDimension($col)
                ->setAutoSize(true);
        }
    }

    /**
     * @param DBECustomer $dbeCustomer
     * @param $userName
     */
    function raiseCustomerLeaverWithLicenseSR(DBECustomer $dbeCustomer, $userName)
    {
        $customerID = $dbeCustomer->getValue(DBECustomer::customerID);
        $buActivity = new BUActivity($thing);
        $buCustomer = new BUCustomer($thing);
        $primaryContact = $buCustomer->getPrimaryContact($customerID);
        $buHeader = new BUHeader($thing);
        $dsHeader = new DataSet($thing);
        $buHeader->getHeader($dsHeader);


        $slaResponseHours = $buActivity->getSlaResponseHours(
            4,
            $customerID,
            $primaryContact->getValue(DBEContact::contactID)
        );

        $dbeProblem = new DBEProblem($thing);
        $dbeProblem->setValue(DBEProblem::problemID, null);
        $siteNo = $primaryContact->getValue(DBEContact::siteNo);
        $dbeProblem->setValue(
            DBEProblem::hdLimitMinutes,
            $dsHeader->getValue(DBEHeader::hdTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::esLimitMinutes,
            $dsHeader->getValue(DBEHeader::esTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::smallProjectsTeamLimitMinutes,
            $dsHeader->getValue(DBEHeader::smallProjectsTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::projectTeamLimitMinutes,
            $dsHeader->getValue(DBEHeader::projectTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::slaResponseHours,
            $slaResponseHours
        );
        $dbeProblem->setValue(
            DBEProblem::customerID,
            $customerID
        );
        $dbeProblem->setValue(
            DBEProblem::status,
            'I'
        );
        $dbeProblem->setValue(
            DBEProblem::priority,
            4
        );
        $dbeProblem->setValue(
            DBEProblem::dateRaised,
            date(DATE_MYSQL_DATETIME)
        ); // default
        $dbeProblem->setValue(
            DBEProblem::contactID,
            $primaryContact->getValue(DBEContact::contactID)
        );
        $dbeProblem->setValue(
            DBEJProblem::hideFromCustomerFlag,
            'Y'
        );
        $dbeProblem->setValue(
            DBEJProblem::queueNo,
            3
        );

        $dbeProblem->setValue(
            DBEJProblem::rootCauseID,
            86
        );
        $dbeProblem->setValue(
            DBEJProblem::userID,
            null
        );        // not allocated
        $dbeProblem->insertRow();

        $dbeCallActivity = new DBECallActivity($thing);

        $dbeCallActivity->setValue(
            DBEJCallActivity::callActivityID,
            null
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $siteNo
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::contactID,
            $primaryContact->getValue(DBEContact::contactID)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_INITIAL_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $startTime = date('H:i');
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            $startTime
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            $startTime
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::serverGuard,
            'N'
        );

        $details = "<p>User $userName is marked as leaver but still has an Office 365 license assigned to it, please review and correct.</p>";

        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            $details
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getPKValue()
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            USER_SYSTEM
        );

        $dbeCallActivity->insertRow();
    }

    function raiseCNCRequest($license, DBECustomer $dbeCustomer, $licenseUser = null)
    {
        $customerID = 282;
        $buActivity = new BUActivity($thing);
        $buCustomer = new BUCustomer($thing);
        $primaryContact = $buCustomer->getPrimaryContact($customerID);
        $buHeader = new BUHeader($thing);
        $dsHeader = new DataSet($thing);
        $buHeader->getHeader($dsHeader);


        $slaResponseHours = $buActivity->getSlaResponseHours(
            4,
            $customerID,
            $primaryContact->getValue(DBEContact::contactID)
        );

        $dbeProblem = new DBEProblem($thing);
        $dbeProblem->setValue(DBEProblem::problemID, null);
        $siteNo = $primaryContact->getValue(DBEContact::siteNo);
        $dbeProblem->setValue(
            DBEProblem::hdLimitMinutes,
            $dsHeader->getValue(DBEHeader::hdTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::esLimitMinutes,
            $dsHeader->getValue(DBEHeader::esTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::smallProjectsTeamLimitMinutes,
            $dsHeader->getValue(DBEHeader::smallProjectsTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::projectTeamLimitMinutes,
            $dsHeader->getValue(DBEHeader::projectTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::slaResponseHours,
            $slaResponseHours
        );
        $dbeProblem->setValue(
            DBEProblem::customerID,
            $customerID
        );
        $dbeProblem->setValue(
            DBEProblem::status,
            'I'
        );
        $dbeProblem->setValue(
            DBEProblem::priority,
            4
        );
        $dbeProblem->setValue(
            DBEProblem::dateRaised,
            date(DATE_MYSQL_DATETIME)
        ); // default
        $dbeProblem->setValue(
            DBEProblem::contactID,
            $primaryContact->getValue(DBEContact::contactID)
        );

        $dbeProblem->setValue(
            DBEJProblem::queueNo,
            1
        );

        $dbeProblem->setValue(
            DBEJProblem::rootCauseID,
            83
        );
        $dbeProblem->setValue(
            DBEJProblem::userID,
            null
        );        // not allocated
        $dbeProblem->insertRow();

        $dbeCallActivity = new DBECallActivity($thing);

        $dbeCallActivity->setValue(
            DBEJCallActivity::callActivityID,
            null
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $siteNo
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::contactID,
            $primaryContact->getValue(DBEContact::contactID)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_INITIAL_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $startTime = date('H:i');
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            $startTime
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            $startTime
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::serverGuard,
            'N'
        );

        $details = "<p>License $license was not found for customer " . $dbeCustomer->getValue(
                DBECustomer::name
            ) . ($licenseUser ? " which is assigned to user $licenseUser." : '') . "</p>
<p>Please add this license within CNCAPPS and rerun the license export process for this customer</p>";

        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            $details
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getPKValue()
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            USER_SYSTEM
        );

        $dbeCallActivity->insertRow();
    }

    /**
     * @param Spreadsheet $spreadSheet
     * @param $licenses
     * @param DBECustomer $dbeCustomer
     * @param DBEOffice365License $dbeOffice365Licenses
     * @param LoggerCLI $logger
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    function processLicenses(Spreadsheet $spreadSheet,
                             $licenses,
                             DBECustomer $dbeCustomer,
                             DBEOffice365License $dbeOffice365Licenses
    )
    {
        $thing = null;
        if (!$licenses || !count($licenses)) {
            return;
        }
        $dateTime = new DateTime();
        $sparedLicenseErrors = [];
        foreach ($licenses as $key => $datum) {
            $licenses[$key][array_key_last($licenses[$key])] = null;
            $dbeOffice365Licenses->getRowForLicense($datum['AccountSkuId']);
            if ($dbeOffice365Licenses->rowCount()) {
                $licenses[$key]['AccountSkuId'] = str_replace(
                    $datum['AccountSkuId'],
                    $dbeOffice365Licenses->getValue(DBEOffice365License::replacement),
                    $datum['AccountSkuId']
                );
                if ($dbeOffice365Licenses->getValue(
                        DBEOffice365License::reportOnSpareLicenses
                    ) && $datum['Unallocated']) {
                    $sparedLicenseErrors[] = [
                        "licenseName" => $licenses[$key]['AccountSkuId'],
                        "quantity"    => $datum['Unallocated']
                    ];
                }
            } else {
                $this->logger->warning('Raising a License not found SR while processing Licenses');
                $this->raiseCNCRequest($datum['AccountSkuId'], $dbeCustomer);
            }
        }

        if (count($sparedLicenseErrors)) {
            // we have found some spared licenses errors...lets send an email to inform about this
            $buMail = new BUMail($thing);


            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
            );

            $template->set_file(
                array(
                    'page' => 'SpareLicensesEmail.html',
                )
            );

            $template->set_block('page', 'licensesBlock', 'licenses');
            $template->setVar(
                [
                    "customerName" => $dbeCustomer->getValue(DBECustomer::name)
                ]
            );
            foreach ($sparedLicenseErrors as $licenseError) {
                $template->setVar(
                    [
                        "licenseName" => $licenseError['licenseName'],
                        "quantity"    => $licenseError['quantity'],
                    ]
                );
                $template->parse("licenses", 'licensesBlock', true);
            }

            $template->parse(
                'output',
                'page',
                true
            );

            $body = $template->get_var('output');

            $subject = "Unallocated O365 licenses for customer " . $dbeCustomer->getValue(DBECustomer::name);
            $emailTo = CONFIG_SALES_EMAIL;

            $hdrs = array(
                'From'         => CONFIG_SUPPORT_EMAIL,
                'To'           => $emailTo,
                'Subject'      => $subject,
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $mime = new Mail_mime();

            $mime->setHTMLBody($body);

            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );

            $body = $mime->get($mime_params);

            $hdrs = $mime->headers($hdrs);


            $buMail->putInQueue(
                CONFIG_SUPPORT_EMAIL,
                "O365sparelicenses@cnc-ltd.co.uk",
                $hdrs,
                $body
            );

        }

        $licensesSheet = $spreadSheet->createSheet();
        $licensesSheet->setTitle('Licenses');

        $licensesSheet->fromArray(
            [
                "License Name",
                "Number of Licenses",
                "Number of Unallocated Licenses"
            ],
            null,
            'A1'
        );
        $licensesSheet->fromArray(
            $licenses,
            null,
            'A2',
            true
        );
        $highestRow = count($licenses) + 2;
        $licensesSheet->fromArray(
            ["Report generated at " . $dateTime->format("d-m-Y H:i:s")],
            null,
            'A' . ($highestRow + 1)
        );
        $highestCol = $licensesSheet->getHighestDataColumn();
        $licensesSheet->getStyle("A$highestRow:$highestCol$highestRow")->getFont()->setBold(true);
        $licensesSheet->getStyle("A1:" . $highestCol . "1")->getFont()->setBold(true);
        $licensesSheet->getStyle("A1:$highestCol$highestRow")->getAlignment()->setHorizontal('center');
        foreach (range('A', $licensesSheet->getHighestDataColumn()) as $col) {
            $licensesSheet->getColumnDimension($col)
                ->setAutoSize(true);
        }
    }

    function processDevices(Spreadsheet $spreadsheet,
                            $devices
    )
    {
        $devicesSheet = $spreadsheet->createSheet();
        $devicesSheet->setTitle('Mobile Devices');
        $devicesSheet->fromArray(
            [
                "Display Name",
                "Email",
                "Device Type",
                "Device Model",
                "Device Friendly Name",
                "Device OS",
                "First Sync",
                "Most Recent Sync"
            ],
            null,
            'A1'
        );
        $devicesSheet->fromArray(
            $devices,
            null,
            'A2',
            true
        );
        $highestRow = $devicesSheet->getHighestRow();
        $highestColumn = $devicesSheet->getHighestColumn();
        $devicesSheet->getStyle("A1:{$highestColumn}1")->getFont()->setBold(true);
        $devicesSheet->getStyle("A1:{$highestColumn}{$highestRow}")->getAlignment()->setHorizontal('center');
        foreach (range('A', $highestColumn) as $col) {
            $devicesSheet->getColumnDimension($col)
                ->setAutoSize(true);
        }
        $thresholdDate = (new DateTime())->sub(
            new DateInterval('P' . $this->dbeHeader->getValue(DBEHeader::office365ActiveSyncWarnAfterXDays) . 'D')
        );
        foreach ($devices as $row => $device) {
            $currentRow = $row + 2;
            $color = null;
            if (!$device['LastSuccessSync']) {
                $color = "FFFFC7CE";
            } else {
                $lastSyncDate = DateTime::createFromFormat('d-m-Y H.i', $device['LastSuccessSync']);
                if ($lastSyncDate < $thresholdDate) {
                    $color = "FFFFC7CE";
                }
            }


            if ($color) {
                $devicesSheet->getStyle("A$currentRow:{$highestColumn}$currentRow")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB($color);
            }
        }
        $dateTime = new DateTime();
        $legendRowStart = $highestRow + 2;
        $devicesSheet->fromArray(
            [
                ["User device has not connected for 60 days"],
                ["Report generated at " . $dateTime->format("d-m-Y H:i:s")],
            ],
            null,
            'A' . $legendRowStart
        );
        $devicesSheet->getStyle("A{$legendRowStart}:A$legendRowStart")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB("FFFFC7CE");
    }

    protected function getParams(): PowerShellParamCollection
    {
        $collection = new PowerShellParamCollection();
        $collection[] = new PowerShellParam("User", $this->user);
        $collection[] = new PowerShellParam("Password", $this->password);
        return $collection;
    }
}