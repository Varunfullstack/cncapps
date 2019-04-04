<?php
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUCustomerReviewMeeting.inc.php');
$thing = null;
$buCustomerReviewMeeting = new BUCustomerReviewMeeting($thing);
$buCustomerReviewMeeting->generateEmails();
echo "Finished sending customer review meeting emails";
?>