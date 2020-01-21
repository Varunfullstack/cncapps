<?php

/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

use CNCLTD\LoggerCLI;
use Twig\Environment;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
global $db;

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../twig/internal');
$twig = new Environment($loader, ["cache" => __DIR__ . '/../cache']);

$logName = 'PasswordLevelCheck';
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
$debugMode = isset($options['d']);

$thing = null;
$logger->info('Start processing password items');
if (!$db->query(
    "UPDATE PASSWORD
  JOIN passwordservice
    ON passwordservice.`passwordServiceID` = password.`serviceID` SET password.level = passwordservice.defaultLevel
WHERE password.`level` < passwordservice.defaultLevel
AND archivedBy IS NULL"
)) {
    $logger->error('Failed to process password items:' . $db->Error);
} else {
    $logger->info($db::$Link_ID->affected_rows . ' Password items have been updated!');
}
