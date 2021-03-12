<?php
/**
 * Created by PhpStorm.
 * User: fizda
 * Date: 09/01/2018
 * Time: 18:03
 */

require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTCustomerCRM.inc.php");
page_open(
    array(
        'sess' => PHPLIB_CLASSNAME_SESSION,
        'auth' => PHPLIB_CLASSNAME_AUTH,
        'perm' => PHPLIB_CLASSNAME_PERM,
        ''
    )
);
global $cfg;
header("Cache-control: private");
$ctCustomer = new CTCustomerCRM(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctCustomer->execute();
page_close();
?>