<?php
/**
 * Created by PhpStorm.
 * User: fizda
 * Date: 16/01/2018
 * Time: 17:04
 */

 // I don´t like conflicts
 
 
if (!isset($argv[0])) {
    die('This script may only be called from the os command line');
}

$dat = getrusage();
define('PHP_TUSAGE', microtime(true));
define('PHP_RUSAGE', $dat["ru_utime.tv_sec"] * 1e6 + $dat["ru_utime.tv_usec"]);

function getCpuUsage()
{
    $dat = getrusage();
    $dat["ru_utime.tv_usec"] = ($dat["ru_utime.tv_sec"] * 1e6 + $dat["ru_utime.tv_usec"]) - PHP_RUSAGE;
    $time = (microtime(true) - PHP_TUSAGE) * 1000000;

    // cpu per request
    if ($time > 0) {
        $cpu = sprintf("%01.2f", ($dat["ru_utime.tv_usec"] / $time) * 100);
    } else {
        $cpu = '0.00';
    }

    return $cpu;
}

date_default_timezone_set('Europe/London');
$start_time = date('Y-m-d H:i:s');

if(!file_exists($argv[1]))

include_once $argv[1];

$end_time = date('Y-m-d H:i:s');

//we are going to use this to add to the monitoring db
$dsn = 'mysql:host=localhost;dbname=cncappsdev';
$DB_USER = "webuser";
$DB_PASSWORD = "CnC1988";
$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
];
$db = new PDO($dsn, $DB_USER, $DB_PASSWORD, $options);

$statement = $db->prepare('INSERT INTO taskLog(description, startedAt, finishedAt, maxCpuUsage, maxMemoryUsage) VALUES( :description, :startedAt, :finishedAt, :maxCpuUsage, :maxMemoryUsage ) ');

$statement->bindValue(':description', $argv[1]);
$statement->bindValue(':startedAt', $start_time);
$statement->bindValue(':finishedAt', $end_time);
$statement->bindValue(':maxCpuUsage', getCpuUsage());
$statement->bindValue(':maxMemoryUsage', memory_get_peak_usage(true));

$statement->execute();