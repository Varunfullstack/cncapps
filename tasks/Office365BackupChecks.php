<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

use CNCLTD\LoggerCLI;
use CNCLTD\SolarwindsAccountItem;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
global $db;
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomerItem.inc.php');
$logName = 'Office365BackupChecks';
$logger  = new LoggerCLI($logName);
// increasing execution time to infinity...
ini_set('max_execution_time', 0);
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts  = "du";
$longopts   = [];
$options    = getopt($shortopts, $longopts);
$debugMode  = isset($options['d']);
$updateMode = isset($options['u']);
$thing      = null;
$buHeader   = new BUHeader($thing);
$dsHeader   = new DataSet($thing);
$buHeader->getHeader($dsHeader);
// we are going to see if we can log in
$solarwindsAPI        = new \CNCLTD\SolarwindsBackupAPI(
    $dsHeader->getValue(DBEHeader::solarwindsPartnerName),
    $dsHeader->getValue(DBEHeader::solarwindsUsername),
    $dsHeader->getValue(DBEHeader::solarwindsPassword)
);
$missingContractItems = [];
try {
    $accountsInfo = $solarwindsAPI->getAccountsInfo();
    foreach ($accountsInfo as $accountInfo) {
        $logger->info('Processing ' . $accountInfo->name);
        if (!$accountInfo->contractId) {
            $missingContractItems[] = $accountInfo;
            $logger->error('This item does not have a contractId set, will send an email to inform about this');
            continue;
        }
        $customerItem = new DBECustomerItem($thing);
        if (!$customerItem->getRow($accountInfo->contractId)) {
            $logger->error('Contract not found!! Creating SR to inform about this');
            createFailedToUpdateContractSR($accountInfo);
        } else {
            if ($updateMode) {
                $logger->info('Update mode enabled - Updating contract users');
                $customerItem = new DBECustomerItem($thing);
                $customerItem->setValue(DBECustomerItem::users, $accountInfo->protectedUsers);
                $customerItem->setValue(
                    DBECustomerItem::curUnitCost,
                    $customerItem->getValue(
                        DBECustomerItem::costPricePerMonth
                    ) * 12 * $accountInfo->protectedUsers
                );
                $customerItem->setValue(
                    DBECustomerItem::curUnitSale,
                    $customerItem->getValue(
                        DBECustomerItem::salePricePerMonth
                    ) * 12 * $accountInfo->protectedUsers
                );
                $customerItem->updateRow();
            }
            if ($accountInfo->protectedUsers !== null) {
                $customerItem = new DBECustomerItem($thing);
                $customerItem->getRow($accountInfo->contractId);
                if ($customerItem->getValue(DBECustomerItem::users) === null) {
                    $customerItem->setValue(DBECustomerItem::users, 0);
                    $updateCustomerItem = new DBECustomerItem($thing);
                    $updateCustomerItem->getRow($accountInfo->contractId);
                    $updateCustomerItem->setValue(DBECustomerItem::users, 0);
                    $updateCustomerItem->updateRow();
                }
                try {
                    $db->preparedQuery(
                        "insert into contractUsersLog(contractId,users, currentUsers) values (?,?,?) ",
                        [
                            ["type" => "i", "value" => $accountInfo->contractId],
                            ["type" => "i", "value" => $accountInfo->protectedUsers],
                            ["type" => "i", "value" => $customerItem->getValue(DBECustomerItem::users)],
                        ]
                    );
                } catch (\Exception $exception) {
                    createFailedToUpdateContractSR($accountInfo);
                }
            }
        }
        $yesterday = new DateTime();
        $yesterday->sub(new DateInterval('P1D'));
        if (!$accountInfo->lastSuccessfulBackupDate || $accountInfo->lastSuccessfulBackupDate < $yesterday) {
            $logger->warning('Backup is not up to date - Sending email to inform');
            createFailedBackupSR($accountInfo);
        }
    }
    if (count($missingContractItems)) {
        $logger->warning('We have items that do not have contractID, so we have inform about this');
        sendNoContractIdWarningEmail($missingContractItems);
    }

} catch (\Exception $exception) {
    $logger->error('Failed to retrieve accounts info: ' . $exception->getMessage());
}
/**
 * @param SolarwindsAccountItem[] $accountItems
 * @throws \Twig\Error\LoaderError
 * @throws \Twig\Error\RuntimeError
 * @throws \Twig\Error\SyntaxError
 */
function sendNoContractIdWarningEmail(array $accountItems)
{
    $buMail      = new BUMail($thing);
    $senderEmail = CONFIG_SUPPORT_EMAIL;
    $toEmail     = "office365backup@" . CONFIG_PUBLIC_DOMAIN;
    $hdrs        = array(
        'From'         => $senderEmail,
        'To'           => $toEmail,
        'Subject'      => 'Office 365 Backup Portal customers missing contractID',
        'Date'         => date("r"),
        'Content-Type' => 'text/html; charset=UTF-8'
    );
    global $twig;
    $html = $twig->render(
        '@internal/office365BackupPortalNoContractIdWarningEmail.html.twig',
        ["items" => $accountItems]
    );
    $buMail->mime->setHTMLBody($html);
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

function createFailedBackupSR(SolarwindsAccountItem $accountItem)
{
    $buActivity = new BUActivity($thing);
    $buActivity->raiseSolarwindsFailedBackupRequest($accountItem);
}

function createFailedToUpdateContractSR(SolarwindsAccountItem $accountItem)
{
    $buActivity = new BUActivity($thing);
    $buActivity->raiseSolarwindsFailedToUpdateContractRequest($accountItem);
}