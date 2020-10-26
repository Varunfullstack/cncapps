<?php
require_once("config.inc.php");
global $cfg;
global $twig;
echo $twig->render('@internal/emailTemplateWithContent.html.twig');