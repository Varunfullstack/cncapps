<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 24/01/2019
 * Time: 9:54
 */

require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg["path_bu"] . "/BUPassword.inc.php");
require_once($cfg["path_dbe"] . "/DBEPassword.inc.php");

$thing = null;

$buPassword = new BUPassword($thing);

$dbePassword = new DBEPassword($thing);

$dbePassword->setValue(
    DBEPassword::encrypted,
    0
);
$dbePassword->getRowsByColumn(DBEPassword::encrypted);

while ($dbePassword->fetchNext()) {
    $updateDBEPassword = new DBEPassword($thing);
    $updateDBEPassword->getRow($dbePassword->getValue(DBEPassword::passwordID));
    if ($dbePassword->getValue(DBEPassword::password) != '') {
        $updateDBEPassword->setValue(
            DBEPassword::password,
            $buPassword->encrypt($dbePassword->getValue(DBEPassword::password))
        );
    }

    if ($dbePassword->getValue(DBEPassword::username) != '') {
        $updateDBEPassword->setValue(
            DBEPassword::username,
            $buPassword->encrypt($dbePassword->getValue(DBEPassword::username))
        );
    }

    if ($dbePassword->getValue(DBEPassword::notes) != '') {

        $re = '/\b(?:http(s)?:\/\/)[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:\/?#[\]@!\$&\'\(\)\*\+,;=.]+/i';
        $notes = $dbePassword->getValue(DBEPassword::notes);

        if (preg_match(
            $re,
            $notes,
            $matches,
            PREG_OFFSET_CAPTURE,
            0
        )) {

            $notes = str_replace(
                $matches[0][0],
                "",
                $notes
            );
            $updateDBEPassword->setValue(
                DBEPassword::URL,
                $buPassword->encrypt($matches[0][0])
            );
        }

        $updateDBEPassword->setValue(
            DBEPassword::notes,
            $buPassword->encrypt($notes)
        );
    }

    $updateDBEPassword->setValue(
        DBEPassword::level,
        1
    );

    $updateDBEPassword->setValue(
        DBEPassword::encrypted,
        1
    );
    $updateDBEPassword->updateRow();
}

echo '<h1>All Done!</h1>';