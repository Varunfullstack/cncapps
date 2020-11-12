<?php


require_once("config.inc.php");
global $cfg;
require_once($cfg["path_bu"] . "/BUMail.inc.php");
$buMail = new BUMail($thing);
$toEmail = 'adrianc@' . CONFIG_PUBLIC_DOMAIN;
$hdrs = array(
    'From'         => 'support@' . CONFIG_PUBLIC_DOMAIN,
    'To'           => $toEmail,
    'Subject'      => "Test Email",
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);

$body = '<h1>Test email!</h1>';

$buMail->mime->setHTMLBody($body);

$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset'  => 'UTF-8',
    'html_charset'  => 'UTF-8',
    'head_charset'  => 'UTF-8'
);

$body = $buMail->mime->get($mime_params);

$hdrs = $buMail->mime->headers($hdrs);

$sent = $buMail->send($toEmail, $hdrs, $body);

var_dump($sent);