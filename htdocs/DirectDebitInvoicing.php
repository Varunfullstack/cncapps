<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 31/08/2018
 * Time: 12:34
 */

require_once("config.inc.php");
require_once($cfg["path_ct"] . "/CTDirectDebitInvoicing.php");
session_start();
page_open(
    array(
        'sess' => PHPLIB_CLASSNAME_SESSION,
        'auth' => PHPLIB_CLASSNAME_AUTH,
        'perm' => PHPLIB_CLASSNAME_PERM,
        ''
    )
);
header("Cache-control: private");
GLOBAL $cfg;
$ctDirectDebitInvoicing = new CTDirectDebitInvoicing(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctDirectDebitInvoicing->execute();
page_close();
?>