<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 13/04/2018
 * Time: 12:28
 */

/**
 * Check that the mail queue has no emails older than 15 minutes
 *
 * If it does then email graham and gary and Karim
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;

$logName = 'NonUKHolidaysEmails';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts = [
    "testDate:"
];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
global $cfg;
require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");

$dateToTest = @$options['testDate'];

$error = false;


if (!$dateToTest) {
    $dateToTest = Date('Y-m-d', strtotime("+3 days"));
}

$bankHolidays = common_getUKBankHolidays((new DateTime($dateToTest))->format('Y'));
// Exclude bank holidays and weekends

if (!in_array($dateToTest, $bankHolidays)) {
    echo 'The date tested is not a bank holidays .. do nothing';
    exit;
}

echo 'The date tested is a bank holiday... proceed';

if (!$db1 = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
    echo 'Could not connect to mysql host ' . DB_HOST;
    exit;
}
$db1->select_db(DB_NAME);


/*
Unprinted purchase orders email to Gary
*/
$query =
    "SELECT 
      con_email,
      con_first_name,
      con_last_name
    FROM
      contact 
      LEFT JOIN address 
        ON address.`add_custno` = con_custno 
        AND address.`add_siteno` = con_siteno 
    WHERE (supportLevel = 'support' or supportLevel = 'main')
      AND address.`add_active_flag` = 'Y'
      AND address.add_non_uk_flag = 'Y'";


$result = $db1->query($query);
$subject = "UK National Holiday - CNC ServiceDesk Availability";
$thing = null;
$buMail = new BUMail($thing);
foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
    global $twig;

    $body = $twig->render(
        '@customerFacing/NonUKHolidays/NonUKHolidays.html.twig',
        [
            "contactFirstName" => $row['con_first_name'],
            "date"             => Date('l jS F', strtotime($dateToTest))
        ]
    );

    $buMail->sendSimpleEmail($body, $subject, $row['con_email']);
}