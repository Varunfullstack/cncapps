<?php

require_once __DIR__.'/../htdocs/config.inc.php';

global $cfg;
require_once($cfg ["path_bu"] . "/BUExpense.inc.php");
$thing     = null;
$buExpense = new BUExpense($thing);
var_dump($buExpense->calculateOvertime(2772368));

