<?php
require_once("config.inc.php");
require_once($cfg["path_bu"] . "/BUTeamPerformance.inc.php");

$buTeamPerformance = new BUTeamPerformance($this);
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