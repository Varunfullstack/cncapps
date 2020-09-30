<?php
global $cfg;

use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");

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

$user = "cncappsapi@cnc-ltd.co.uk";
$password = '3uGhNEBW6dsAHd6q';
$client_Id = 'client_e2maZ8d5@cnc-ltd.co.uk';
$client_secret = '{1!XM^QJcqvM8qj';
$gsmKey = "2FB2-LTSW-E06B-3F49-43DC";

// we are going to ask for a new access token
$webrootAPI = new \CNCLTD\WebrootAPI\WebrootAPI($user, $password, $client_Id, $client_secret, $gsmKey);

$matches = [];


$sitesResponse = $webrootAPI->getSites();

foreach ($sitesResponse->sites as $site) {

    $re = '/^([a-zA-Z0-9]{4})([a-zA-Z0-9]{4})([a-zA-Z0-9]{4})([a-zA-Z0-9]{4})([a-zA-Z0-9]{4})/';
    preg_match($re, $site->accountKeyCode, $matches, PREG_OFFSET_CAPTURE, 0);
    $siteGSMKey = "{$matches[1][0]}-{$matches[2][0]}-{$matches[3][0]}-{$matches[4][0]}-{$matches[5][0]}";

    foreach ($webrootAPI->getDevices($siteGSMKey) as $device) {
        if (empty($matches[$site->siteName])) {
            $matches[$site->siteName] = [];
        }

        if (empty($matches[$site->siteName][$device->hostName])) {
            $matches[$site->siteName][$device->hostName] = [
                "webroot" => null,
                "control" => null,
                "labtech" => null
            ];
        }

        $lastSeenDateTime = new DateTime($device->lastSeen);

        if (!empty($matches[$site->siteName][$device->hostName]['webroot'])) {
            $logger->error("Duplicated Webroot device: {$site->siteName} {$device->hostName}");
            continue;
        }
        $matches[$site->siteName][$device->hostName]['webroot'] = new \CNCLTD\ToCheckDevice(
            $site->siteName,
            $device->hostName,
            $lastSeenDateTime
        );

    }
}
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
    "SELECT clients.`Name` AS customerName, computers.`Name` AS computerName, computers.`LastContact` AS lastSeen  FROM computers LEFT JOIN clients ON computers.`ClientID` = clients.`ClientID`"
);
while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {

    if (empty($matches[$row['customerName']])) {
        $matches[$row['customerName']] = [];
    }

    if (empty($matches[$row['customerName']][$row['computerName']])) {
        $matches[$row['customerName']][$row['computerName']] = [
            "webroot" => null,
            "control" => null,
            "labtech" => null
        ];
    }

    $lastSeenDateTime = new DateTime($row['lastSeen']);

    if (!empty($matches[$site->siteName][$device->hostName]['labtech'])) {
        $logger->error("Duplicated Labtech device: {$row['customerName']} {$row['computerName']}");
        continue;
    }

    $matches[$row['customerName']][$row['computerName']]['labtech'] = new \CNCLTD\ToCheckDevice(
        $row['customerName'],
        $row['computerName'],
        $lastSeenDateTime
    );
}


$sqlite = new SQLite3('E:\Sites\cwcontrol\session.db');
$result = $sqlite->query(
    'select CustomProperty1 as customerName, GuestMachineName as computerName, GuestInfoUpdateTime as lastSeen from Session'
);
$controlDevices = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    if (empty($matches[$row['customerName']])) {
        $matches[$row['customerName']] = [];
    }

    if (empty($matches[$row['customerName']][$row['computerName']])) {
        $matches[$row['customerName']][$row['computerName']] = [
            "webroot" => null,
            "control" => null,
            "labtech" => null
        ];
    }

    $lastSeenDateTime = new DateTime($row['lastSeen']);

    if (!empty($matches[$site->siteName][$device->hostName]['control'])) {
        $logger->error("Duplicated Control device: {$row['customerName']} {$row['computerName']}");
        continue;
    }

    $matches[$row['customerName']][$row['computerName']]['control'] = new \CNCLTD\ToCheckDevice(
        $row['customerName'],
        $row['computerName'],
        $lastSeenDateTime
    );
}
$result->finalize();
$csv = fopen('test.csv', 'w');
fputcsv($csv, ["Customer", "Machine", "Automate", "Control", "Webroot"]);

foreach (array_keys($matches) as $customerName) {
    foreach (array_keys($matches[$customerName]) as $computerName) {
        if (empty($matches[$customerName][$computerName]['webroot']) || empty($matches[$customerName][$computerName]['labtech']) || empty($matches[$customerName][$computerName]['control'])) {
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

    }
}

