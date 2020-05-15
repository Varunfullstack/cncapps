<?php
global $cfg;
require_once("config.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
$thing = null;


$period = isset($_REQUEST['period']) ? $_REQUEST['period'] : date(
    'Y-m',
    strtotime('last month')
);


$questionnaireReport = new \CNCLTD\QuestionnaireReportGenerator(1);
$questionnaireReport->setPeriod($period);

$report = null;
$report .= $questionnaireReport->getReport();

$buMail = new BUMail($thing);

$senderEmail = CONFIG_SALES_EMAIL;
$senderName = 'CNC Sales Department';

$toEmail = CONFIG_SALES_EMAIL;

$hdrs = array(
    'From'         => $senderEmail,
    'To'           => $toEmail,
    'Subject'      => 'Monthly Support Questionnaire Report - ' . $questionnaireReport->getMonthName(
        ) . ' ' . $questionnaireReport->getYear(),
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);


echo $report;

$buMail->mime->setHTMLBody($report);

$respondantsCsv = $buQuestionnaireReport->getRespondantsCsv();

$buMail->mime->addAttachment(
    $respondantsCsv,
    'text/csv',
    'respondants.csv',
    false
);

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
    $toEmail,
    $hdrs,
    $body
);
