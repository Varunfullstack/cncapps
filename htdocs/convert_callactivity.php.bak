<?php
/**
 * One-off conversion script for call activity contacts
 *
 * Bug ref 208
 *    routine to copy contact details from calls to activities following addition of new contact field
 * to callactivity table.
 * If current call contact not at site then set activity contact to default site delivery contact.
 */
require_once("config.inc.php");
$db = new dbSweetcode();        // select from callactivity/call
$db2 = new dbSweetcode();        // this one for joining text lines
// get all rows from notepad where type = CAP (call activity problem)
set_time_limit(20 * 60);
$db->query(
    'SELECT
	call_contact.con_siteno,		# the current call contact address no
	callt.cal_ctactno,
	callactivity.caa_callactivityno,
	callactivity_address.add_siteno,
	callactivity_address.add_del_contno
FROM
	callactivity 
	JOIN callt ON caa_callno = cal_callno
	JOIN contact AS call_contact ON cal_ctactno = call_contact.con_contno 
	JOIN address AS callactivity_address ON caa_siteno = add_siteno AND cal_custno = add_custno'
);
while ($db->next_record()) {
    // the current call contact IS at the actiivity site so can be used as is
    if ($db->Record['con_siteno'] == $db->Record['add_siteno']) {
        $activityContactNo = $db->Record['cal_ctactno'];
    } else {
        // need to use site default contact
        $activityContactNo = $db->Record['add_del_contno'];
    }
    $db2->query(
        'UPDATE callactivity SET caa_contno = ' . $activityContactNo .
        ' WHERE caa_callactivityno =' . $db->Record['caa_callactivityno']
    );
}
?>