<?php
/**
 * Action Alert Email controller
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUSecondsite.inc.php');
$thing = null;
$testRun = !!@$_REQUEST['testRun'];
$customerItemID = @$_REQUEST['customerItemID'];
$buSecondsite = new BUSecondsite($thing);

set_time_limit(0); // unlimited execution time


$buSecondsite->validateBackups(
    $customerItemID,
    $testRun
);

$template = new Template(
    EMAIL_TEMPLATE_DIR,
    "remove"
);

$template->set_file(
    'page',
    'secondSiteCompletedEmail.inc.html'
);

$template->set_block(
    'page',
    'logBlock',
    'logs'
);

foreach ($buSecondsite->log as $logEntry) {

    if ($logEntry['type'] == BUSecondsite::LOG_TYPE_SUCCESS) {
        continue; // don't report successes in detail
    }
    $class = "";
    switch ($logEntry['type']) {

        case BUSecondsite::LOG_TYPE_ERROR_INCOMPLETE:
            $class = 'incomplete';
            break;

        case BUSecondsite::LOG_TYPE_ERROR_PATH_MISSING:
            $class = 'pathMissing';
            break;

        case BUSecondsite::LOG_TYPE_ERROR_NO_IMAGE:
            $class = 'noImage';
            break;
    }

    $template->set_var(
        array(
            'message' => $logEntry['message'],
            'class'   => $class
        )
    );
    $template->parse(
        'logs',
        'logBlock',
        true
    );

} // end foreach

$template->set_block(
    'page',
    'delayedCheckServerBlock',
    'delayedServers'
);

$servers = $buSecondsite->getDelayedCheckServers();
foreach ($servers as $server) {

    $template->set_var(
        array(
            'customerName' => $server['cus_name'],
            'serverName'   => $server['serverName'],
            'delayDays'    => $server['imageDelayDays'],
            'delayUser'    => $server['delayUser'],
            'delayDate'    => $server['imageDelayDate']
        )
    );
    $template->parse(
        'delayedServers',
        'delayedCheckServerBlock',
        true
    );

}

$template->set_block(
    'page',
    'suspendedCheckServerBlock',
    'suspendedServers'
);

$servers = $buSecondsite->getSuspendedCheckServers();

if (!count($servers)) {
    $template->setVar(
        'suspendedServers',
        "",
        false
    );
}

foreach ($servers as $server) {

    $template->set_var(
        array(
            'customerName'       => $server['cus_name'],
            'serverName'         => $server['serverName'],
            'suspendedUntilDate' => $server['suspendedUntilDate'],
            'suspendUser'        => $server['suspendUser'],
            'suspendedDate'      => $server['suspendedDate']
        )
    );
    $template->parse(
        'suspendedServers',
        'suspendedCheckServerBlock',
        true
    );
}

$template->set_block(
    'page',
    'excludedLocalServerBlock',
    'excludedLocalServers'
);

$servers = $buSecondsite->getExcludedLocalServers();

foreach ($servers as $server) {

    $template->set_var(
        array(
            'customerName' => $server['cus_name'],
            'serverName'   => $server['serverName']
        )
    );
    $template->parse(
        'excludedLocalServers',
        'excludedLocalServerBlock',
        true
    );

}

$template->setVar(
    array(
        'serverCount'          => $buSecondsite->serverCount,
        'serverErrorCount'     => $buSecondsite->serverErrorCount,
        'suspendedServerCount' => $buSecondsite->suspendedServerCount,
        'imageCount'           => $buSecondsite->imageCount,
        'imageErrorCount'      => $buSecondsite->imageErrorCount,
        'successCount'         => $buSecondsite->imagePassesCount,
        'successRate'          => round(
            $buSecondsite->imageCount ? $buSecondsite->imagePassesCount / $buSecondsite->imageCount * 100 : 0,
            1
        )
    )
);

$template->parse(
    'output',
    'page',
    true
);

$html = $template->get_var('output');
$subject = 'Offsite Backup Validation Completed';

if ($testRun) {
    $subject = 'Offsite Backup Test Run Completed';
}


$senderEmail = CONFIG_SUPPORT_EMAIL;
$senderName = 'CNC Support Department';

$toEmail = '2sprocesscompleted@' . CONFIG_PUBLIC_DOMAIN;

$hdrs = array(
    'To'           => $toEmail,
    'From'         => $senderEmail,
    'Subject'      => $subject,
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);

$buMail = new BUMail($thing);

$buMail->mime->setHTMLBody($html);

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

echo $html; // and output to page
?>