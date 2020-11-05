<?php

require_once("config.inc.php");
global $cfg;
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
global $twig;
$templateName = '@internal/email-template-1.html.twig';
if (isset($_REQUEST['templateName'])) {
    $templateName = $_REQUEST['templateName'];
}
$context = [];
if (isset($_REQUEST['context'])) {
    $context = $_REQUEST['context'];
}

$body = $twig->render($templateName, $context);
echo $body;

if (isset($_REQUEST['send'])) {
    $that = null;
    $buMail = new BUMail($that);

    $emailTo = CONFIG_SALES_EMAIL;

    $hdrs = array(
        'From'         => CONFIG_SUPPORT_EMAIL,
        'To'           => $emailTo,
        'Subject'      => "Nice Twig Test",
        'Date'         => date("r"),
        'Content-Type' => 'text/html; charset=UTF-8'
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
        $emailTo,
        $hdrs,
        $body
    );
}

