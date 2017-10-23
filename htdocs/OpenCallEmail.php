<?php
/**
 * notify each CNC users about their outstanding support calls
 *
 * called as scheduled task at given time every day
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */

ini_set('zend.ze1_compatibility_mode', 0);
require_once("config.inc.php");
require_once("../cnccode/business_classes/BUMail.inc.php");

define('OS_CALL_EMAIL_FROM_USER', 'sales@cnc-ltd.co.uk');
define('OS_CALL_EMAIL_SUBJECT', 'Open SR Activities');
define('FORMAT_MYSQL_UK_DATE', '%e/%c/%Y');


$domain = 'cnc-ltd.co.uk';

if (!$localDb = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
    echo 'Could not connect to mysql host ' . DB_HOST;
    exit;
}

/*
get list of engineers with open calls
*/
$localDb->select_db(DB_NAME);

$outputEmails = isset($_GET['outputEmails']);

$query =
    'SELECT
		DISTINCT CONCAT(firstName, " ", lastName) AS Engineer,
		cns_logname,
		cns_consno 
	 FROM callactivity  
     JOIN problem ON pro_problemno = caa_problemno
     JOIN callacttype ON cat_callacttypeno = caa_callacttypeno
     JOIN consultant ON caa_consno = cns_consno
     JOIN customer ON pro_custno = cus_custno
     WHERE caa_endtime = ""
     AND caa_date <= NOW()';                                                // and on-site

$resultSet = $localDb->query($query);

//this finds all the engineers that have open activities
/*
Send each engineer an email
*/
$engineers = $resultSet->fetch_all(MYSQLI_ASSOC);

$buMail = new BUMail($this);

$managers = [];
foreach ($engineers as $row) {
    $query =
        'SELECT caa_callactivityno, cus_name, CONCAT(firstName, " ", lastName) as name, caa_date, cns_manager ' .
        ' FROM callactivity ' .
        ' JOIN problem ON pro_problemno = caa_problemno' .
        ' JOIN callacttype ON cat_callacttypeno = caa_callacttypeno' .
        ' JOIN consultant ON caa_consno = cns_consno' .
        ' JOIN customer ON pro_custno = cus_custno' .
        ' WHERE caa_endtime = ""' .
        ' AND caa_date <= NOW()' .                                                                // in the past
        ' AND caa_consno = ' . $row['cns_consno'] .
        ' ORDER BY caa_date, pro_custno';

    $result = $localDb->query($query);
    $sender_name = "Call System";
    $sender_email = OS_CALL_EMAIL_FROM_USER;
    $headers = "From: " . $sender_name . " <" . $sender_email . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html";
    if (!$outputEmails) {
        ob_start();
    }
    ?>
    <HTML>
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
        while ($i = $result->fetch_row()) {
            $managerId = $i[4];
            if ($managerId && (strtotime($i[3]) <= strtotime('-5 days', time()))) {
                //this guy has a manager and this activity was open more than two days ago
                if (!isset($managers[$managerId])) {
                    $managers[$managerId] = (object)[
                        "minionConsultants" => []
                    ];
                }

                if (!isset($managers[$managerId]->minionConsultants[$row['cns_consno']])) {
                    $managers[$managerId]->minionConsultants[$row['cns_consno']] = (object)[
                        "name" => $row['Engineer'],
                        "openActivities" => []
                    ];
                }

                $managers[$managerId]->minionConsultants[$row['cns_consno']]->openActivities[] = $i;
            }
            ?>
            <TR>
                <TD>
                    <A href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/Activity.php?action=displayActivity&callActivityID=<?php print $i[0] ?>"><?php print $i[0] ?></A>
                </TD>
                <TD>
                    <?php print $i[1] ?>
                </TD>
                <TD>
                    <?php print $i[3] ?>
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

        $body = $buMail->mime->get();

        $hdrs = array(
            'From' => CONFIG_SALES_MANAGER_EMAIL,
            'Subject' => $emailSubject
        );

        $hdrs = $buMail->mime->headers($hdrs);

        $sendTo = $row['cns_logname'] . '@' . $domain;

        $sent = $buMail->send(
            $sendTo,
            $hdrs,
            $body
        );

        if ($sent) {
            echo 'email sent to ' . $row['Engineer'] . ' email: ' . $sendTo;
            echo '<br>';
        } else {
            echo 'failed to send email to ' . $row['Engineer'] . ' email: ' . $sendTo;
            echo '<br>';
        }
    }
}

foreach ($managers as $managerId => $manager) {
    //get information about the manager

    $managersRows = $localDb->query('SELECT cns_logname FROM consultant WHERE cns_consno = ' . $managerId);

    $managerRow = $managersRows->fetch_assoc();

    if (!$managerRow) {
        echo "<br>Manager with id $managerId not found, skipping<br>";
        continue;
    }

    $sender_name = "Call System";
    $sender_email = OS_CALL_EMAIL_FROM_USER;
    $headers = "From: " . $sender_name . " <" . $sender_email . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html";
    if (!$outputEmails) {
        ob_start();
    }
    ?>
    <HTML>
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
                        <A href="http://<?= $_SERVER['HTTP_HOST'] ?>/Activity.php?action=displayActivity&callActivityID=<?= $openActivity[0] ?>"><?= $openActivity[0] ?></A>
                    </TD>

                    <TD>
                        <?= $openActivity[1] ?>
                    </TD>
                    <TD>
                        <?= $openActivity[3] ?>
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

        $body = $buMail->mime->get();

        $hdrs = array(
            'From' => CONFIG_SALES_MANAGER_EMAIL,
            'Subject' => $emailSubject
        );

        $hdrs = $buMail->mime->headers($hdrs);

        $sendTo = $managerRow['cns_logname'] . '@' . $domain;

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
    }

}
?>
