<?php

use Twig\Environment;
use Twig\TwigFilter;

const DEV_PORTAL_URL = "https://www.cnc-ltd.co.uk:4481";
function is_cli()
{
    if (defined('STDIN')) {
        return true;
    }
    if (empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) {
        return true;
    }
    return false;
}

function cli_echo($string, $color = null)
{
    $restoreColor   = "\e[0m";
    $applyColorCode = null;
    switch ($color) {
        case "error":
            $applyColorCode = "\e[31m";
            break;
        case "success":
            $applyColorCode = "\e[32m";
            break;
        case 'info':
            $applyColorCode = "\e[36m";
            break;
        case 'warning':
            $applyColorCode = "\e[33m";
    }
    if ($applyColorCode) {
        $string = $applyColorCode . $string . $restoreColor;
    }
    echo $string . PHP_EOL;
}

function getEnvironmentByPath()
{

    if (strpos(__DIR__, 'cncapps') !== false) {
        $_SERVER['HTTP_HOST'] = 'cncapps.cnc-ltd.co.uk';
        return MAIN_CONFIG_SERVER_TYPE_LIVE;
    }
    if (strpos(__DIR__, 'cncdev7') !== false) {
        $_SERVER['HTTP_HOST'] = 'cncdev.cnc-ltd.co.uk';
        return MAIN_CONFIG_SERVER_TYPE_DEVELOPMENT;
    }
    if (strpos(__DIR__, 'cnctest') !== false) {
        $_SERVER['HTTP_HOST'] = 'cnctest.cnc-ltd.co.uk';
        return MAIN_CONFIG_SERVER_TYPE_TEST;
    }
    if (strpos(__DIR__, 'cncweb') !== false) {
        $_SERVER['HTTP_HOST'] = 'cncweb.cnc-ltd.co.uk';
        return MAIN_CONFIG_SERVER_TYPE_WEBSITE;
    }
    if (strpos(__DIR__, 'cncdesign') !== false) {
        $_SERVER['HTTP_HOST'] = 'cncdesign.cnc-ltd.co.uk';
        return MAIN_CONFIG_SERVER_TYPE_DESIGN;
    }
    if (strpos(__DIR__, 'cncdev2') !== false) {
        $_SERVER['HTTP_HOST'] = 'cncdev2.cnc-ltd.co.uk';
        return MAIN_CONFIG_SERVER_TYPE_DEV2;
    }
    return MAIN_CONFIG_SERVER_TYPE_LIVE;
}

function escape_win32_argv(string $value): string
{
    static $expr = '( 
        [\x00-\x20\x7F"] # control chars, whitespace or double quote 
      | \\\\++ (?=("|$)) # backslashes followed by a quote or at the end 
    )ux';
    if ($value === '') {
        return '""';
    }
    $quote    = false;
    $replacer = function ($match) use ($value, &$quote) {
        switch ($match[0][0]) { // only inspect the first byte of the match
            case '"': // double quotes are escaped and must be quoted
                $match[0] = '\\"';
            case ' ':
            case "\t": // spaces and tabs are ok but must be quoted
                $quote = true;
                return $match[0];
            case '\\': // matching backslashes are escaped if quoted
                return $match[0] . $match[0];
            default:
                throw new InvalidArgumentException(
                    sprintf(
                        "Invalid byte at offset %d: 0x%02X",
                        strpos($value, $match[0]),
                        ord($match[0])
                    )
                );
        }
    };
    $escaped  = preg_replace_callback($expr, $replacer, (string)$value);
    if ($escaped === null) {
        throw preg_last_error() === PREG_BAD_UTF8_ERROR ? new InvalidArgumentException(
            "Invalid UTF-8 string"
        ) : new Error("PCRE error: " . preg_last_error());
    }
    return $quote // only quote when needed
        ? '"' . $escaped . '"' : $value;
}

/** Escape cmd.exe metacharacters with ^ */
function escape_win32_cmd(string $value): string
{
    return preg_replace('([()%!^"<>&|])', '^$0', $value);
}

/** Like shell_exec() but bypass cmd.exe */
function noshell_exec(string $command): string
{
    static $descriptors = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $options = ['bypass_shell' => true];
    if (!$proc = proc_open($command, $descriptors, $pipes, null, null, $options)) {
        throw new \Error('Creating child process failed');
    }
    fclose($pipes[0]);
    $result = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    proc_close($proc);
    return $result;
}

function money_format($format, $number)
{
    $regex = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?' . '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
    if (setlocale(LC_MONETARY, 0) == 'C') {
        setlocale(LC_MONETARY, '');
    }
    $locale = localeconv();
    preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
    foreach ($matches as $fmatch) {
        $value      = floatval($number);
        $flags      = array(
            'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ? $match[1] : ' ',
            'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0,
            'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ? $match[0] : '+',
            'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0,
            'isleft'    => preg_match('/\-/', $fmatch[1]) > 0
        );
        $width      = trim($fmatch[2]) ? (int)$fmatch[2] : 0;
        $left       = trim($fmatch[3]) ? (int)$fmatch[3] : 0;
        $right      = trim($fmatch[4]) ? (int)$fmatch[4] : $locale['int_frac_digits'];
        $conversion = $fmatch[5];
        $positive   = true;
        if ($value < 0) {
            $positive = false;
            $value    *= -1;
        }
        $letter = $positive ? 'p' : 'n';
        $prefix = $suffix = $cprefix = $csuffix = $signal = '';
        $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
        switch (true) {
            case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
                $prefix = $signal;
                break;
            case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
                $suffix = $signal;
                break;
            case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
                $cprefix = $signal;
                break;
            case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
                $csuffix = $signal;
                break;
            case $flags['usesignal'] == '(':
            case $locale["{$letter}_sign_posn"] == 0:
                $prefix = '(';
                $suffix = ')';
                break;
        }
        if (!$flags['nosimbol']) {
            $currency = $cprefix . ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) . $csuffix;
        } else {
            $currency = '';
        }
        $space = $locale["{$letter}_sep_by_space"] ? ' ' : '';
        $value = number_format(
            $value,
            $right,
            $locale['mon_decimal_point'],
            $flags['nogroup'] ? '' : $locale['mon_thousands_sep']
        );
        $value = @explode($locale['mon_decimal_point'], $value);
        $n     = strlen($prefix) + strlen($currency) + strlen($value[0]);
        if ($left > 0 && $left > $n) {
            $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
        }
        $value = implode($locale['mon_decimal_point'], $value);
        if ($locale["{$letter}_cs_precedes"]) {
            $value = $prefix . $currency . $space . $value . $suffix;
        } else {
            $value = $prefix . $value . $space . $currency . $suffix;
        }
        if ($width > 0) {
            $value = str_pad(
                $value,
                $width,
                $flags['fillchar'],
                $flags['isleft'] ? STR_PAD_RIGHT : STR_PAD_LEFT
            );
        }
        $format = str_replace($fmatch[0], $value, $format);
    }
    return $format;
}

function utf8MoneyFormat($format, $number)
{
    return utf8_encode(money_format($format, $number));
}

define(
    "UK_MONEY_FORMAT",
    "%.2n"
);
function var_debug($variable, $strlen = 100, $width = 25, $depth = 10, $i = 0, &$objects = array())
{
    $search  = array("\0", "\a", "\b", "\f", "\n", "\r", "\t", "\v");
    $replace = array('\0', '\a', '\b', '\f', '\n', '\r', '\t', '\v');
    $string  = '';
    switch (gettype($variable)) {
        case 'boolean':
            $string .= $variable ? 'true' : 'false';
            break;
        case 'integer':
            $string .= $variable;
            break;
        case 'double':
            $string .= $variable;
            break;
        case 'resource':
            $string .= '[resource]';
            break;
        case 'NULL':
            $string .= "null";
            break;
        case 'unknown type':
            $string .= '???';
            break;
        case 'string':
            $len      = strlen($variable);
            $variable = str_replace($search, $replace, substr($variable, 0, $strlen), $count);
            $variable = substr($variable, 0, $strlen);
            if ($len < $strlen) $string .= '"' . $variable . '"'; else $string .= 'string(' . $len . '): "' . $variable . '"...';
            break;
        case 'array':
            $len = count($variable);
            if ($i == $depth) $string .= 'array(' . $len . ') {...}'; elseif (!$len) $string .= 'array(0) {}';
            else {
                $keys   = array_keys($variable);
                $spaces = str_repeat(' ', $i * 2);
                $string .= "array($len)\n" . $spaces . '{';
                $count  = 0;
                foreach ($keys as $key) {
                    if ($count == $width) {
                        $string .= "\n" . $spaces . "  ...";
                        break;
                    }
                    $string .= "\n" . $spaces . "  [$key] => ";
                    $string .= var_debug($variable[$key], $strlen, $width, $depth, $i + 1, $objects);
                    $count++;
                }
                $string .= "\n" . $spaces . '}';
            }
            break;
        case 'object':
            $id = array_search($variable, $objects, true);
            if ($id !== false) $string .= get_class(
                    $variable
                ) . '#' . ($id + 1) . ' {...}'; else if ($i == $depth) $string .= get_class(
                    $variable
                ) . ' {...}'; else {
                $id         = array_push($objects, $variable);
                $array      = (array)$variable;
                $spaces     = str_repeat(' ', $i * 2);
                $string     .= get_class($variable) . "#$id\n" . $spaces . '{';
                $properties = array_keys($array);
                foreach ($properties as $property) {
                    $name   = str_replace("\0", ':', trim($property));
                    $string .= "\n" . $spaces . "  [$name] => ";
                    $string .= var_debug($array[$property], $strlen, $width, $depth, $i + 1, $objects);
                }
                $string .= "\n" . $spaces . '}';
            }
            break;
    }
    if ($i > 0) return $string;
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    do $caller = array_shift($backtrace); while ($caller && !isset($caller['file']));
    if ($caller) $string = $caller['file'] . ':' . $caller['line'] . "\n" . $string;
    echo nl2br(str_replace(' ', '&nbsp;', htmlentities($string)));
}

date_default_timezone_set('Europe/London');
ini_set(
    'memory_limit',
    '8192M'
);
/**
 * Configuration settings.
 *
 * Karim Ahmed
 *
 */
/*
Strip all slashes from request variables (includes cookies)

Our application assumes no automatic escaping of strings coming in from browsers etc (we are clever
enough to decide when to do that for ourselves thank you! ;-) )

Therefore, if magic_quotes_gpc is set, strip all backslashes from GPC arrays.

If you have access to your server's php.ini file, please turn magic_quotes_gpc off
so we don't waste time!
*/
/*
if (get_magic_quotes_gpc()) {

   $_POST 		=	array_map('stripslashes_deep', $_POST);
   $_GET 			= array_map('stripslashes_deep', $_GET);
   $_REQUEST	= array_map('stripslashes_deep', $_REQUEST);
   $_COOKIE		= array_map('stripslashes_deep', $_COOKIE);
}
*/
/*
End Strip all slashes from request variables (includes cookies)
*/
/**
 * @param $value
 * @return array|string
 */
function stripslashes_deep($value)
{
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    return $value;
}

set_time_limit(30 * 60);
// if magic_quotes_gpc is set then strip all backslashes from GPC arrays first
// absolute path to root application directory
define(
    'MAIN_CONFIG_SERVER_TYPE_TEST',
    'test'
);
define(
    'MAIN_CONFIG_SERVER_TYPE_LIVE',
    'live'
);
define(
    'MAIN_CONFIG_SERVER_TYPE_DEVELOPMENT',
    'development'
);
define(
    'MAIN_CONFIG_SERVER_TYPE_REPLICATED',
    'replicated'
); // replicated server
define(
    'MAIN_CONFIG_SERVER_TYPE_WEBSITE',
    'website'
);
define(
    'MAIN_CONFIG_SERVER_TYPE_DESIGN',
    'design'
);
define(
    'MAIN_CONFIG_SERVER_TYPE_DEV2',
    'dev2'
);
define(
    'CUSTOMERS_ENCRYPTION_PRIVATE_KEY',
    'c:\\keys\\privkey.pem'
);
define(
    'CUSTOMERS_ENCRYPTION_PUBLIC_KEY',
    'c:\\keys\\privkey.pub'
);
define(
    'USER_ENCRYPTION_PRIVATE_KEY',
    'c:\\keys\\user-private.pem'
);
define(
    'USER_ENCRYPTION_PUBLIC_KEY',
    'c:\\keys\\user-private.pub'
);
define(
    'PASSWORD_ENCRYPTION_PRIVATE_KEY',
    'c:\\keys\\passwordPrivate.pem'
);
define(
    'PASSWORD_ENCRYPTION_PUBLIC_KEY',
    'c:\\keys\\passwordPublic.pub'
);
define(
    'PASSWORD_PASSPHRASE',
    "adductor scincoid glue aviation sought beaker"
);
define(
    'LABTECH_DB_HOST',
    '192.168.33.64'
);
define(
    'LABTECH_DB_NAME',
    'labtech'
);
define(
    'LABTECH_DB_USERNAME',
    'cnccrmuser'
);
define(
    'LABTECH_DB_PASSWORD',
    'kj389fj29fjh'
);
$onPavilionWebServer = false;
$GLOBALS['php7']     = true;
$php7                = true;
$environment         = [];
if (isset($_SERVER['HTTP_HOST'])) {                // not set for command line calls
    switch ($_SERVER['HTTP_HOST']) {

        case 'cncapps.cnc-ltd.co.uk':
            $server_type = MAIN_CONFIG_SERVER_TYPE_LIVE;
            break;
        case 'cncdev.cnc-ltd.co.uk':
            $server_type = MAIN_CONFIG_SERVER_TYPE_DEVELOPMENT;
            break;
        case 'cnctest.cnc-ltd.co.uk':
            $server_type = MAIN_CONFIG_SERVER_TYPE_TEST;
            break;
        case 'cncweb.cnc-ltd.co.uk':
            $server_type = MAIN_CONFIG_SERVER_TYPE_WEBSITE;
            break;
        case 'cncdesign.cnc-ltd.co.uk':
            $server_type = MAIN_CONFIG_SERVER_TYPE_DESIGN;
        case 'cncdev2.cnc-ltd.co.uk':
            $server_type = MAIN_CONFIG_SERVER_TYPE_DEV2;
    }
    $GLOBALS['isRunningFromCommandLine'] = false;

} else {                // command line call so assume live and force HTTP_HOST value
    $server_type                         = getEnvironmentByPath();
    $GLOBALS['isRunningFromCommandLine'] = true;
}
define('SITE_URL', "https://" . $_SERVER['HTTP_HOST']);
define(
    'CONFIG_PUBLIC_DOMAIN',
    'cnc-ltd.co.uk'
);
define(
    "DB_HOST",
    "localhost"
);
switch ($server_type) {

    case MAIN_CONFIG_SERVER_TYPE_DEVELOPMENT:
        define(
            "DB_NAME",
            "cncappsdev"
        );
        define(
            "BASE_DRIVE",
            dirname(__DIR__)
        );
        define(
            "SCR_DIR",
            "\\\\cncltd\\cnc\\Company\\scr\\dev"
        );
        define(
            "TECHNICAL_DIR",
            "\\\\cncltd\\cnc\\Customer\\dev\\Computer & Network Consultants Ltd"
        );
        define(
            "CUSTOMER_DIR_FROM_BROWSER",
            "//cncltd/cnc/customer/dev"
        );
        define(
            "CUSTOMER_DIR",
            "\\\\cncltd\\cnc\\Customer\\dev"
        );
        define(
            'CONFIG_CATCHALL_EMAIL',
            'HelpdeskTestSystemEmails@' . CONFIG_PUBLIC_DOMAIN . ', xavi@pavilionweb.co.uk'
        );
        error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
        ini_set(
            'display_errors',
            'on'
        );
        ini_set("error_log", "E:\Sites\dev-error.log");
        error_log('this is a test');
        $GLOBALS['mail_options'] = array(
            'driver' => 'smtp',
            'host'   => 'cncltd-co-uk0i.mail.protection.outlook.com',
            'port'   => 25,
            'auth'   => false
        );
        define(
            'CONFIG_TEST_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SALES_EMAIL',
            'sales@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SALES_MANAGER_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SUPPORT_EMAIL',
            'support@cnc-ltd.co.uk'
        );
        define(
            'CONFIG_CUSTOMER_SERVICE_EMAIL',
            ' customerservice@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SUPPORT_MANAGER_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SUPPORT_ADMINISTRATOR_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_HELP_DESK_EMAIL',
            'helpdeskE-Mails@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_PREPAY_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define('PORTAL_URL', DEV_PORTAL_URL);
        break;
    case MAIN_CONFIG_SERVER_TYPE_LIVE:
        // email addresses
        define(
            'CONFIG_CATCHALL_EMAIL',
            'HelpdeskTestSystemEmails@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            "TECHNICAL_DIR",
            "\\\\cncltd\\cnc\\Company\\Technical\\Asset List Export"
        );
        define(
            "BASE_DRIVE",
            dirname(__DIR__)
        );
        define(
            "SCR_DIR",
            "\\\\cncltd\\cnc\\Customer\\scr"
        );
        define(
            "CUSTOMER_DIR",
            "\\\\cncltd\\cnc\\Customer"
        );
        define(
            "COMPANY_DIR_FROM_BROWSER",
            "//cncltd/cnc/Company"
        );
        define(
            "CUSTOMER_DIR_FROM_BROWSER",
            "//cncltd/cnc/customer"
        );
        define(
            'CONFIG_TEST_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SALES_EMAIL',
            'sales@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SALES_MANAGER_EMAIL',
            'garyj@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SUPPORT_EMAIL',
            'support@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            "DB_NAME",
            "cncapps"
        );
        define(
            'CONFIG_CUSTOMER_SERVICE_EMAIL',
            ' customerservice@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SUPPORT_MANAGER_EMAIL',
            'SDmanager@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SUPPORT_ADMINISTRATOR_EMAIL',
            'SDmanager@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_HELP_DESK_EMAIL',
            'helpdeskE-Mails@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_PREPAY_EMAIL',
            'PrePayOverFixedAmount@cnc-ltd.co.uk'
        );
        error_reporting(E_ALL & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
        ini_set(
            'display_errors',
            'off'
        );
        ini_set("error_log", "E:\Sites\live-error.log");
        define('PORTAL_URL', "https://www.cnc-ltd.co.uk");
        $GLOBALS['mail_options'] = array(
            'driver' => 'smtp',
            'host'   => 'cncltd-co-uk0i.mail.protection.outlook.com',
            'port'   => 25,
            'auth'   => false
        );
        break;
    case MAIN_CONFIG_SERVER_TYPE_TEST:
        define(
            "DB_NAME",
            "cnctest"
        );
        define(
            "BASE_DRIVE",
            dirname(__DIR__)
        );
        define(
            "TECHNICAL_DIR",
            "\\\\cncltd\\cnc\\Customer\\dev\\Computer & Network Consultants Ltd"
        );
        define(
            "SCR_DIR",
            "\\\\cncltd\\cnc\\Company\\scr\\dev"
        );
        define(
            "CUSTOMER_DIR_FROM_BROWSER",
            "//cncltd/cnc/customer/dev"
        );
        define(
            "CUSTOMER_DIR",
            "\\\\cncltd\\cnc\\Customer\\dev"
        );
        define(
            'CONFIG_CATCHALL_EMAIL',
            'HelpdeskTestSystemEmails@' . CONFIG_PUBLIC_DOMAIN . ', xavi@pavilionweb.co.uk'
        );
//            error_reporting(E_ALL & ~E_STRICT)
        error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
        ini_set(
            'display_errors',
            'on'
        );
        ini_set("error_log", "E:\\Sites\\test-error.log");
        $GLOBALS['mail_options'] = array(
            'driver' => 'smtp',
            'host'   => 'cncltd-co-uk0i.mail.protection.outlook.com',
            'port'   => 25,
            'auth'   => false
        );
        define(
            'CONFIG_TEST_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SALES_EMAIL',
            'sales@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SALES_MANAGER_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SUPPORT_EMAIL',
            'support@cnc-ltd.co.uk'
        );
        define(
            'CONFIG_CUSTOMER_SERVICE_EMAIL',
            ' customerservice@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SUPPORT_MANAGER_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SUPPORT_ADMINISTRATOR_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_HELP_DESK_EMAIL',
            'helpdeskE-Mails@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_PREPAY_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define('PORTAL_URL', DEV_PORTAL_URL);
        break;
    case MAIN_CONFIG_SERVER_TYPE_WEBSITE:
        define(
            "DB_NAME",
            "cncweb"
        );
        define(
            "TECHNICAL_DIR",
            "\\\\cncltd\\cnc\\Customer\\dev\\Computer & Network Consultants Ltd"
        );
        define(
            "BASE_DRIVE",
            dirname(__DIR__)
        );
        define(
            "SCR_DIR",
            "\\\\cncltd\\cnc\\Company\\scr\\dev"
        );
        define(
            "CUSTOMER_DIR_FROM_BROWSER",
            "//cncltd/cnc/customer/dev"
        );
        define(
            "CUSTOMER_DIR",
            "\\\\cncltd\\cnc\\Customer\\dev"
        );
        define(
            'CONFIG_CATCHALL_EMAIL',
            'HelpdeskTestSystemEmails@' . CONFIG_PUBLIC_DOMAIN . ', xavi@pavilionweb.co.uk'
        );
        error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
        ini_set(
            'display_errors',
            'on'
        );
        ini_set("error_log", "E:\\Sites\\web-error.log");
        $GLOBALS['mail_options'] = array(
            'driver' => 'smtp',
            'host'   => 'cncltd-co-uk0i.mail.protection.outlook.com',
            'port'   => 25,
            'auth'   => false
        );
        define(
            'CONFIG_TEST_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SALES_EMAIL',
            'sales@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SALES_MANAGER_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SUPPORT_EMAIL',
            'support@cnc-ltd.co.uk'
        );
        define(
            'CONFIG_CUSTOMER_SERVICE_EMAIL',
            ' customerservice@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SUPPORT_MANAGER_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SUPPORT_ADMINISTRATOR_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_HELP_DESK_EMAIL',
            'helpdeskE-Mails@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_PREPAY_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define('PORTAL_URL', DEV_PORTAL_URL);
        break;
    case MAIN_CONFIG_SERVER_TYPE_DESIGN:
        define(
            "DB_NAME",
            "cnctest"
        );
        define(
            "TECHNICAL_DIR",
            "\\\\cncltd\\cnc\\Customer\\dev\\Computer & Network Consultants Ltd"
        );
        define(
            "BASE_DRIVE",
            dirname(__DIR__)
        );
        define(
            "SCR_DIR",
            "\\\\cncltd\\cnc\\Company\\scr\\dev"
        );
        define(
            "CUSTOMER_DIR_FROM_BROWSER",
            "//cncltd/cnc/customer/dev"
        );
        define(
            "CUSTOMER_DIR",
            "\\\\cncltd\\cnc\\Customer\\dev"
        );
        define(
            'CONFIG_CATCHALL_EMAIL',
            'HelpdeskTestSystemEmails@' . CONFIG_PUBLIC_DOMAIN
        );
        error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
        ini_set(
            'display_errors',
            'on'
        );
        ini_set("error_log", "E:\\Sites\\design-error.log");
        $GLOBALS['mail_options'] = array(
            'driver' => 'smtp',
            'host'   => 'cncltd-co-uk0i.mail.protection.outlook.com',
            'port'   => 25,
            'auth'   => false
        );
        define(
            'CONFIG_TEST_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SALES_EMAIL',
            'sales@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SALES_MANAGER_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SUPPORT_EMAIL',
            'support@cnc-ltd.co.uk'
        );
        define(
            'CONFIG_CUSTOMER_SERVICE_EMAIL',
            ' customerservice@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SUPPORT_MANAGER_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SUPPORT_ADMINISTRATOR_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_HELP_DESK_EMAIL',
            'helpdeskE-Mails@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_PREPAY_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define('PORTAL_URL', DEV_PORTAL_URL);
        $GLOBALS['request_mail_options'] = array(
            'host'     => 'cncmx01',
            'port'     => 143,
            'user'     => 'devasr',
            'password' => 'Unread01$'
        );
        break;
    case MAIN_CONFIG_SERVER_TYPE_DEV2:
        define(
            "DB_NAME",
            "mustafa"
        );
        define(
            "BASE_DRIVE",
            dirname(__DIR__)
        );
        define(
            "SCR_DIR",
            "\\\\cncltd\\cnc\\Company\\scr\\dev"
        );
        define(
            "TECHNICAL_DIR",
            "\\\\cncltd\\cnc\\Customer\\dev\\Computer & Network Consultants Ltd"
        );
        define(
            "CUSTOMER_DIR_FROM_BROWSER",
            "//cncltd/cnc/customer/dev"
        );
        define(
            "CUSTOMER_DIR",
            "\\\\cncltd\\cnc\\Customer\\dev"
        );
        define(
            'CONFIG_CATCHALL_EMAIL',
            'HelpdeskTestSystemEmails@' . CONFIG_PUBLIC_DOMAIN
        );
        error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
        ini_set(
            'display_errors',
            'on'
        );
        ini_set("error_log", "E:\Sites\dev2-error.log");
        $GLOBALS['mail_options'] = array(
            'driver' => 'smtp',
            'host'   => 'cncltd-co-uk0i.mail.protection.outlook.com',
            'port'   => 25,
            'auth'   => false
        );
        define(
            'CONFIG_TEST_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SALES_EMAIL',
            'sales@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SALES_MANAGER_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SUPPORT_EMAIL',
            'support@cnc-ltd.co.uk'
        );
        define(
            'CONFIG_CUSTOMER_SERVICE_EMAIL',
            ' customerservice@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_SUPPORT_MANAGER_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_SUPPORT_ADMINISTRATOR_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define(
            'CONFIG_HELP_DESK_EMAIL',
            'helpdeskE-Mails@' . CONFIG_PUBLIC_DOMAIN
        );
        define(
            'CONFIG_PREPAY_EMAIL',
            CONFIG_CATCHALL_EMAIL
        );
        define('PORTAL_URL', DEV_PORTAL_URL);
        break;

} // end switch
define(
    'API_URL',
    PORTAL_URL . '/api'
);
define(
    'PORTAL_FEEDBACK_URL',
    PORTAL_URL . '/service-request-feedback/?token='
);
define(
    'CONFIG_LDAP_DOMAINCONTROLLER',
    'cncdc1'
);
define(
    'CONFIG_SECONDARY_LDAP_DOMAINCONTROLLER',
    'cncdc03'
);
define(
    'CONFIG_LDAP_DOMAIN',
    'cncltd'
);
define(
    'PDF_TEMP_DIR',
    BASE_DRIVE . "/htdocs/pdfTemp"
);
define(
    'PDF_RESOURCE_DIR',
    BASE_DRIVE . '/htdocs/PDF-resources'
);
define(
    'INTERNAL_DOCUMENTS_FOLDER',
    BASE_DRIVE . '/serviceRequestsDocuments'
);
define(
    "APPLICATION_DIR",
    BASE_DRIVE . "/cnccode"
);
define(
    'APPLICATION_LOGS',
    BASE_DRIVE . '/logs'
);
define(
    "POWERSHELL_DIR",
    BASE_DRIVE . "\powershell"
);
define(
    "SWEETCODE_DIR",
    BASE_DRIVE . "/sweetcode"
);
define(
    "IMAGES_DIR",
    BASE_DRIVE . "/htdocs/images"
);
define(
    "QUOTES_DIR",
    BASE_DRIVE . "/htdocs/quotes"
);
define(
    "DELIVERY_NOTES_DIR",
    BASE_DRIVE . "/htdocs/delivery_notes"
);
define(
    "LETTER_TEMPLATE_DIR",
    BASE_DRIVE . "/htdocs/letter_templates"
);
define(
    "EMAIL_TEMPLATE_DIR",
    BASE_DRIVE . "/htdocs/email_templates"
);
define(
    "SAGE_EXPORT_DIR",
    BASE_DRIVE . "/htdocs/export"
);
define(
    "DB_USER",
    "webuser"
);
define(
    "DB_PASSWORD",
    "CnC1988"
);
define(
    "CONFIG_SQL_LOG",
    BASE_DRIVE . "/htdocs/log_file/sql_log.html"
);
define(
    'RECEIPT_PATH',
    BASE_DRIVE . '/receipts/'
);
$cfg['quote_path'] = BASE_DRIVE . "/htdocs/quotes";
define(
    "PHPLIB_SESSIONS_DIR",
    BASE_DRIVE . "/sessions/"
);
$GLOBALS['db_options'] = array(
    'type'       => 'db',
    'dsn'        => 'mysqli://' . DB_USER . ':' . DB_PASSWORD . '@' . DB_HOST . '/' . DB_NAME,
    'mail_table' => 'mail_queue'
);
require BASE_DRIVE . '/vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader('', __DIR__ . '/../twig');
$loader->addPath('internal', 'internal');
$loader->addPath('customerFacing', 'customerFacing');
$twig = new Environment(
    $loader, [
               "cache" => __DIR__ . '/../cache',
               "debug" => $server_type !== MAIN_CONFIG_SERVER_TYPE_LIVE,
           ]
);
$twig->addFilter(
    new TwigFilter(
        'MBtoGB', function ($string) {
        if (!is_numeric($string)) {
            return '';
        }
        return number_format($string / 1024) . 'GB';
    }
    )
);
$twig->addExtension(new \Twig\Extra\Intl\IntlExtension());
$twig->addExtension(new \Twig\Extension\DebugExtension());
define(
    'DOMPDF_ENABLE_AUTOLOAD',
    false
);
define(
    "USER_KA",
    1
);
define(
    "USER_PM",
    61
);
define(
    "USER_GL",
    3
);
define(
    "USER_GJ",
    2
);
define(
    "USER_AC",
    29
);
define(
    "USER_CA",
    70
);
define(
    "USER_RH",
    49
);
define(
    "USER_PS",
    65
);
define(
    "USER_MW",
    57
);
define(
    "USER_AB",
    71
);      // alison
define(
    "USER_JM",
    60
);      // jon Moody
define(
    "USER_SYSTEM",
    67
);
/*
List of userIDs that can add managers comments to service requests
*/
$GLOBALS['can_add_manager_comment'] = array(USER_AC, USER_GL, USER_GJ, USER_RH, USER_KA, 110);
/*
When automated emails coming in from unrecognised email addresses with these domains,
do not attempt to match a customer whos contacts have the domain
*/
$GLOBALS['exclude_sr_email_domains'] = array(
    'gmail.com',
    'googlemail.com',
    'hotmail.com',
    'hotmail.co.uk',
    'theaccessgroup.com'
);
define(
    'CONFIG_SERVICE_REQUEST_DESC',
    'Service Request'
);  // Description used in system
define(
    "PDF_DIR",
    APPLICATION_DIR . '/fpdf'
);
$cfg["path"] = APPLICATION_DIR;
define(
    "SHOW_TIMINGS",
    FALSE
);            // Causes CTController to display script and page execute times
define(
    "CONFIG_IDIOT_GUARD_ON",
    FALSE
);            // Causes random string to all urls generated
// by Controller::buildLink() to avoid caching
// --------------------------------------------------------------------------
define(
    "MAX_PAGE_TITLE",
    95
);        // For browser page title-bar
// I want to start using constants for global app settings
// defaults
define(
    'CONFIG_DEF_ITEMTYPEID',
    1
);                    // default item type when creating new items
define(
    'CONFIG_SERVER_ITEMTYPEID',
    16
);                // Server item type
define(
    'CONFIG_DUO_ITEMID',
    17540
);
define(
    'CONFIG_SERVERCARE_ITEMTYPEID',
    55
);
define(
    'CONFIG_SERVICEDESK_ITEMTYPEID',
    56
);
define(
    'CONFIG_PREPAY_ITEMTYPEID',
    57
);
define(
    'CONFIG_WEBROOT_ITEMTYPEID',
    17619
);
define(
    'CONFIG_2NDSITE_LOCAL_ITEMTYPEID',
    58
);
define(
    'CONFIG_2NDSITE_CNC_ITEMTYPEID',
    59
);
define(
    'CONFIG_DEF_PREPAY_ITEMID',
    4111
);                // general support contract
define(
    'CONFIG_DEF_PREPAY_TOPUP_ITEMID',
    6448
);    // general support contract topup
define(
    'CONFIG_DEF_SERVERGUARD_ANNUAL_CHARGE_ITEMID',
    12182
);        // CNC Server Guard Annual Charge
define(
    'CONFIG_INSTALLATION_ITEMID',
    9251
);
define(
    'CONFIG_SERVICEDESK_ITEMID',
    6915
);    // service desk renewal item
define(
    'CONFIG_CONSULTANCY_DAY_LABOUR_ITEMID',
    1502
);
define(
    'CONFIG_CONSULTANCY_OUT_OF_HOURS_LABOUR_ITEMID',
    1503
);
define(
    'CONFIG_CONSULTANCY_HOURLY_LABOUR_ITEMID',
    2237
);
define(
    'CONFIG_SALES_STOCK_CUSTOMERID',
    2511
);
define(
    'CONFIG_MAINT_STOCK_CUSTOMERID',
    2512
);
define(
    'CONFIG_OPERATING_STOCK_CUSTOMERID',
    2513
);
define(
    'CONFIG_ASSET_STOCK_CUSTOMERID',
    2514
);
define(
    'CONFIG_INTERNAL_CUSTOMERID',
    282
);
define(
    'CONFIG_SALES_STOCK_SUPPLIERID',
    53
);
define(
    'CONFIG_MAINT_STOCK_SUPPLIERID',
    322
);
define(
    'CONFIG_STANDARD_TEXT_TYPE_EMAIL',
    2
);
define(
    'CONFIG_STANDARD_TEXT_TYPE_SALES_REQUEST',
    5
);
define(
    'CONFIG_DEFAULT_MEETING_USERID',
    44
); // for use on the client information form (Graham)
define(
    'CONFIG_HEALTHCHECK_ACTIVITY_USER_ID',
    49
); // roger
define(
    'CONFIG_SCHEDULED_TASK_USER_ID',
    1
); // for use on the client information form (Graham)
// renewal types
define(
    'CONFIG_BROADBAND_RENEWAL_TYPE_ID',
    1
);
define(
    'CONFIG_CONTRACT_RENEWAL_TYPE_ID',
    2
);
define(
    'CONFIG_QUOTATION_RENEWAL_TYPE_ID',
    3
);
define(
    'CONFIG_DOMAIN_RENEWAL_TYPE_ID',
    4
);
define(
    'CONFIG_HOSTING_RENEWAL_TYPE_ID',
    5
);
define(
    'CONFIG_CONTRACT_RENEWAL_SERVICEDESK',
    'CNC ServiceDesk Contract'
);
define(
    'CONFIG_TOPUP_ACTIVITY_TYPE_ID',
    37
); // for use on the client information form (Graham)
define(
    'CONFIG_RESOLVED_ACTIVITY_TYPE_ID',
    35
); // for resolved call activity type
define(
    'CONFIG_COMPLETED_ACTIVITY_TYPE_ID',
    35
); // for escalated call activity type
define(
    'CONFIG_FIXED_ACTIVITY_TYPE_ID',
    57
);
define(
    'CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID',
    60
);
define(
    'CONFIG_SALES_ACTIVITY_TYPE_ID',
    43
);
define(
    'CONFIG_SERVER_HEALTH_CHECK_ACTIVITY_TYPE_ID',
    12
);
define(
    'CONFIG_SERVER_HEALTH_CHECK_CHECKLIST_ACTIVITY_TYPE_ID',
    48
);
define(
    'CONFIG_SERVER_HEALTH_CHECK_OFF_SITE_ACTIVITY_TYPE_ID',
    12
);
define(
    'CONFIG_SERVER_HEALTH_CHECK_ON_SITE_ACTIVITY_TYPE_ID',
    50
);
define(
    'CONFIG_SERVER_GUARD_UPDATE_ACTIVITY_TYPE_ID',
    55
);
define(
    'CONFIG_2NDSITE_BACKUP_ACTIVITY_TYPE_ID',
    49
);
define(
    'CONFIG_CONTRACT_ADJUSTMENT_ACTIVITY_TYPE_ID',
    39
);          // used when auto generating travel
define(
    'CONFIG_TRAVEL_ACTIVITY_TYPE_ID',
    6
);                    // used when auto generating travel
define(
    'CONFIG_ENGINEER_TRAVEL_ACTIVITY_TYPE_ID',
    22
);        // used in prepay statements
define(
    'CONFIG_PROACTIVE_SUPPORT_ACTIVITY_TYPE_ID',
    36
);    //  "    "   "
define(
    'CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID',
    11
);
define(
    'CONFIG_REMOTE_TELEPHONE_ACTIVITY_TYPE_ID',
    8
);
define(
    'CONFIG_INITIAL_ACTIVITY_TYPE_ID',
    51
); // Initial problem activity
define(
    'CONFIG_VISIT_REQUEST_ACTIVITY_TYPE_ID',
    21
);
define(
    'CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID',
    59
);
define(
    'CONFIG_TIME_REQUEST_ACTIVITY_TYPE_ID',
    61
);
define(
    'CONFIG_2NDSITE_BACKUP_ACTIVITY_CATEGORY_ID',
    55
);
define(
    'CONFIG_TRAVEL_ACTIVITY_CATEGORY_ID',
    30
);
define(
    'CONFIG_UNKNOWN_ACTIVITY_CATEGORY_ID',
    59
);
define(
    'CONFIG_LOGGED_FOR_INFO_ACTIVITY_CATEGORY_ID',
    9
);
define(
    'CONFIG_NOTHING_FOUND_ROOT_CAUSE_ID',
    54
);
define(
    'CONFIG_CONTRACT_RENEWAL_DAYS',
    45
);
define(
    'CONFIG_SALES_FURTHER_ACTION_ID',
    2
);
define(
    'CONFIG_VISIT_FURTHER_ACTION_ID',
    4
);
// payment terms
define(
    'CONFIG_PAYMENT_TERMS_30_DAYS',
    9
);
define(
    'CONFIG_PAYMENT_TERMS_DIRECT_DEBIT',
    11
);
define(
    'CONFIG_PAYMENT_TERMS_NO_INVOICE',
    7
);
/*
Prepay alert limit
Amount of prepay activity for a service request above which a warning email is sent.
*/
define(
    'CONFIG_PREPAY_ALERT_LIMIT',
    100
);
define(
    'CONFIG_HEADER_GSC_STATEMENT_FLAG',
    'mailshot8Flag'
);    // GSC statement contact flag column
define(
    'CONFIG_HEADER_INVOICE_CONTACT',
    'mailshot2Flag'
);     // Customer contact to send invoices to
define(
    'CONFIG_HEADER_DAILY_OPEN_SR_REPORT',
    'mailshot11Flag'
);
// Phone numbers
define(
    'CONFIG_IT_SUPPORT_PHONE',
    '01273 384111'
);
define(
    'CONFIG_PHONE_SYSTEM_SUPPORT_PHONE',
    '01273 384111'
);
define(
    'DATE_MYSQL_DATE',
    'Y-m-d'
);
define(
    'DATE_MYSQL_TIME',
    'H:i:s'
);
define(
    'CONFIG_MYSQL_TIME_HOURS_MINUTES',
    'H:i'
);
define(
    'DATE_MYSQL_DATETIME',
    DATE_MYSQL_DATE . ' ' . DATE_MYSQL_TIME
);
define(
    'DATE_CNC_DATE_FORMAT',
    'd/m/Y'
);
define(
    'DATE_CNC_DATE_TIME_FORMAT',
    DATE_CNC_DATE_FORMAT . " H:i:s"
);
$cfg["postToSco"]   = FALSE;
$cfg["txt_chevron"] = "&gt;";
// System paths and URLs
$cfg["cnclogo_path"]         = IMAGES_DIR . '/cnc_logo.png';
$cfg["cncaddress_path"]      = IMAGES_DIR . '/cncaddress.gif';
$cfg["cncwatermark_path"]    = IMAGES_DIR . '/CNC_watermarkActualSize.png';
$cfg["php_extension"]        = ".php";
$cfg["html_extension"]       = ".html";
$cfg["home"]                 = "index.php";
$cfg["path_templates"]       = APPLICATION_DIR . "/templates";
$cfg["path_test_templates"]  = APPLICATION_DIR . "/test_templates";
$cfg["path_lib"]             = APPLICATION_DIR . "/lib";
$cfg["path_lang_strings"]    = APPLICATION_DIR . "/localise_english.inc.php";
$cfg["path_test_strings"]    = APPLICATION_DIR . "/localise_test.inc.php";
$cfg["path_dbe"]             = APPLICATION_DIR . "/data_classes";
$cfg["path_bu"]              = APPLICATION_DIR . "/business_classes";
$cfg["path_ct"]              = APPLICATION_DIR . "/controller_classes";
$cfg["path_gc"]              = SWEETCODE_DIR . "/generic_classes";
$cfg["path_func"]            = APPLICATION_DIR . "/functions";
$cfg["path_db_backup"]       = "\\\\cncnas2\\DATA\\CNCAPPS_Raw_Backup";
$cfg["path_uc"]              = APPLICATION_DIR . "/utility_classes";
$cfg["path_phplib_classes"]  = APPLICATION_DIR . "/lib";
$cfg["path_phpunit_classes"] = APPLICATION_DIR . "/phpunit_classes";
// --------------------------------------------------------------------------
$path = BASE_DRIVE . "/php/PEAR";          // this is our own modified pear lib
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once(BASE_DRIVE . "/phplib4/prepend.php"); // need to do this on live site
require_once($cfg["path_phplib_classes"] . DIRECTORY_SEPARATOR . "local4.inc.php");
/** @var dbSweetcode $db */
$db = new dbSweetcode;
//$db->query("SET sql_mode = ''");    // strict mode off
//$pkdb= new dbSweetcode;
//$db->Debug = DEBUG;        // Turn this on if database debug output needed
?>