<?php
/* really send the messages */
require_once("config.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
$thing = null;
$buMail = new BUMail($thing);
$buMail->sendQueue();
echo 'All done';
?>
