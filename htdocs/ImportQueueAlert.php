<?php
/**
 * Check that the import queue has no unimported rows older than 15 minutes
 *
 * If it does then email sdManager
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUMail.inc.php");

define(
    'EMAIL_SUBJECT',
    'Import Queue Problem'
);

$sql = "
  SELECT 
    COUNT(*)
  FROM
    `automated_request`
  WHERE
    importedFlag = 'N'
    AND TIMEDIFF( NOW(), createDateTime ) > '00:15:00'";

$db->query($sql);
$db->next_record();
$count = $db->Record[0];
$thing = null;
if ($count > 0) {

    $body = "$count rows have been in the import queue for longer than 15 minutes.\n";

    $hdrs_array = array(
        'From'    => CONFIG_SALES_MANAGER_EMAIL,
        'Subject' => EMAIL_SUBJECT
    );

    $buMail = new BUMail($thing);

    $buMail->mime->setTXTBody($body);

    $body = $buMail->mime->get();

    $hdrs = array(
        'From'    => CONFIG_SALES_MANAGER_EMAIL,
        'Subject' => EMAIL_SUBJECT
    );

    $hdrs = $buMail->mime->headers($hdrs);

    $buMail->putInQueue(
        CONFIG_SALES_MANAGER_EMAIL,
        $send_to_email,
        $hdrs,
        $body,
        true
    );

}
?>