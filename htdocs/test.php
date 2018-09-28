<?php


require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
function getExpiryDate(DateTime $installDate,
                       DateTime $today = null
)
{

    if ($today == null) {
        $today = new DateTime();
    }

    //get next expiry date

    $expiryDay = (int)$installDate->format('d');
    $expiryMonth = (int)$installDate->format('m');

    // we need to check
    $expiryYear = (int)$today->format('Y');

    if ($expiryMonth < (int)$today->format('m') ||
        $expiryMonth == (int)$today->format('m') &&
        $expiryDay < (int)$today->format('d')) {
        $expiryYear += 1;
    }

    $nextExpiryDate = DateTime::createFromFormat(
        'Y-m-d',
        "$expiryYear-$expiryMonth-$expiryDay"
    );

    $difference = (int)$nextExpiryDate->diff($today)->format('%m');

    $expiryDate = clone $nextExpiryDate;
    if ($difference < 3) {
        $expiryDate->add(new DateInterval("P1Y"));
    }
    return [
        $nextExpiryDate,
        $today,
        $expiryDate
    ];

}


$array = [
    ["01/02/2013", "02/08/2017", "01/02/2018", "01/02/2018",],
    ["01/02/2013", "01/01/2018", "01/02/2018", "01/02/2019",],
    ["01/02/2013", "01/02/2018", "01/02/2018", "01/02/2019",],
    ["01/02/2013", "01/04/2018", "01/02/2019", "01/02/2019",],
    ["01/02/2013", "01/06/2018", "01/02/2019", "01/02/2019",],
    ["01/02/2013", "02/12/2018", "01/02/2019", "01/02/2020",],
    ["01/01/2010", "02/10/2011", "01/01/2012", "01/01/2013",],
    ["30/12/2010", "30/11/2011", "30/12/2011", "30/12/2012",],
    ["01/01/2011", "01/10/2011", "01/01/2012", "01/01/2012",],
    ["01/01/2011", "02/10/2011", "01/01/2012", "01/01/2013",],
];

foreach ($array

         as $item) {
    $result = getExpiryDate(
        DateTime::createFromFormat(
            'd/m/Y',
            $item[0]
        ),
        DateTime::createFromFormat(
            'd/m/Y',
            $item[1]
        )
    );

    /** @var DateTimeInterface $nextExpiryObtained */
    $nextExpiryObtained = $result[0];
    /** @var DateTimeInterface $today */
    $today = $result[1];
    /** @var DateTimeInterface $finalExpiryDate */
    $finalExpiryDate = $result[2];
    ?>
    <br>
    <br>
    <div>
        <div>Installation Date: <?= $item[0] ?></div>
        <div>Today's date: <?= $item[1] ?></div>
        <div>Next Expiry Expected <?= $item[2] ?></div>
        <div>Next Expiry Obtained <?= $nextExpiryObtained->format('d/m/Y') ?></div>
        <?php

        if ($result[0]->format('d/m/Y') === $item[2]) {
            ?>
            <div style="color: green;">
                Next Expiry Date Pass
            </div>
            <?php
        } else {
            ?>
            <div style="color: red;">
                Next Expiry Date Fail
            </div>
            <?php
        }
        ?>
        <div>
            Date diff <?= $nextExpiryObtained->diff($today)->format('%m') ?>
        </div>
        <div>
            Final expiry date expected: <?= $item[3] ?>
        </div>
        <div>
            Final expiry date obtained: <?= $finalExpiryDate->format('d/m/Y') ?>
        </div>
        <?php

        if ($finalExpiryDate->format('d/m/Y') === $item[3]) {
            ?>
            <div style="color: green;">
                Final Expiry Date Pass
            </div>
            <?php
        } else {
            ?>
            <div style="color: red;">
                Final Expiry Date Fail
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}


exit;

$buMail = new BUMail($thing);
$thing = null;
$buActivity = new BUActivity($thing);


$buMail->mime->setHTMLBody("<div>this is a test</div>");

$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset'  => 'UTF-8',
    'html_charset'  => 'UTF-8',
    'head_charset'  => 'UTF-8'
);
$body = $buMail->mime->get($mime_params);
$senderEmail = CONFIG_SUPPORT_EMAIL;

$toEmail = "guerreradelviento@gmail.com";

$cc = "fizdalf@gmail.com";

$bcc = "publixavi@gmail.com";

$toEmail = implode(
    ";",
    [$toEmail, $cc, $bcc]
);

$hdrs = array(
    'From'         => $senderEmail,
    'To'           => $toEmail,
    'Subject'      => 'Testeando',
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8',
    'Cc'           => $cc,
);
$recipients = "xavi@pavilionweb.co.uk";
$hdrs = $buMail->mime->headers($hdrs);

$buMail->putInQueue(
    $senderEmail,
    $recipients,
    $hdrs,
    $body
);
var_dump($buMail->sendQueue());

exit;


$results = new DataSet($thing);
$buActivity->getActivityByID(
    1640238,
    $results
);
$template = new Template(
    EMAIL_TEMPLATE_DIR,
    "remove"
);
$template->set_file(
    'page',
    'MonitoringEmail.inc.html'
);

$urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' .
    $results->getValue(DBECallActivity::callActivityID);
$activityRef = $results->getValue(DBEJCallActivity::problemID) . ' ' .
    $results->getValue(DBEJCallActivity::customerName);
$durationHours = common_convertHHMMToDecimal(
        $results->getValue(DBEJCallActivity::endTime)
    ) - common_convertHHMMToDecimal($results->getValue(DBEJCallActivity::startTime));

$awaitingCustomerResponse = null;

if ($results->getValue(DBEJCallActivity::requestAwaitingCustomerResponseFlag) == 'Y') {
    $awaitingCustomerResponse = 'Awaiting Customer';
} else {
    $awaitingCustomerResponse = 'Awaiting CNC';
}


$template->setVar(
    array(
        'activityRef'                 => $activityRef,
        'activityDate'                => $results->getValue(DBEJCallActivity::date),
        'activityStartTime'           => $results->getValue(DBEJCallActivity::startTime),
        'activityEndTime'             => $results->getValue(DBEJCallActivity::endTime),
        'activityTypeName'            => $results->getValue(DBEJCallActivity::activityType),
        'urlActivity'                 => $urlActivity,
        'userName'                    => $results->getValue(DBEJCallActivity::userName),
        'durationHours'               => round(
            $durationHours,
            2
        ),
        'requestStatus'               => true,
        'awaitingCustomerResponse'    => $awaitingCustomerResponse,
        'customerName'                => $results->getValue(DBEJCallActivity::customerName),
        'reason'                      => $results->getValue(DBEJCallActivity::reason),
        'CONFIG_SERVICE_REQUEST_DESC' => CONFIG_SERVICE_REQUEST_DESC
    )
);

$template->parse(
    'output',
    'page',
    true
);

$body = $template->get_var('output');


$toEmail = "xavi@pavilionweb.co.uk";

$senderEmail = CONFIG_SUPPORT_EMAIL;
$hdrs = array(
    'From'         => $senderEmail,
    'To'           => $toEmail,
    'Subject'      => 'Monitored SR ' . $results->getValue(
            DBEJCallActivity::problemID
        ) . ' For ' . $results->getValue(DBEJCallActivity::customerName),
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);
$body = preg_replace(
    '/[\x00-\x1F\x7F-\xFF]/',
    '',
    $body
);
$body = preg_replace(
    '/[\x00-\x1F\x7F]/',
    '',
    $body
);
$body = preg_replace(
    '/[\x00-\x1F\x7F]/u',
    '',
    $body
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


$buMail->putInQueue(
    $senderEmail,
    $toEmail,
    $hdrs,
    $body,
    true
);

$buMail->sendQueue();
