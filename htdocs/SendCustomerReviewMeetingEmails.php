<?php
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUCustomerReviewMeeting.inc.php');
$buCustomerReviewMeeting = new BUCustomerReviewMeeting($this);
$buCustomerReviewMeeting->generateEmails();
echo "Finished sending customer review meeting emails";
?>