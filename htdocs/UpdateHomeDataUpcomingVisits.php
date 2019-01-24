<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 27/09/2018
 * Time: 12:06
 */

GLOBAL $cfg;
require_once("config.inc.php");
require_once ($cfg['path_bu']) . '/BUHome.php';

$thing = null;

$buHome = new BUHome();

$buHome->updateUpcomingVisits();

?>