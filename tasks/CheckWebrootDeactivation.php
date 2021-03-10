<?php
global $cfg;
const DATA_CSV_FILENAME = 'data.csv';
use CNCLTD\LoggerCLI;
use CNCLTD\ToCheckDevice;
use CNCLTD\WebrootAPI\WebrootAPI;

require_once(__DIR__ . "/../htdocs/config.inc.php");
require_once($cfg ["path_bu"] . "/BUHeader.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_bu"] . '/BUCustomer.inc.php');
$logName = 'CheckWebrootDeactivation';
$logger  = new LoggerCLI($logName);
// increasing execution time to infinity...
ini_set('max_execution_time', 0);
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "dt";
$longopts  = [];
$options   = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$testMode = false;
if (isset($options['t'])) {
    $testMode = true;
}
$thing    = null;
$buHeader = new BUHeader($thing);
$dsHeader = new DataSet($thing);
$buHeader->getHeader($dsHeader);
$user          = "cncappsapi@" . CONFIG_PUBLIC_DOMAIN;
$password      = '3uGhNEBW6dsAHd6q';
$client_Id     = 'client_e2maZ8d5@' . CONFIG_PUBLIC_DOMAIN;
$client_secret = '{1!XM^QJcqvM8qj';
$gsmKey        = "2FB2-LTSW-E06B-3F49-43DC";
// we are going to ask for a new access token
$webrootAPI = new WebrootAPI($user, $password, $client_Id, $client_secret, $gsmKey);
$matches    = [];
$errors     = [];
// here we have all the webroot computers...
$dsn       = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
$options   = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
];
$labtechDB = new PDO(
    $dsn, LABTECH_DB_USERNAME, LABTECH_DB_PASSWORD, $options
);
$statement = $labtechDB->query(
    "SELECT clients.`Name` AS customerName, computers.`Name` AS computerName, computers.`LastContact` AS lastSeen, computers.`ComputerID`, `v_extradataclients`.`AV Manufacturer` = 'Webroot' AS isWebroot  FROM computers LEFT JOIN clients ON computers.`ClientID` = clients.`ClientID` LEFT JOIN `v_extradataclients` ON v_extradataclients.`clientid` = computers.`ClientID`"
);
if (!$statement) {
    $logger->error('Failed to pull data from Labtech');
    exit;
}
function isLabtechRetired($computerName, $customerName, PDO $labtechDB): bool
{
    $statement = $labtechDB->prepare(
        "SELECT
  COUNT(*) > 0 as isRetired
FROM
  retiredassets
  LEFT JOIN clients
    ON retiredassets.`ClientID` = clients.`ClientID`
WHERE clients.`Name` = ?
  AND retiredassets.`Name` = ?"
    );
    $statement->execute(
        [
            $customerName,
            $computerName
        ]
    );
    return (bool)$statement->fetchColumn(0);
}

while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {

    if (!$row['customerName'] || !$row['computerName']) {
        $errorTxt = "Labtech device without customer name or computer name ? ComputerID {$row['computerID']} ";
        $logger->error($errorTxt);
        $errors[] = $errorTxt;
        continue;
    }
    $customerName = strtolower($row['customerName']);
    $computerName = strtolower($row['computerName']);
    if (empty($matches[$customerName])) {
        $matches[$customerName] = [
            "isWebroot" => $row['isWebroot']
        ];
    }
    if (empty($matches[$customerName][$computerName])) {
        $matches[$customerName][$computerName] = [
            "webroot"     => null,
            "control"     => null,
            "labtech"     => null,
            "isWebroot"   => null,
            "webrootMIDs" => []
        ];
    }
    $lastSeenDateTime = new DateTime($row['lastSeen']);
    if (!empty($matches[$customerName][$computerName]['labtech'])) {
        $errorTxt = "Duplicated Labtech device: {$customerName} {$computerName}";
        $logger->error($errorTxt);
        $errors[] = $errorTxt;
        continue;
    }
    $matches[$customerName][$computerName]['labtech'] = new ToCheckDevice(
        $row['customerName'], $row['computerName'], $lastSeenDateTime
    );
}
$sitesResponse = $webrootAPI->getSites();
function raiseDuplicatedMIDRequest($computerName, $customerName)
{
    $dbeCustomer = new DBECustomer($thing);
    $customer    = $dbeCustomer->getCustomerByName($customerName);
    $customerId  = 282;
    $reason      = "$computerName is duplicated in Webroot Portal, please check and retire as appropriate";
    if (!$customer) {
        $reason .= " for customer $customerName";
    } else {
        $customerId = $customer->getValue(DBECustomer::customerID);
    }
    $emailSubjectSummary = "$computerName is duplicated in Webroot Portal";
    raiseRequest($customerId, $reason, $computerName, $emailSubjectSummary);
}

function raiseSeenInLabtechButNotWebrootRequest($computerName, $customerName, $thresholdDays)
{
    $dbeCustomer = new DBECustomer($thing);
    $customer    = $dbeCustomer->getCustomerByName($customerName);
    $customerId  = 282;
    $reason      = "$computerName has not been seen in Webroot for over $thresholdDays days but has been reporting online within CW Automate so Webroot might be broken. Please review and correct";
    if (!$customer) {
        $reason .= " for customer $customerName";
    } else {
        $customerId = $customer->getValue(DBECustomer::customerID);
    }
    $emailSubjectSummary = "$computerName not checking into Webroot Portal";
    raiseRequest($customerId, $reason, $computerName, $emailSubjectSummary);
}

function raiseSeenInWebrootButNotLabtechRequest($computerName, $customerName, $thresholdDays)
{
    $dbeCustomer = new DBECustomer($thing);
    $customer    = $dbeCustomer->getCustomerByName($customerName);
    $customerId  = 282;
    $reason      = "$computerName has been seen in Webroot for within $thresholdDays days but has not been reporting online within CW Automate so the agent might be broken. Please review and correct";
    if (!$customer) {
        $reason .= " for customer $customerName";
    } else {
        $customerId = $customer->getValue(DBECustomer::customerID);
    }
    $emailSubjectSummary = "$computerName seen in Webroot but not Automate";
    raiseRequest($customerId, $reason, $computerName, $emailSubjectSummary);
}

function raiseRequest($customerId, $reason, $computerName, $emailSubjectSummary)
{

    $buActivity     = new BUActivity($thing);
    $buCustomer     = new BUCustomer($thing);
    $primaryContact = $buCustomer->getPrimaryContact($customerId);
    $buHeader       = new BUHeader($thing);
    $dsHeader       = new DataSet($thing);
    $buHeader->getHeader($dsHeader);
    $priority         = 3;
    $slaResponseHours = $buActivity->getSlaResponseHours(
        $priority,
        $customerId,
        $primaryContact->getValue(DBEContact::contactID)
    );
    $dbeProblem       = new DBEProblem($thing);
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
        $customerId
    );
    $dbeProblem->setValue(
        DBEProblem::status,
        'I'
    );
    $dbeProblem->setValue(
        DBEProblem::priority,
        $priority
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
    $dbeProblem->setValue(
        DBEProblem::raiseTypeId,
        BUProblemRaiseType::ALERTID
    );
    $dbeProblem->setValue(DBEProblem::assetName, $computerName);
    $dbeProblem->setValue(DBEProblem::emailSubjectSummary, $emailSubjectSummary);
    $dbeProblem->setValue(
        DBEProblem::hideFromCustomerFlag,
        'Y'
    );
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
    $dbeCallActivity->setValue(
        DBEJCallActivity::reason,
        $reason
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

$toCheckDate = (new DateTime())->sub(
    new DateInterval("P{$dsHeader->getValue(DBEHeader::computerLastSeenThresholdDays)}D")
);
foreach ($sitesResponse->sites as $site) {

    if (!$site->siteName) {
        $errorTxt = "Webroot site without name? accountKey: {$site->accountKeyCode}";
        $logger->error($errorTxt);
        $errors[] = $errorTxt;
        continue;
    }
    $customerName = strtolower($site->siteName);
    foreach ($webrootAPI->getEndpoints($site->siteId) as $device) {

        if ($device->deactivated) {
            continue;
        }
        if (!$device->hostName) {
            $errorTxt = "Webroot device without name? accountKey: {$site->accountKeyCode} {$site->siteName}";
            $logger->error($errorTxt);
            $errors[] = $errorTxt;
            continue;
        }
        $computerName = strtolower($device->hostName);
        if (empty($matches[$customerName])) {
            $matches[$customerName] = [
                "isWebroot" => true,
            ];
        }
        if (empty($matches[$customerName][$computerName])) {
            $matches[$customerName][$computerName] = [
                "webroot"     => null,
                "control"     => null,
                "labtech"     => null,
                "webrootMIDs" => []
            ];
        }
        $mid = explode(":::", $device->machineId)[0];
        if (empty($matches[$customerName][$computerName]["webrootMIDs"][$mid])) {
            $matches[$customerName][$computerName]["webrootMIDs"][$mid] = true;
        } else {
            raiseDuplicatedMIDRequest($computerName, $customerName);
        }
        $lastSeenDateTime = new DateTime($device->lastSeen);
        if ($lastSeenDateTime <= $toCheckDate && isLabtechRetired($computerName, $customerName, $labtechDB)) {
            $testText = ' (Not actually deactivated testOnly)';
            if (!$testMode) {
                $webrootAPI->deactivateEndpoint($site->siteId, $device->endpointId);
                $testText = '';
            }
            $logger->warning(
                "$computerName Webroot endpoint deactivated due to being retired in Automate and not seen recently in Webroot{$testText}"
            );
            continue;
        }
        if (!empty($matches[$customerName][$computerName]['labtech'])) {
            if ($lastSeenDateTime <= $toCheckDate && $matches[$customerName][$computerName]['labtech']->lastSeenDateTime > $toCheckDate) {
                raiseSeenInLabtechButNotWebrootRequest(
                    $computerName,
                    $customerName,
                    $dsHeader->getValue(DBEHeader::computerLastSeenThresholdDays)
                );
            }
            if ($lastSeenDateTime > $toCheckDate && $matches[$customerName][$computerName]['labtech']->lastSeenDateTime <= $toCheckDate) {
                raiseSeenInWebrootButNotLabtechRequest(
                    $computerName,
                    $customerName,
                    $dsHeader->getValue(DBEHeader::computerLastSeenThresholdDays)
                );
            }
        }
        // ignore same computer name and only care if same instance MID
        if (!empty($matches[$customerName][$computerName]['webroot'])) {
            $errorTxt = "Duplicated Webroot device: {$customerName} {$computerName}";
            $logger->error($errorTxt);
            $errors[] = $errorTxt;
            continue;
        }
        $matches[$customerName][$computerName]['webroot'] = new ToCheckDevice(
            $customerName, $computerName, $lastSeenDateTime
        );

    }
}
$sqlite         = new SQLite3('E:\Sites\cwcontrol\session.db');
$result         = $sqlite->query(
    'select CustomProperty1 as customerName, GuestMachineName as computerName, GuestInfoUpdateTime as lastSeen, rowid from Session'
);
$controlDevices = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {

    $customerName = strtolower($row['customerName']);
    $computerName = strtolower($row['computerName']);
    if (!$customerName || !$computerName) {
        $errorTxt = "Control device without customer name or computer name ? CustomerName: {$customerName} ComputerName: {$computerName} rowid: {$row['rowid']} ";
        $logger->error($errorTxt);
        $errors[] = $errorTxt;
        continue;
    }
    if (empty($matches[$customerName])) {
        $matches[$customerName] = [
            "isWebroot" => false
        ];
    }
    if (empty($matches[$customerName][$computerName])) {
        $matches[$customerName][$computerName] = [
            "control"   => null,
            "labtech"   => null,
            "isWebroot" => null,
        ];
    }
    $lastSeenDateTime = new DateTime($row['lastSeen']);
    if (!empty($matches[$customerName][$computerName]['control'])) {
        $errorTxt = "Duplicated Control device: {$customerName} {$computerName}";
        $logger->error($errorTxt);
        $errors[] = $errorTxt;
        continue;
    }
    $matches[$customerName][$computerName]['control'] = new ToCheckDevice(
        $row['customerName'], $row['computerName'], $lastSeenDateTime
    );
}
$result->finalize();
$csv = fopen(DATA_CSV_FILENAME, 'w');
fputcsv($csv, ["Customer", "Machine", "Automate", "Control", "Webroot"]);
foreach (array_keys($matches) as $customerName) {
    foreach (array_keys($matches[$customerName]) as $computerName) {

        if ($computerName === 'isWebroot') {
            continue;
        }
        if ((empty($matches[$customerName][$computerName]['webroot']) && $matches[$customerName]['isWebroot']) || empty($matches[$customerName][$computerName]['labtech']) || empty($matches[$customerName][$computerName]['control'])) {
            // add this to the CSV
            fputcsv(
                $csv,
                [
                    $customerName,
                    $computerName,
                    empty($matches[$customerName][$computerName]['labtech']) ? 'Missing' : $matches[$customerName][$computerName]['labtech']->lastSeenDateTime->format(
                        DATE_MYSQL_DATETIME
                    ),
                    empty($matches[$customerName][$computerName]['control']) ? 'Missing' : $matches[$customerName][$computerName]['control']->lastSeenDateTime->format(
                        DATE_MYSQL_DATETIME
                    ),
                    empty($matches[$customerName][$computerName]['webroot']) ? 'Missing' : $matches[$customerName][$computerName]['webroot']->lastSeenDateTime->format(
                        DATE_MYSQL_DATETIME
                    ),
                ]
            );
            continue;
        }
        // we have a full match so we need to check the dates
        if ($matches[$customerName][$computerName]['labtech']->lastSeenDateTime <= $toCheckDate || $matches[$customerName][$computerName]['control']->lastSeenDateTime <= $toCheckDate || ($matches[$customerName]['isWebroot'] && $matches[$customerName][$computerName]['webroot']->lastSeenDateTime <= $toCheckDate)) {
            // at least one date is older than the checked date
            fputcsv(
                $csv,
                [
                    $customerName,
                    $computerName,
                    $matches[$customerName][$computerName]['labtech']->lastSeenDateTime->format(
                        DATE_MYSQL_DATETIME
                    ),
                    $matches[$customerName][$computerName]['control']->lastSeenDateTime->format(
                        DATE_MYSQL_DATETIME
                    ),
                    $matches[$customerName]['isWebroot'] ? $matches[$customerName][$computerName]['webroot']->lastSeenDateTime->format(
                        DATE_MYSQL_DATETIME
                    ) : 'N/A',
                ]
            );
        }
    }
}
fclose($csv);
$buMail  = new BUMail($thing);
$toEmail = 'unseencomputers@' . CONFIG_PUBLIC_DOMAIN;
$hdrs    = array(
    'From'         => 'support@' . CONFIG_PUBLIC_DOMAIN,
    'To'           => $toEmail,
    'Subject'      => "Computers not seen recently",
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);
global $twig;
$body = $twig->render('@internal/computersNotSeenRecentlyEmail.html.twig', ["items" => $errors]);
$buMail->mime->setHTMLBody($body);
$buMail->mime->addAttachment(DATA_CSV_FILENAME, 'text/csv');
$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset'  => 'UTF-8',
    'html_charset'  => 'UTF-8',
    'head_charset'  => 'UTF-8'
);
$body        = $buMail->mime->get($mime_params);
$hdrs        = $buMail->mime->headers($hdrs);
$buMail->send($toEmail, $hdrs, $body);
echo file_get_contents(DATA_CSV_FILENAME);

