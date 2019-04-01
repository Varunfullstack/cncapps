<?php
/* really send the messages */
require_once("config.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
$thing = null;
$buMail = new BUMail($thing);
$result = $buMail->sendQueue();

//
//if($result instanceof Mail_Queue_Error){
//var_dump($result);
//
//}
echo 'All done';
?>
