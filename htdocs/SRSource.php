<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTSRSource.inc.php");
session_start();
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
$ctSRSource = new CTSRSource(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctSRSource->execute();
page_close();
?>
<link rel="stylesheet"
      href="components/style.css?version=<?= time() ?>"
>
<link rel="stylesheet"
      href="css/table.css?version=<?= time() ?>"
>
<script type="module"
        src='components/dist/CMPSRSource.js?version=<?= time() ?>'
></script>