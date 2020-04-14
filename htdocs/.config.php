<?php
session_start();
// for new sweeeeeet code!
// Turn page output compression on to ensure speedy page return
ini_set('zlib.output_compression', 'On');
ini_set('zlib.output_compression_level', '9');
// define 2 error levels
define('CONFIG_ERROR_LEVEL_DEVELOPMENT', E_ALL & ~E_STRICT & ~E_WARNING &  ~E_DEPRECATED);            // we want to see them all!
define('CONFIG_ERROR_LEVEL_PRODUCTION', CONFIG_ERROR_LEVEL_DEVELOPMENT );
/*
* Handy
*/
define('WIN_CR', "\r\n");
define('NIX_CR', "\n");
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    define('CR', WIN_CR);
} else {
    define('CR', NIX_CR);
}
define('TAB', "\t");

define('CONFIG_SERVER_TYPE_TEST', 'test');
define('CONFIG_SERVER_TYPE_LIVE', 'live');
define('CONFIG_SERVER_TYPE_DEVELOPMENT', 'development');

$add_path = '';
define('CONFIG_ROWS_PER_PAGE', 2000);
if ($_SERVER['HTTP_HOST'] == 'cncappdev' OR $_SERVER['HTTP_HOST'] == 'cncappsdev') {
    $server_type = CONFIG_SERVER_TYPE_TEST;
}
if ($_SERVER['HTTP_HOST'] == 'cncapp' OR $_SERVER['HTTP_HOST'] == 'cncapps') {
    $server_type = CONFIG_SERVER_TYPE_LIVE;
}
if ($_SERVER['HTTP_HOST'] == 'devtest.pavilionweb.com') {
    $server_type = CONFIG_SERVER_TYPE_DEVELOPMENT;
}
if ($_SERVER['HTTP_HOST'] == 'cncdev.cnc-ltd.co.uk') {
    $serverPhp7 = true;
}


/*
* Paths
*/
define('CONFIG_PATH_SC_CLASSES', $_SERVER['DOCUMENT_ROOT'] . $add_path . '/.classes/sc/');
define('CONFIG_PATH_CNC_CLASSES', $_SERVER['DOCUMENT_ROOT'] . $add_path . '/.classes/cnc/');
define('CONFIG_PATH_PEAR_CLASSES', $_SERVER['DOCUMENT_ROOT'] . $add_path . '/.classes/PEAR/');
define('CONFIG_PATH_SC_HTML', $_SERVER['DOCUMENT_ROOT'] . $add_path . '/.html/sc/');
define('CONFIG_PATH_CNC_HTML', $_SERVER['DOCUMENT_ROOT'] . $add_path . '/.html/cnc/');
define('CONFIG_PATH_CACHE', $_SERVER['DOCUMENT_ROOT'] . $add_path . '/.page_cache/');
/*
Pages
*/
define('CONFIG_PAGE_LOGIN', 'login.php');
require_once(CONFIG_PATH_SC_CLASSES . 'string.php');
/*
Use the right database params for our server
*/
switch ($server_type) {
    case CONFIG_SERVER_TYPE_TEST:
        define('CONFIG_DB_HOST', 'localhost');
        define('CONFIG_DB_USERNAME', 'webuser');
        define('CONFIG_DB_PASSWORD', 'CnC1988');
        define('CONFIG_DB_NAME', 'cncappsdev');
        break;
    case CONFIG_SERVER_TYPE_LIVE:
        define('CONFIG_DB_HOST', 'localhost');
        define('CONFIG_DB_USERNAME', 'webuser');
        define('CONFIG_DB_PASSWORD', 'CnC1988');
        define('CONFIG_DB_NAME', 'cncapps');
        break;
    case CONFIG_SERVER_TYPE_DEVELOPMENT:
        if ($serverPhp7) {
            define('CONFIG_DB_HOST', "localhost");
        } else {
            define('CONFIG_DB_HOST', "188.39.98.130");
        }

        define('CONFIG_DB_USERNAME', 'webuser');
        define('CONFIG_DB_PASSWORD', 'CnC1988');
        define('CONFIG_DB_NAME', 'cncappsdev');
        break;
}
/*
END database params
*/
/*
Error loging and levels

Set error handling and display/logging options depending upon system environment.
if strings "test" or "local" in the URL then assume this is a non-production system
*/
if ($server_type == CONFIG_SERVER_TYPE_TEST | $server_type == CONFIG_SERVER_TYPE_DEVELOPMENT) {
    error_reporting(CONFIG_ERROR_LEVEL_DEVELOPMENT);
    ini_set('display_errors', 'On');                                                // errors to screen
} else {
    error_reporting(CONFIG_ERROR_LEVEL_PRODUCTION);
    ini_set('display_errors', 'Off');                                                // no errors to screen
}

//$_SESSION['ajax_is_enabled'] = false;										// default
/*
Language
*/
define('CONFIG_LANGUAGE_TRANSLATION_IS_ON', false);

if (CONFIG_LANGUAGE_TRANSLATION_IS_ON) {
    define('CONFIG_DEFAULT_LANGUAGE', 'en');
    if (isset($_REQUEST['language'])) {
        $_SESSION['language'] = $_REQUEST['language'];
        SC_String::createTranslationArray($_SESSION['language']);
    }
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = CONFIG_DEFAULT_LANGUAGE;
        SC_String::createTranslationArray($_SESSION['language']);
    }
    setlocale(LC_ALL, $_SESSION['language']);
}
/*
End Language
*/
/*
Debug (pass in on query. e.g. ......&show_timings=1)
*/
if (isset($_REQUEST['show_queries'])) {
    $_SESSION['show_queries'] = $_REQUEST['show_queries'];
}
if (isset($_REQUEST['javascript_is_enabled'])) {
    $_SESSION['javascript_is_enabled'] = $_REQUEST['javascript_is_enabled'];
}
if (isset($_REQUEST['show_ajax'])) {
    $_SESSION['show_ajax'] = $_REQUEST['show_ajax'];
}
if (isset($_REQUEST['ajax_client_debug'])) {
    $_SESSION['ajax_client_debug'] = $_REQUEST['ajax_client_debug'];
}
if (isset($_REQUEST['show_timings'])) {
    $_SESSION['show_timings'] = $_REQUEST['show_timings'];
}
if (isset($_REQUEST['show_source'])) {
    $_SESSION['show_source'] = $_REQUEST['show_source'];
}
/*
End Debug
*/
/*
Strip all slashes from request variables (includes cookies)

Our application assumes no automatic escaping of strings coming in from browsers etc (we are clever
enough to decide when to do that for ourselves thank you! ;-) )

Therefore, if magic_quotes_gpc is set, strip all backslashes from GPC arrays.

If you have access to your server's php.ini file, please turn magic_quotes_gpc off
so we don't waste time!
*/
if (get_magic_quotes_gpc()) {
    function stripslashes_deep($value)
    {
        $value = is_array($value) ?
            array_map('stripslashes_deep', $value) :
            stripslashes($value);

        return $value;
    }

    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}
/*
End Strip all slashes from request variables (includes cookies)
*/
?>
