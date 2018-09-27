<?php
require_once("config.inc.php");
require_once($cfg["path_bu"] . "/BUQuestionnaireReport.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

$buQuestionnaireReport = new BUQuestionnaireReport($this);

if ($_REQUEST['period']) {
    $period = $_REQUEST['period'];
} else {
    $period = date('Y-m', strtotime('last month'));
}
$buQuestionnaireReport->setPeriod($period);
$buQuestionnaireReport->setQuestionnaireID(1); // CNC support

$prizewinner = $buQuestionnaireReport->setPrizewinner();

$report = '<P><A HREF="' . $_SERVER['HTTP_HOST'] . '/Prizewinner.php">' . $prizewinner . '</A></P>';

$report .= $buQuestionnaireReport->getReport();

$buMail = new BUMail($this);

$senderEmail = CONFIG_SALES_EMAIL;
$senderName = 'CNC Sales Department';

$toEmail = CONFIG_SALES_EMAIL;

$hdrs = array(
    'From' => $senderEmail,
    'To' => $toEmail,
    'Subject' => 'Monthly Support Questionnaire Report - ' . $buQuestionnaireReport->getMonthName() . ' ' . $buQuestionnaireReport->getYear(),
    'Date' => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);


echo $report;

$buMail->mime->setHTMLBody($report);

$respondantsCsv = $buQuestionnaireReport->getRespondantsCsv();

$buMail->mime->addAttachment($respondantsCsv, 'text/csv', 'respondants.csv', false);

$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset' => 'UTF-8',
    'html_charset' => 'UTF-8',
    'head_charset' => 'UTF-8'
);
$body = $buMail->mime->get($mime_params);


$hdrs = $buMail->mime->headers($hdrs);

$buMail->putInQueue(
    $senderEmail,
    $toEmail,
    $hdrs,
    $body
);

?>
