<?php
use CNCLTD\LoggerCLI;
use Twig\Environment;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
global $server_type;
require_once($cfg ['path_bu'] . '/BUMail.inc.php');
/** @var $db dbSweetcode */
global $db;
/** @var $twig Environment */
global $twig;
$logName = 'CheckPendingCompletionSR';
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
$db->query(
    'SELECT
  initialActivity.caa_callactivityno AS activityId,
  pro_problemno AS serviceRequestId,
  CONCAT(
    fix_consultant.firstName,
    \' \',
    fix_consultant.lastName
  ) AS fixedBy,
  DATEDIFF(
    CURRENT_DATE(),
    fixedActivity.`caa_date`
  ) AS outstandingFor,
  fixedActivity.`caa_date` AS fixedDate,
  customer.`cus_name` AS customerName,
  IF(
    fix_consultant.cns_manager = 0
    OR fix_consultant.cns_manager IS NULL,
    fix_consultant.`cns_consno`,
    fix_consultant.cns_manager
  ) AS managerID,
  manager.`cns_logname` AS managerEmail
FROM
  problem
  JOIN callactivity fixedActivity
    ON fixedActivity.`caa_problemno` = problem.`pro_problemno`
    AND fixedActivity.caa_callacttypeno = 57
  JOIN callactivity initialActivity
    ON initialActivity.`caa_problemno` = problem.`pro_problemno`
    AND initialActivity.caa_callacttypeno = 51
    AND initialActivity.caa_status = \'C\'
  LEFT JOIN project
    ON project.projectID = problem.pro_projectno
  LEFT JOIN consultant AS fix_consultant
    ON problem.pro_fixed_consno = fix_consultant.cns_consno
  INNER JOIN customer
    ON problem.pro_custno = customer.cus_custno
  LEFT JOIN consultant AS manager
    ON manager.`cns_consno` = IF(
      fix_consultant.cns_manager = 0
      OR fix_consultant.cns_manager IS NULL,
      fix_consultant.`cns_consno`,
      fix_consultant.cns_manager
    )
WHERE pro_contract_cuino IS NOT NULL
  AND pro_status = \'F\'
  AND pro_complete_date <= NOW()
  AND pro_total_activity_duration_hours >
  (SELECT
    hed_sr_autocomplete_threshold_hours
  FROM
    headert)
  AND DATEDIFF(
    CURRENT_DATE(),
    fixedActivity.`caa_date`
  ) >=
  (SELECT
    headert.closureReminderDays
  FROM
    headert
  LIMIT 1)
  AND pro_priority <> 5
ORDER BY managerID,
  outstandingFor DESC'
);
$lastManager = null;

$items = [];

while ($db->next_record(MYSQLI_ASSOC)) {
    if ($lastManager && $lastManager !== $db->Record['managerEmail']) {
        sendEmail($twig, $items, $lastManager);
        $items = [];
    }

    $items[] = [
        "activityLink"   => SITE_URL . "/SRActivity.php?action=displayActivity&callActivityID=" . $db->Record['activityId'],
        "serviceRequest" => $db->Record['serviceRequestId'],
        "customerName"   => $db->Record['customerName'],
        "fixedBy"        => $db->Record['fixedBy'],
        "fixedDate"      => $db->Record['fixedDate'],
        "outstandingFor" => $db->Record['outstandingFor'],
    ];
    $lastManager = $db->Record['managerEmail'];
}
sendEmail($twig, $items, $lastManager);

function sendEmail(Environment $twig, $items, $managerName)
{
    if (!count($items)) {
        return;
    }
    $thing = null;
    $buMail = new BUMail($thing);
    $senderEmail = CONFIG_SUPPORT_EMAIL;
    $searchLink = SITE_URL . "/Activity.php?action=search&activity%5B1%5D%5BcustomerID%5D=&customerString=&activity%5B1%5D%5BcallActivityID%5D=&activity%5B1%5D%5BproblemID%5D=&activity%5B1%5D%5BcallActTypeID%5D=&activity%5B1%5D%5BcontractCustomerItemID%5D=99&activity%5B1%5D%5Bstatus%5D=CHECKED_NON_T_AND_M&activity%5B1%5D%5Bpriority%5D=&activity%5B1%5D%5BbreachedSlaOption%5D=&activity%5B1%5D%5BrootCauseID%5D=&activity%5B1%5D%5BuserID%5D=&activity%5B1%5D%5BindividualActivitySpentTime%5D=&activity%5B1%5D%5BserviceRequestSpentTime%5D=&activity%5B1%5D%5BactivityText%5D=&activity%5B1%5D%5BfromDate%5D=&activity%5B1%5D%5BtoDate%5D=&Search=Search";
    // we have a different manager so we have to start a new email, and send the previous one
    $body = $twig->render('@internal/pendingCompletion.html.twig', ["searchLink" => $searchLink, "items" => $items]);
    $toEmail = $managerName . '@' . CONFIG_PUBLIC_DOMAIN;
    $subject = "You have " . count($items) . " service requests to manually complete";
    $hdrs = array(
        'From'         => $senderEmail,
        'To'           => $toEmail,
        'Subject'      => $subject,
        'Date'         => date("r"),
        'Content-Type' => 'text/html; charset=UTF-8'
    );

    $buMail->mime->setHTMLBody($body);

    $mime_params = array(
        'text_encoding' => '7bit',
        'text_charset'  => 'UTF-8',
        'html_charset'  => 'UTF-8',
        'head_charset'  => 'UTF-8'
    );
    $body = $buMail->mime->get($mime_params);

    $hdrs = $buMail->mime->headers($hdrs);

    $buMail->send(
        $toEmail,
        $hdrs,
        $body
    );
}
