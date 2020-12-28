<?php
use CNCLTD\LoggerCLI;


require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;

$logName = 'FetchBitlockerKeys';
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
require_once($cfg["path_bu"] . "/BUCustomer.inc.php");

class BitLockerComputer
{
    public $customerID;
    public $computerName;
    public $bitlockerRecoveryKey;
}


//we are going to use this to add to the monitoring db
$dsn = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
];
$localDB = new PDO(
    $dsn,
    LABTECH_DB_USERNAME,
    LABTECH_DB_PASSWORD,
    $options
);

$query = $localDB->query(
    'SELECT 
  clients.`ExternalID` as customerID,
  computers.`Name` as computerName,
  v_extradatacomputers.`Bitlocker Recovery Key` as bitlockerRecoveryKey 
FROM
  computers 
  LEFT JOIN clients ON computers.`ClientID`  = clients.`ClientID`
  LEFT JOIN v_extradatacomputers 
    ON v_extradatacomputers.`computerid` = computers.`ComputerID` 
    WHERE v_extradatacomputers.`Bitlocker Password/Key` regexp "[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}"
    ORDER BY ExternalID '
);

/** @var BitLockerComputer[] $computers */
$computers = $query->fetchAll(
    PDO::FETCH_CLASS,
    BitLockerComputer::class
);

$thing = null;
$buCustomer = new BUCustomer($thing);

$previousCustomer = null;
$keyFolder = null;
foreach ($computers as $computer) {
    $matches = [];
    if (preg_match_all(
        "/[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}/m",
        $computer->bitlockerRecoveryKey,
        $matches
    )) {

        if (count($matches)) {
            $matches = $matches[0];
            if ($previousCustomer != $computer->customerID) {
                $dir = $buCustomer->getCustomerFolderPath($computer->customerID);
                $keyFolder = $dir . '/Current Documentation/Bitlocker Recovery Keys/';
            }
            $fileName = $keyFolder . $computer->computerName . ".txt";
            echo '<div>Generating file ' . $fileName . '</div>';
            $data = implode(PHP_EOL, $matches);
            file_put_contents(
                $fileName,
                $data
            );
        }
    }

}

?>