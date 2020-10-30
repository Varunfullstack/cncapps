<?php
require_once("config.inc.php");
global $cfg;
global $twig;
echo $twig->render('@internal/email-template-4.html.twig');