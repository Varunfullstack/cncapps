<?php


require_once("config.inc.php");
global $cfg;
global $twig;
$templateName = '@internal/email-template-1.html.twig';
if (isset($_REQUEST['templateName'])) {
    $templateName = $_REQUEST['templateName'];
}
$context = [];
if (isset($_REQUEST['context'])) {
    $context = $_REQUEST['context'];
}
echo $twig->render($templateName, $context);