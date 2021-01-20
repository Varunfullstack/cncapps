<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 02/08/2018
 * Time: 10:26
 */
require_once "config.inc.php";
require_once $cfg['path_dbe'] . '/DBEJContactAudit.php';
require_once $cfg['path_bu'] . '/BUMail.inc.php';
$thing = null;
$test  = new DBEJContactAudit($thing);
$days = isset($_REQUEST['days']) ? $_REQUEST['days'] : 7;
$test->search(
    null,
    new DateTime("$days days ago"),
    new DateTime()
);
$template = new Template (
    EMAIL_TEMPLATE_DIR, "remove"
);
$template->set_file(
    array(
        'page' => 'ContactAuditLogChangesEmail.html',
    )
);
$template->set_block(
    'page',
    'contactAuditChangeBlock',
    'items'
);
$rowCount = $test->rowCount();
if (!$rowCount) {
    echo 'Nothing to show';
    exit;
}
while ($test->fetchNext()) {
    $template->set_var(
        [
            "createdByUserName"    => $test->getValue(DBEJContactAudit::createdByUserName),
            "createdByContactName" => $test->getValue(DBEJContactAudit::createdByContactName),
            "customerName"         => $test->getValue(DBEJContactAudit::customerName),
            "contactID"            => $test->getValue(DBEJContactAudit::contactID),
            "siteNo"               => $test->getValue(DBEJContactAudit::siteNo),
            "customerID"           => $test->getValue(DBEJContactAudit::customerID),
            "title"                => $test->getValue(DBEJContactAudit::title),
            "position"             => $test->getValue(DBEJContactAudit::position),
            "lastName"             => $test->getValue(DBEJContactAudit::lastName),
            "firstName"            => $test->getValue(DBEJContactAudit::firstName),
            "email"                => $test->getValue(DBEJContactAudit::email),
            "phone"                => $test->getValue(DBEJContactAudit::phone),
            "mobilePhone"          => $test->getValue(DBEJContactAudit::mobilePhone),
            "fax"                  => $test->getValue(DBEJContactAudit::fax),
            "portalPassword"       => $test->getValue(DBEJContactAudit::portalPassword),
            "mailshot"             => $test->getValue(DBEJContactAudit::mailshot),
            "mailshot2Flag"        => $test->getValue(DBEJContactAudit::mailshot2Flag),
            "mailshot3Flag"        => $test->getValue(DBEJContactAudit::mailshot3Flag),
            "mailshot8Flag"        => $test->getValue(DBEJContactAudit::mailshot8Flag),
            "mailshot9Flag"        => $test->getValue(DBEJContactAudit::mailshot9Flag),
            "mailshot11Flag"       => $test->getValue(DBEJContactAudit::mailshot11Flag),
            "notes"                => $test->getValue(DBEJContactAudit::notes),
            "createdByContactId"   => $test->getValue(DBEJContactAudit::createdByContactId),
            "createdByUserId"      => $test->getValue(DBEJContactAudit::createdByUserId),
            "createdAt"            => $test->getValue(DBEJContactAudit::createdAt),
            "action"               => $test->getValue(DBEJContactAudit::action),
        ]
    );
    $template->parse(
        'items',
        'contactAuditChangeBlock',
        true
    );
}
$template->parse(
    'output',
    'page',
    true
);
$body = $template->get_var('output');
$thing  = null;
$buMail = new BUMail($thing);
$buMail->mime->setHTMLBody($body);
$hdrs_array  = array(
    'From'         => CONFIG_SUPPORT_EMAIL,
    'To'           => "ContactChanges@" . CONFIG_PUBLIC_DOMAIN,
    'Subject'      => "Contact Change Audit Log",
    'Content-Type' => 'text/html; charset=UTF-8'
);
$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset'  => 'UTF-8',
    'html_charset'  => 'UTF-8',
    'head_charset'  => 'UTF-8'
);
$body        = $buMail->mime->get($mime_params);
$hdrs = $buMail->mime->headers($hdrs_array);
$buMail->putInQueue(
    CONFIG_SUPPORT_EMAIL,
    "ContactChanges@" . CONFIG_PUBLIC_DOMAIN,
    $hdrs,
    $body
);
echo $body; // and output to page