<?php
use CNCLTD\LoggerCLI;
require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;

$logName = 'TeamPerformanceUpdate';
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
require_once($cfg["path_bu"] . "/BUTeamPerformance.inc.php");
$thing = null;
$buTeamPerformance = new BUTeamPerformance($thing);
$buTeamPerformance->update(
    date('Y'),
    date('m')
);
$buTeamPerformance->update(
    date(
        'Y',
        strtotime("-1 months")
    ),
    date(
        'm',
        strtotime("-1 months")
    )
)


//Uncomment this to update previous months
/*
$buTeamPerformance->update( 2015, 1 );
$buTeamPerformance->update( 2015, 2 );
$buTeamPerformance->update( 2015, 3 );
$buTeamPerformance->update( 2015, 4 );
$buTeamPerformance->update( 2015, 5 );
$buTeamPerformance->update( 2015, 6 );
$buTeamPerformance->update( 2015, 7 );
$buTeamPerformance->update( 2015, 8 );
$buTeamPerformance->update( 2015, 9 );
$buTeamPerformance->update( 2015, 10 );
$buTeamPerformance->update( 2015, 11 );
$buTeamPerformance->update( 2015, 12 );
$buTeamPerformance->update( 2016, 1 );
$buTeamPerformance->update( 2016, 2 );
$buTeamPerformance->update( 2016, 3 );
$buTeamPerformance->update( 2016, 4 );
$buTeamPerformance->update( 2016, 5 );
*/
?>