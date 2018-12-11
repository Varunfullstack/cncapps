<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/11/2018
 * Time: 9:18
 */


require_once("config.inc.php");
require_once($cfg["path_ct"] . "/CTAnswerTypeConfig.php");
session_start();
page_open(
    array(
        'sess' => PHPLIB_CLASSNAME_SESSION,
        'auth' => PHPLIB_CLASSNAME_AUTH,
        'perm' => PHPLIB_CLASSNAME_PERM,
        ''
    )
);
GLOBAL $cfg;
//header("Cache-control: private");
$ctAnswerTypeConfig = new CTAnswerTypeConfig(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctAnswerTypeConfig->execute();
page_close();
?>