<?php
/**
* One-off conversion script for new help desk call activity system
*
*
* Bug ref 208
*	routine to link all activities for the same call (now project) using new parent_callactivityno
*/
require_once("config.inc.php");
$db = new dbSweetcode();		// select from callactivity/call
$db2 = new dbSweetcode();		// this one for joining text lines
set_time_limit(20*60);
$db->query(
	"SELECT *
		FROM callactivity
	WHERE
		projectID <> 0
		ORDER BY projectID, caa_date, caa_starttime"
);

$lastProjectID = 99;
$last_callactivityno = 99;

while ($db->next_record()){

	echo "Project: ". $db->Record['projectID'] . " Date: " . $db->Record['caa_date'] . "<BR/>";
/*
 	if ( $db->Record['projectID'] == $lastProjectID ){
		$db2->query(
			'UPDATE callactivity SET caa_parent_callactivityno = '.$last_callactivityno .
			' WHERE caa_callactivityno ='. $db->Record['caa_callactivityno']
		);
	}
*/
	$last_callactivityno = $db->Record['caa_callactivityno'];
	$lastProjectID = $db->Record['projectID'];
	
}
?>