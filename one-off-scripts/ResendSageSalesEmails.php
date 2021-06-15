<?php
global $cfg;
require_once(__DIR__ . "/../htdocs/config.inc.php");
require_once($cfg['path_bu'] . '/BUInvoice.inc.php');
/** @var dbSweetcode $db */
global $db;
//$logName = 'CheckAutoApproveExpensesOvertime';
//$logger  = new LoggerCLI($logName);
// increasing execution time to infinity...
ini_set('max_execution_time', 0);
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "p:";
$longopts  = [];
$options   = getopt($shortopts, $longopts);
if (!$options['p']) {
    throw new InvalidArgumentException('Please provide a valid YYYY-MM-DD date for the invoices printed date');
}
$dateString = $options['p'];
$date       = DateTimeImmutable::createFromFormat('Y-m-d', $dateString);
if (!$date) {
    throw new InvalidArgumentException('Please provide a valid YYYY-MM-DD date for the invoices printed date');
}
$statement = $db->preparedQuery(
    "SELECT group_concat(invhead.`inh_invno`) as ids FROM invhead WHERE inh_date_printed = ?",
    [
        [
            "type"  => "s",
            "value" => $date->format(DATE_MYSQL_DATE)
        ]
    ]
);
$allIds    = $statement->fetch_assoc()['ids'];
if (!$allIds) {
    return;
}
$thing     = null;
$buInvoice = new BUInvoice($thing);
$buInvoice->sendSageSalesEmail(explode(",", $allIds));