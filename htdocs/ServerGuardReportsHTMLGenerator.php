<?php
require_once("config.inc.php");
global $cfg;
global $twig;
echo $twig->render('@customerFacing/style-3-rows-email/ServerGuardReports/ServerGuardReports.html.twig');