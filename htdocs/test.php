<?php


require_once("config.inc.php");
require_once($cfg["path_ct"] . "/CTCustomerReviewMeeting.inc.php");

GLOBAL $cfg;

$buCustomerReviewMeeting = new BUCustomerReviewMeeting($that);
$startDate = (DateTime::createFromFormat(
    "m/Y",
    '01/2018'
))->modify('first day of this month ');
$endDate = (DateTime::createFromFormat(
    "m/Y",
    '01/2019'
))->modify('last day of this month');


echo $buCustomerReviewMeeting->generateSalesPdf(
    1939,
    $startDate,
    $endDate,
    '2019-02-14'
);


?>