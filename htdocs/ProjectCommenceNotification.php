<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 10/10/2018
 * Time: 14:58
 */

require_once("config.inc.php");

require_once($cfg["path_dbe"] . "/DBEProject.inc.php");
require_once($cfg["path_dbe"] . '/DBECustomer.inc.php');
require_once($cfg["path_bu"] . "/BUMail.inc.php");

$today = new DateTime();

$thisYearHolidays = common_getUKBankHolidays($today->format('Y'));
$nextYearHolidays = common_getUKBankHolidays(((int)$today->format('Y')) + 1);

$holidays = array_merge(
    $thisYearHolidays,
    $nextYearHolidays
);

$workingDaysTotal = 3;

$workingDaysPassed = 0;

$date = clone  $today;
while ($workingDaysPassed < $workingDaysTotal) {

    $date->add(new DateInterval('P1D'));
    if (!in_array(
            $date->format('Y-m-d'),
            $holidays
        ) && $date->format('N') <= 5) {
        $workingDaysPassed++;
    }
}
echo "<div>Next Date is : " . $date->format('Y-m-d') . "</div>";
$thing = null;

$dbeProject = new DBEProject($thing);

$dbeProject->setValue(
    DBEProject::commenceDate,
    $date->format('Y-m-d')
);
$dbeProject->getRowsByColumn(DBEProject::commenceDate);
$template = new Template (
    EMAIL_TEMPLATE_DIR,
    "remove"
);

$template->set_file(
    'page',
    'ProjectCommenceNotificationEmail.html'
);

$dbeCustomer = new DBECustomer($thing);

while ($dbeProject->fetchNext()) {
    echo "<div>We have a project to send: " . $dbeProject->getValue(DBEProject::description) . "</div>";
    $buMail = new BUMail($this);
    $senderEmail = CONFIG_SALES_EMAIL;
    $senderName = 'CNC Sales Department';

    $toEmail = CONFIG_SALES_EMAIL;
    $dbeCustomer->getRow($dbeProject->getValue(DBEProject::customerID));

    $dbeCustomer->fetchNext();


    $linkedSalesOrderID = $dbeProject->getValue(DBEProject::ordHeadID);

    $template->setVar(
        [
            "projectDescription"  => $dbeProject->getValue(DBEProject::description),
            "customerName"        => $dbeCustomer->getValue(DBECustomer::name),
            "projectCommenceDate" => $dbeProject->getValue(DBEProject::commenceDate),
            "salesOrderLink"      => "<a href='" . SITE_URL . "/SalesOrder.php?action=displaySalesOrder&ordheadID=$linkedSalesOrderID'>Sales Order</a>"
        ]
    );

    $subject = "Project starting for " . $dbeCustomer->getValue(DBECustomer::name) . " on " . $dbeProject->getValue(
            DBEProject::commenceDate
        );

    $template->parse(
        'output',
        'page',
        false
    );

    $body = $template->get_var('output');

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


