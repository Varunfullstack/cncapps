<?php
require_once("config.inc.php");
require_once($cfg["path_bu"] . "/BUServiceDeskReport.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

$buServiceDeskReport = new BUServiceDeskReport($this);

if ($_REQUEST['period']) {
    $period = $_REQUEST['period'];
} else {
    $period = date('Y-m', strtotime('last month'));
}
$buServiceDeskReport->setPeriod($period);

$report = $buServiceDeskReport->getMonthlyReport();

$buMail = new BUMail($this);

$senderEmail = CONFIG_SUPPORT_EMAIL;
$senderName = 'CNC Support Department';

$hdrs = array(
    'From' => $senderEmail,
    'Subject' => 'Monthly Service Desk Report - ' . $buServiceDeskReport->getMonthName() . ' ' . $buServiceDeskReport->getYear(),
    'Date' => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);

echo $report;

$buMail->mime->setHTMLBody($report);

$body = $buMail->mime->get();

$hdrs = $buMail->mime->headers($hdrs);

$buMail->putInQueue(
    $senderEmail,
    'monthlysdreport@' . CONFIG_PUBLIC_DOMAIN,
    $hdrs,
    $body,
    true
);

?>
