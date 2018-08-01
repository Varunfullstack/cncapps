<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 01/08/2018
 * Time: 9:53
 */


require_once("config.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");


//we are going to use this to add to the monitoring db
$dsn = 'mysql:host=192.168.33.64;dbname=labtech';
$DB_USER = "root";
$DB_PASSWORD = "kj389fj29fjh";
$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
];
$localDB = new PDO(
    $dsn,
    $DB_USER,
    $DB_PASSWORD,
    $options
);

$query = $localDB->query('select * from outgoingemails ');

$emails = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($emails as $emailData) {


    $buMail = new BUMail($this);
    $toEmail = $emailData['To'];


    $hdrs = array(
        'From'         => 'sg@cnc-ltd.co.uk',
        'To'           => $toEmail,
        'Subject'      => $emailData['Subject'],
        'Date'         => date("r"),
        'Content-Type' => 'text/html; charset=UTF-8'
    );

    $buMail->mime->setHTMLBody($emailData['Body']);

    if ($emailData['AttachName']) {
        $query = "select * from outgoingemailattachments where EmailAttachID in ($emailData[AttachName])";
        $attachmentsQuery = $localDB->query($query);

        $attachments = $attachmentsQuery->fetchAll(PDO::FETCH_ASSOC);
        foreach ($attachments as $attachment) {
            $buMail->mime->addAttachment(
                $attachment['Attachment'],
                "application/octet-stream",
                $attachment['AttachName'],
                false
            );
        }

    }
    $mime_params = array(
        'text_encoding' => '7bit',
        'text_charset'  => 'UTF-8',
        'html_charset'  => 'UTF-8',
        'head_charset'  => 'UTF-8'
    );
    $thisBody = $buMail->mime->get($mime_params);
    $hdrs = $buMail->mime->headers($hdrs);

    $success = $buMail->send(
        $toEmail,
        $hdrs,
        $thisBody
    );

    if ($success && ($server_type === MAIN_CONFIG_SERVER_TYPE_LIVE)) {
        $localDB->query("delete from outgoingemails where EmailID = $emailData[EmailID]");
        if ($emailData['AttachName']) {
            $localDB->query("delete from outgoingemailattachments where EmailAttachID in ($emailData[AttachName])");
        }
    }
}
