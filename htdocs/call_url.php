#!/usr/local/bin/php
<?php
/**
 * Communcate with a network from the command line.
 *
 * Cross-platform PHP solution to allow a simple call to a given URL.
 * Developed to allow PHP scripts to be executed from Windows Scheduler.
 *
 * LICENSE: Not to be used outside of Sweet Code Ltd
 *
 * @copyright  2008 Sweet Code Ltd
 * @license    http://www.sweetcode.co.uk/license/1_0.txt   Sweet Code License 1.0
 * @version    $Id:$1.0
 * @link       http://
 * @since      File available since Release
 */
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

set_time_limit(0);
date_default_timezone_set('Europe/London');

$ch = curl_init($argv[1]);                                        // create handle using passed URL

curl_setopt($ch, CURLOPT_TIMEOUT, 1000);                // 20 second timeout

$start_time = date('Y-m-d H:i:s');

$response = curl_exec($ch);

$end_time = date('Y-m-d H:i:s');

$error = curl_error($ch);

/* initialise result array */
$result = array('header' => '',
    'start_time' => '',
    'end_time' => '',
    'body' => '',
    'curl_error' => '',
    'http_code' => '',
    'last_url' => '');

if ($error != "") {
    $result['curl_error'] = $error;
} else {
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $result['start_time'] = $start_time;
    $result['end_time'] = $end_time;
    $result['header'] = substr($response, 0, $header_size);
    $result['body'] = substr($response, $header_size);                        // body is remainder
    $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $result['last_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
}

curl_close($ch);                                                                // free handle


/*
$handle = fopen( 'E:\\htdocs\\service_desk_monitor_log.htm', 'a');
fwrite( $handle, '<PRE>' . print_r( $result, true ) . '</PRE>');
fclose( $handle );
*/
if ($result['curl_error']) {
    die('CURL Error: ' . $result['curl_error']);
}
if ($result['http_code'] != 200) {
    die('HTTP Error: ' . $result['http_code']);
}

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


?>
