<?php
/* really send the messages */
require_once("config.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
$buMail = new BUMail($this);
$buMail->sendQueue();
?>
