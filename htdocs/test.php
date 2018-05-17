<?php

var_dump(get_current_user());
exit;
require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");


if (!($GLOBALS['isRunningFromCommandLine']) && isset($GLOBALS ['auth'])) {
    $userID = $GLOBALS ['auth']->is_authenticated();
} else {
    $userID = CONFIG_SCHEDULED_TASK_USER_ID;
}

echo 'the user is ' . $userID;
