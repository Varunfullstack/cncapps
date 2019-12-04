<?php
/**
 * notify each CNC users about their outstanding support calls
 *
 * called as scheduled task at given time every day
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */

ini_set('max_execution_time', 50000);

$thing = null;
require_once("config.inc.php");
require_once("../cnccode/business_classes/BUMail.inc.php");

define(
    'OS_CALL_EMAIL_FROM_USER',
    'sales@cnc-ltd.co.uk'
);
define(
    'OS_CALL_EMAIL_SUBJECT',
    'Open SR Activities'
);
define(
    'FORMAT_MYSQL_UK_DATE',
    '%e/%c/%Y'
);

$domain = 'cnc-ltd.co.uk';


//we are going to use this to add to the monitoring db
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
];

if (!$pdoDB = new PDO(
    $dsn,
    DB_USER,
    DB_PASSWORD,
    $options
)) {
    echo 'Could not connect to mysql host ' . DB_HOST;
    exit;
}


class EngineerActivity
{
    public $activityId;
    public $customerName;
    public $engineerName;
    public $engineerLogName;
    public $engineerId;
    public $activityDate;
    public $engineerManagerId;
}

$outputEmails = isset($_REQUEST['outputEmails']);

$query =
    'SELECT
  caa_callactivityno as activityId,
  cus_name as customerName,
  CONCAT(firstName, " ", lastName) AS engineerName,
  cns_logname as engineerLogName,
  cns_consno as engineerId,
  caa_date as activityDate,
  cns_manager as engineerManagerId
FROM 
  callactivity 
  JOIN problem
    ON pro_problemno = caa_problemno
  JOIN callacttype
    ON cat_callacttypeno = caa_callacttypeno
  JOIN consultant
    ON caa_consno = cns_consno
  JOIN customer
    ON pro_custno = cus_custno
WHERE caa_date <= NOW()
  AND (
    caa_endtime = ""
    OR caa_endtime IS NULL
  )
ORDER BY engineerName,
  caa_date,
  pro_custno';                                                // and on-site

$pdoStatement = $pdoDB->query($query);

//this finds all the engineers that have open activities
/*
Send each engineer an email
*/
/** @var EngineerActivity[] $activities */
$activities = $pdoStatement->fetchAll(PDO::FETCH_CLASS, EngineerActivity::class);
$buMail = new BUMail($thing);
$engineers = array_reduce(
    $activities,
    function ($acc, EngineerActivity $engineerActivity) {
        if (!isset($acc[$engineerActivity->engineerId])) {
            $acc[$engineerActivity->engineerId] = [];
        }
        $acc[$engineerActivity->engineerId][] = $engineerActivity;
        return $acc;
    },
    []
);


$managers = [];
/**
 * @var int $engineerId
 * @var EngineerActivity[] $engineerActivities
 */
foreach ($engineers as $engineerId => $engineerActivities) {
    $logName = null;
    $engineerName = null;
    if (!$outputEmails) {
        ob_start();
    }
    ?>
    <HTML lang="en">
    <P>
        Your following activities are open (no end time entered):
    </P>
    <TABLE>
        <TR>
            <TD>
                Ref
            </TD>
            <TD>
                Customer
            </TD>
            <TD>
                Date
            </TD>
        </TR>
        <?php
        foreach ($engineerActivities as $engineerActivity) {
            if (!$logName) {
                $logName = $engineerActivity->engineerLogName;
            }
            if (!$engineerName) {
                $engineerName = $engineerActivity->engineerName;
            }


            $managerId = $engineerActivity->engineerManagerId;
            if ($managerId && (strtotime($engineerActivity->activityDate) <= strtotime(
                        '-5 days',
                        time()
                    ))) {
                //this guy has a manager and this activity was open more than 5 days ago
                if (!isset($managers[$managerId])) {
                    $managers[$managerId] = (object)[
                        "minionConsultants" => []
                    ];
                }

                if (!isset($managers[$managerId]->minionConsultants[$engineerId])) {
                    $managers[$managerId]->minionConsultants[$engineerId] = (object)[
                        "name"           => $engineerActivity->engineerName,
                        "openActivities" => []
                    ];
                }

                $managers[$managerId]->minionConsultants[$engineerId]->openActivities[] = $engineerActivity;
            }
            ?>
            <TR>
                <TD>
                    <A href="<?= SITE_URL ?>/Activity.php?action=displayActivity&callActivityID=<?php print $engineerActivity->activityId ?>"><?php print $engineerActivity->activityId ?></A>
                </TD>
                <TD>
                    <?php print $engineerActivity->customerName ?>
                </TD>
                <TD>
                    <?php print $engineerActivity->activityDate ?>
                </TD>
            </TR>
            <?php
        }
        ?>
    </TABLE>
    </HTML>
    <?php

    if (!$outputEmails) {
        $body = ob_get_contents();
        ob_end_clean();


        $emailSubject = "You have open requests";

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $sendTo = $logName . '@' . $domain;
        $hdrs = array(
            'To'           => $sendTo,
            'From'         => CONFIG_SUPPORT_EMAIL,
            'Subject'      => $emailSubject,
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $hdrs = $buMail->mime->headers($hdrs);
        $sent = $buMail->send(
            $sendTo,
            $hdrs,
            $body
        );

        if ($sent) {
            echo 'email sent to ' . $engineerName . ' email: ' . $sendTo;
            echo '<br>';
        } else {
            echo 'failed to send email to ' . $engineerName . ' email: ' . $sendTo;
            echo '<br>';
        }
    }
}

foreach ($managers as $managerId => $manager) {
    //get information about the manager

    $managersRows = $pdoDB->query('SELECT cns_logname FROM consultant WHERE cns_consno = ' . $managerId);

    $managerRow = $managersRows->fetch(PDO::FETCH_ASSOC);

    if (!$managerRow) {
        echo "<br>Manager with id $managerId not found, skipping<br>";
        continue;
    }

    if (!$outputEmails) {
        ob_start();
    }
    ?>
    <HTML lang="en">
    <style>
        table, th, td {
            border: 1px solid black;
        }
    </style>
    <P>
        The following consultants under your management have open activities for 2 days or more
    </P>
    <TABLE>
        <TR>
            <td>
                Consultant Name
            </td>
            <TD>
                Ref
            </TD>
            <TD>
                Customer
            </TD>
            <TD>
                Date
            </TD>
        </TR>
        <?php
        foreach ($manager->minionConsultants as $minionConsultant) {
            $isFirst = true;
            /** @var EngineerActivity $openActivity */
            foreach ($minionConsultant->openActivities as $openActivity) {

                ?>
                <TR>
                    <?php
                    if ($isFirst) { ?>
                        <TD rowspan="<?= count($minionConsultant->openActivities); ?>">
                            <?= $minionConsultant->name ?>
                        </TD>
                        <?php
                    }
                    ?>
                    <TD>
                        <A href="<?= SITE_URL ?>/Activity.php?action=displayActivity&callActivityID=<?= $openActivity->activityId ?>"><?= $openActivity->activityId ?></A>
                    </TD>

                    <TD>
                        <?= $openActivity->customerName ?>
                    </TD>
                    <TD>
                        <?= $openActivity->activityDate ?>
                    </TD>
                </TR>
                <?php
                if ($isFirst) {
                    $isFirst = false;
                }
            }
        }
        ?>
    </TABLE>
    </HTML>
    <?php

    if (!$outputEmails) {
        $body = ob_get_contents();
        ob_end_clean();

        $emailSubject = "Your managed engineers have open requests";


        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);
        $sendTo = $managerRow['cns_logname'] . '@' . $domain;
        $hdrs = array(
            'To'           => $sendTo,
            'From'         => CONFIG_SUPPORT_EMAIL,
            'Subject'      => $emailSubject,
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        $hdrs = $buMail->mime->headers($hdrs);
        $sent = $buMail->send(
            $sendTo,
            $hdrs,
            $body
        );

        if ($sent) {
            echo 'email sent to ' . $managerRow['cns_logname'] . ' email: ' . $sendTo;
        } else {
            echo 'failed to send email to ' . $managerRow['cns_logname'] . ' email: ' . $sendTo;
        }
        echo '<br>';
    }

}
?>
