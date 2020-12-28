<?php
/**
 * Check that MySQL backup has worked OK
 *
 * called as scheduled task at given time every day
 *
 * Check the date on the file and the size of it.
 * Email Gary, Karim and Roger on failure
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUMail.inc.php");

$thing = null;

define(
    'BACKUP_ALERT_EMAIL_FROM_USER',
    'sales@' . CONFIG_PUBLIC_DOMAIN
);
define(
    'BACKUP_ALERT_EMAIL_SUBJECT',
    'MySQL Backup Problem (raised by "Backup Integrity Check" scheduled task)'
);
define(
    'BACKUP_ALERT_BACKUP_FILE',
    'mysqldump.sql.gz'
);

define(
    'BACKUP_ALERT_MIN_FILE_SIZE_BYTES',
    10485760
);
define(
    'BACKUP_ALERT_MIN_AGE_HOURS',
    24
);

define(
    'MASTER_HOST',
    'cncapps'
);

$error = false;

$send_to_email = 'karim@sweetcode.co.uk,' . CONFIG_SALES_MANAGER_EMAIL;

// is the date on the file in the last 24 hours?

$dump_file = $cfg["path_db_backup"] . '\\' . BACKUP_ALERT_BACKUP_FILE;

$dump_file_age_seconds = time() - fileatime($dump_file);

if ($dump_file_age_seconds > (BACKUP_ALERT_MIN_AGE_HOURS * 3600)) {

    $error = 'The database backup file at ' . $dump_file . ' is ' . (int)($dump_file_age_seconds / 3600) . ' hours old at the time this backup integrity script ran.';

}

if (filesize($dump_file) < BACKUP_ALERT_MIN_FILE_SIZE_BYTES) {

    $error = 'The database backup file at ' . $dump_file . ' is smaller than ' . BACKUP_ALERT_MIN_FILE_SIZE_BYTES / 1024 / 1024 . ' MB long.';

}

if ($error) {

    $error .= "\n\nThis means that something went wrong with the morning Scheduled Task 'Daily MySQL Backup' on the server and must be fixed.\n";

    $buMail = new BUMail($thing);

    $buMail->mime->setTXTBody($error);

    $body = $buMail->mime->get();

    $hdrs = array(
        'To'      => $send_to_email,
        'From'    => BACKUP_ALERT_EMAIL_FROM_USER,
        'Subject' => BACKUP_ALERT_EMAIL_SUBJECT
    );

    $hdrs = $buMail->mime->headers($hdrs);

    $buMail->putInQueue(
        BACKUP_ALERT_EMAIL_FROM_USER,
        $send_to_email,
        $hdrs,
        $body
    );

} else {
    echo "Backup OK";
}
?>