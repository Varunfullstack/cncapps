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
    if ($dbePassword->getValue(DBEPassword::URL) != '') {
        $updateDBEPassword->setValue(
            DBEPassword::URL,
            $buPassword->encrypt($dbePassword->getValue(DBEPassword::URL))
        );
    }
    if ($dbePassword->getValue(DBEPassword::notes) != '') {
        $updateDBEPassword->setValue(
            DBEPassword::notes,
            $buPassword->encrypt($dbePassword->getValue(DBEPassword::notes))
        );
    }

    $updateDBEPassword->setValue(
        DBEPassword::encrypted,
        1
    );
    $updateDBEPassword->updateRow();
}

echo '<h1>All Done!</h1>';