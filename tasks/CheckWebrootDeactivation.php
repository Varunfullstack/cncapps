<?php
global $cfg;

use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");

require_once($cfg ["path_bu"] . "/BUHeader.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");


$logName = 'CheckWebrootDeactivation';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts = [];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$thing = null;

$buHeader = new BUHeader($thing);
$dsHeader = new DataSet($thing);
$buHeader->getHeader($dsHeader);

$user = "cncappsapi@cnc-ltd.co.uk";
$password = '3uGhNEBW6dsAHd6q';
$client_Id = 'client_e2maZ8d5@cnc-ltd.co.uk';
$client_secret = '{1!XM^QJcqvM8qj';
$gsmKey = "2FB2-LTSW-E06B-3F49-43DC";

// we are going to ask for a new access token
$webrootAPI = new \CNCLTD\WebrootAPI\WebrootAPI($user, $password, $client_Id, $client_secret, $gsmKey);

$matches = [];
$errors = [];
// here we have all the webroot computers...
$dsn = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
];
$labtechDB = new PDO(
    $dsn,
    LABTECH_DB_USERNAME,
    LABTECH_DB_PASSWORD,
    $options
);


$statement = $labtechDB->query(
    "SELECT clients.`Name` AS customerName, computers.`Name` AS computerName, computers.`LastContact` AS lastSeen, computers.`ComputerID`, `v_extradataclients`.`AV Manufacturer` = 'Webroot' AS isWebroot  FROM computers LEFT JOIN clients ON computers.`ClientID` = clients.`ClientID` LEFT JOIN `v_extradataclients` ON v_extradataclients.`clientid` = computers.`ClientID`"
);

if (!$statement) {
    $logger->error('Failed to pull data from Labtech');
    exit;
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
            "webroot"   => null,
            "control"   => null,
            "labtech"   => null,
            "isWebroot" => null,
        ];
    }

    $lastSeenDateTime = new DateTime($row['lastSeen']);

    if (!empty($matches[$customerName][$computerName]['labtech'])) {
        $errorTxt = "Duplicated Labtech device: {$customerName} {$computerName}";
        $logger->error($errorTxt);
        $errors[] = $errorTxt;
        continue;
    }

    $matches[$customerName][$computerName]['labtech'] = new \CNCLTD\ToCheckDevice(
        $row['customerName'],
        $row['computerName'],
        $lastSeenDateTime
    );
}


$sitesResponse = $webrootAPI->getSites();

foreach ($sitesResponse->sites as $site) {
    $re = '/^([a-zA-Z0-9]{4})([a-zA-Z0-9]{4})([a-zA-Z0-9]{4})([a-zA-Z0-9]{4})([a-zA-Z0-9]{4})/';
    preg_match($re, $site->accountKeyCode, $accountKeyMatches, PREG_OFFSET_CAPTURE, 0);
    $siteGSMKey = "{$accountKeyMatches[1][0]}-{$accountKeyMatches[2][0]}-{$accountKeyMatches[3][0]}-{$accountKeyMatches[4][0]}-{$accountKeyMatches[5][0]}";

    if (!$site->siteName) {
        $errorTxt = "Webroot site without name? accountKey: {$site->accountKeyCode}";
        $logger->error($errorTxt);
        $errors[] = $errorTxt;
        continue;
    }

    $customerName = strtolower($site->siteName);

    foreach ($webrootAPI->getDevices($siteGSMKey) as $device) {

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
                "isWebroot" => true
            ];
        }

        if (empty($matches[$customerName][$computerName])) {
            $matches[$customerName][$computerName] = [
                "webroot" => null,
                "control" => null,
                "labtech" => null
            ];
        }

        $lastSeenDateTime = new DateTime($device->lastSeen);

        if (!empty($matches[$customerName][$computerName]['webroot'])) {
            $errorTxt = "Duplicated Webroot device: {$customerName} {$computerName}";
            $logger->error($errorTxt);
            $errors[] = $errorTxt;
            continue;
        }
        $matches[$customerName][$computerName]['webroot'] = new \CNCLTD\ToCheckDevice(
            $customerName,
            $computerName,
            $lastSeenDateTime
        );

    }
}

$sqlite = new SQLite3('E:\Sites\cwcontrol\session.db');
$result = $sqlite->query(
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

    $matches[$customerName][$computerName]['control'] = new \CNCLTD\ToCheckDevice(
        $row['customerName'],
        $row['computerName'],
        $lastSeenDateTime
    );
}
$result->finalize();
$csv = fopen('data.csv', 'w');
fputcsv($csv, ["Customer", "Machine", "Automate", "Control", "Webroot"]);

$toCheckDate = (new DateTime())->sub(
    new DateInterval("P{$dsHeader->getValue(DBEHeader::computerLastSeenThresholdDays)}D")
);
//$data = json_encode($matches, JSON_INVALID_UTF8_IGNORE);
//if (!$data) {
//    $logger->error('Failed to parse JSON ' . json_last_error_msg());
//}
//file_put_contents('test.json', $data);

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

$buMail = new BUMail($thing);

$toEmail = 'unseencomputers@cnc-ltd.co.uk';
$hdrs = array(
    'From'         => 'support@cnc-ltd.co.uk',
    'To'           => $toEmail,
    'Subject'      => "Computers not seen recently",
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);

global $twig;
$body = $twig->render('@internal/computersNotSeenRecentlyEmail.html.twig', ["items" => $errors]);
$buMail->mime->setHTMLBody($body);
$buMail->mime->addAttachment('data.csv', 'text/csv');

$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset'  => 'UTF-8',
    'html_charset'  => 'UTF-8',
    'head_charset'  => 'UTF-8'
);

$body = $buMail->mime->get($mime_params);

$hdrs = $buMail->mime->headers($hdrs);

$buMail->send($toEmail, $hdrs, $body);

echo file_get_contents('data.csv');

