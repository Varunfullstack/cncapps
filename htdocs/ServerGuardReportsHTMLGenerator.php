<?php
require_once("config.inc.php");
global $cfg;
global $twig;
echo $twig->render('@customerFacing/ServerGuardReports/ServerGuardReports.html.twig');