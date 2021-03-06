<?php

use CNCLTD\LoggerCLI;
use CNCLTD\PendingExpense;
use CNCLTD\PendingOvertime;
use Twig\Environment;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUExpense.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');
global $db;
/** @var $twig Environment */
global $twig;
$logName = 'SendUnapprovedExpenseOvertimeWarningEmails';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts = [];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$thing = null;
$dbeProblem = new DBEProblem($hing);
$dbeProblem->getToCheckCriticalFlagSRs();

$buHeader = new BUHeader($thing);
$dsHeader = new DataSet($thing);
$buHeader->getHeader($dsHeader);
$daysInAdvance = $dsHeader->getValue(DBEHeader::daysInAdvanceExpensesNextMonthAlert);
$logger->info("Days in advance $daysInAdvance");
$logger->info('Next processing date: ' . $dsHeader->getValue(DBEHeader::expensesNextProcessingDate));
$nextProcessingDate = DateTime::createFromFormat(
    DATE_MYSQL_DATE,
    $dsHeader->getValue(DBEHeader::expensesNextProcessingDate)
);
if (!$nextProcessingDate) {
    $logger->error('The next processing date has not been set. Stopping process');
    exit;
}
$expensesNextProcessingDateStart = (clone $nextProcessingDate)->sub(new DateInterval('P' . $daysInAdvance . "D"));
$logger->info('Start date: ' . $expensesNextProcessingDateStart->format(DATE_MYSQL_DATE));

$today = new DateTime();
if ($today < $expensesNextProcessingDateStart) {
    $logger->info('It is not the date yet, stop processing');
    exit;
}


$approvers = [];
$buExpense = new BUExpense($thing);
// we have to group these items by the approver ID
foreach (getPendingToApproveOvertimeItems() as $pendingToApproveItem) {
    if (!isset($approvers[$pendingToApproveItem->approverId])) {
        $approvers[$pendingToApproveItem->approverId] = [
            "overtimeActivities" => [],
            "expenses"           => [],
            "approverName"       => $pendingToApproveItem->approverName,
            "approverUserName"   => $pendingToApproveItem->approverUserName,
            "processingDate"     => $nextProcessingDate->format('d-m-Y'),
            "serverURL"          => SITE_URL
        ];
    }
    $approvers[$pendingToApproveItem->approverId]['overtimeActivities'][] = $pendingToApproveItem;
}

$buExpense = new BUExpense($thing);
foreach (getPendingToApproveExpenseItems() as $pendingToApproveExpenseItem) {
    if (!isset($approvers[$pendingToApproveExpenseItem->approverId])) {
        $approvers[$pendingToApproveExpenseItem->approverId] = [
            "overtimeActivities" => [],
            "expenses"           => [],
            "approverName"       => $pendingToApproveExpenseItem->approverName,
            "approverUserName"   => $pendingToApproveExpenseItem->approverUserName,
            "processingDate"     => $nextProcessingDate->format('d-m-Y'),
            "serverURL"          => SITE_URL
        ];
    }

    $approvers[$pendingToApproveExpenseItem->approverId]['expenses'][] = $pendingToApproveExpenseItem;
}

$buMail = new BUMail($thing);
foreach ($approvers as $approver) {
    $body = $twig->render('@internal/unapprovedExpenseOvertimeWarningEmail.html.twig', $approver);
    $fromEmail = CONFIG_SALES_EMAIL;
    $toEmail = $approver['approverUserName'] . '@' . CONFIG_PUBLIC_DOMAIN;
    $subject = "You have overtime or expenses requests that are waiting to be approved.";
    $hdrs = array(
        'From'    => $fromEmail,
        'To'      => $toEmail,
        'Subject' => $subject
    );
    $mime = new Mail_mime();
    $mime->setHTMLBody($body);
    $mime_params = array(
        'text_encoding' => '7bit',
        'text_charset'  => 'UTF-8',
        'html_charset'  => 'UTF-8',
        'head_charset'  => 'UTF-8'
    );
    $body = $mime->get($mime_params);
    $hdrs = $mime->headers($hdrs);
    $buMail->send(
        $toEmail,
        $hdrs,
        $body
    );
}

// SICKNESS...

$db->query(
    "SELECT
  sicknessTest.*,
  consultant.`cns_name` AS staffName
FROM
  (SELECT
    userID,
    SUM(IF(sickTime = 'F', 1, 0.5)) AS sickDaysThisYear,
    (SELECT
      SUM(IF(sickTime = 'F', 1, 0.5))
    FROM
      user_time_log b
    WHERE b.`userID` = a.`userID`
      AND b.`loggedDate` BETWEEN CURDATE() - INTERVAL 30 DAY
      AND CURDATE() AND sickTime IS NOT NULL) AS sickDaysLast30Days,
      
    yearlySicknessThresholdWarning
  FROM
    user_time_log a 
    JOIN headert
  WHERE a.`loggedDate` >= DATE_FORMAT(NOW(), '%Y-01-01')
    AND sickTime IS NOT NULL
    AND
    (SELECT
      COUNT(*)
    FROM
      user_time_log b
    WHERE b.`userID` = a.`userID`
      AND b.`loggedDate` BETWEEN CURDATE() - INTERVAL 30 DAY
      AND CURDATE() AND sickTime IS NOT NULL)
  GROUP BY userID) sicknessTest
  LEFT JOIN consultant ON sicknessTest.userID = consultant.`cns_consno`
WHERE sickDaysThisYear >= yearlySicknessThresholdWarning"
);
$sickPeople = $db->fetchAll(MYSQLI_ASSOC);
$body = $twig->render(
    '@internal/sickReportEmail.html.twig',
    [
        "sickPeople"                     => $sickPeople,
        "yearlySicknessThresholdWarning" => @$sickPeople[0]['yearlySicknessThresholdWarning']
    ]
);
$fromEmail = CONFIG_SUPPORT_EMAIL;
$toEmail = "payroll@" . CONFIG_PUBLIC_DOMAIN;
$subject = "Staff Sickness Report For Payroll";
$hdrs = array(
    'From'    => $fromEmail,
    'To'      => $toEmail,
    'Subject' => $subject
);
$mime = new Mail_mime();
$mime->setHTMLBody($body);
$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset'  => 'UTF-8',
    'html_charset'  => 'UTF-8',
    'head_charset'  => 'UTF-8'
);
$body = $mime->get($mime_params);
$hdrs = $mime->headers($hdrs);
$buMail->send(
    $toEmail,
    $hdrs,
    $body
);

function getPendingToApproveExpenseItems()
{
    /** @var $db dbSweetcode */
    global $db;
    $query = "SELECT
  expense.`exp_expenseno` AS id,
  consultant.cns_name AS staffName,
  consultant.`cns_consno` AS userId,
  exp_callactivityno AS activityId,
  callactivity.`caa_problemno` AS serviceRequestId,
  caa_date as `dateSubmitted`,
  expensetype.`ext_desc` AS expenseTypeDescription,
  expense.`exp_expensetypeno` AS expenseTypeId,
  expense.`exp_value` AS `value`,
  project.`description` AS projectDescription,
  project.`projectID` AS projectId,
  approver.cns_name AS approverName,
   approver.cns_logname AS approverUserName,
  approver.cns_consno AS approverId
FROM
  expense
  LEFT JOIN `callactivity`
    ON `callactivity`.`caa_callactivityno` = expense.`exp_callactivityno`
  LEFT JOIN consultant
    ON callactivity.`caa_consno` = consultant.`cns_consno`
  LEFT JOIN `expensetype`
    ON `expensetype`.`ext_expensetypeno` = expense.`exp_expensetypeno`
  LEFT JOIN problem
    ON problem.`pro_problemno` = callactivity.`caa_problemno`
  LEFT JOIN project
    ON project.`projectID` = problem.`pro_projectno`
  JOIN consultant approver
    ON approver.`cns_consno` = consultant.`expenseApproverID`
WHERE caa_endtime
  AND caa_endtime IS NOT NULL
  AND expense.`approvedBy` IS NULL
  AND expense.`deniedReason` IS NULL
  AND exp_exported_flag <> \"Y\"";

    $result = $db->preparedQuery($query, []);
    $toReturn = [];
    while ($object = $result->fetch_object(PendingExpense::class)) {
        $toReturn[] = $object;
    }
    return $toReturn;
}


/**
 * @return PendingOvertime[]
 */
function getPendingToApproveOvertimeItems()
{
    /** @var $db dbSweetcode */
    global $db;
    $pendingToApproveOvertimeQuery =
        "SELECT caa_date               as dateSubmitted,
       caa_callactivityno              as activityId,
       caa_problemno                   as serviceRequestId,
       consultant.cns_name             as staffName,
       consultant.`cns_consno`         AS userId,
       project.`description`           AS projectDescription,
       project.`projectID`             AS projectId,
       approver.cns_name               as approverName,
       approver.cns_consno             as approverId,
       approver.cns_logname            as approverUserName,
       getOvertime(caa_callactivityno) as overtimeValue
FROM callactivity
         JOIN problem
              ON pro_problemno = caa_problemno
         JOIN callacttype
              ON caa_callacttypeno = cat_callacttypeno
         JOIN customer
              ON pro_custno = cus_custno
         JOIN consultant
              ON caa_consno = cns_consno
         join consultant approver
              on approver.cns_consno = consultant.expenseApproverID
         join headert
              on headert.`headerID` = 1
         left join project
                   on project.`projectID` = problem.`pro_projectno`
WHERE caa_endtime
  and caa_endtime is not null
  and (caa_status = 'C'
    OR caa_status = 'A')
  AND caa_ot_exp_flag = 'N'
  and callactivity.`overtimeApprovedBy` is null
  and callactivity.overtimeDeniedReason is null
  AND getOvertime(caa_callactivityno) * 60 >= `minimumOvertimeMinutesRequired`
  and submitAsOvertime
  AND (caa_endtime <> caa_starttime)
  AND callacttype.engineerOvertimeFlag = 'Y'";
    $result = $db->preparedQuery($pendingToApproveOvertimeQuery, []);
    $toReturn = [];
    while ($object = $result->fetch_object(PendingOvertime::class)) {
        $toReturn[] = $object;
    }
    return $toReturn;
}


