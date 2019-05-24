<?php
require_once("config.inc.php");
require_once($cfg["path_bu"] . "/BUServiceDeskReport.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

$thing = null;
$buServiceDeskReport = new BUServiceDeskReport($thing);

$period = date(
    'Y-m',
    strtotime('last month')
);
if (isset($_REQUEST['period'])) {
    $period = $_REQUEST['period'];
}
$buServiceDeskReport->setPeriod($period);

$report = $buServiceDeskReport->getMonthlyReport();

$buMail = new BUMail($thing);

$senderEmail = CONFIG_SUPPORT_EMAIL;
$senderName = 'CNC Support Department';

$hdrs = array(
    'From'         => $senderEmail,
    'Subject'      => 'Monthly Service Desk Report - ' . $buServiceDeskReport->getMonthName(
        ) . ' ' . $buServiceDeskReport->getYear(),
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);

echo $report;

$buMail->mime->setHTMLBody($report);
$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset'  => 'UTF-8',
    'html_charset'  => 'UTF-8',
    'head_charset'  => 'UTF-8'
);
$body = $buMail->mime->get($mime_params);

$hdrs = $buMail->mime->headers($hdrs);

$buMail->putInQueue(
    $senderEmail,
    'monthlysdreport@' . CONFIG_PUBLIC_DOMAIN,
    $hdrs,
    $body
);

?>
