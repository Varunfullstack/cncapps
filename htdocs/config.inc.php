<?php
date_default_timezone_set('Europe/London');
require_once(__DIR__ . '/../../openWebAnalytics/owa_php.php');

$owa = new owa_php();
// Set the site id you want to track
$owa->setSiteId('b39a0f923d7f45bec2ccb7fa0435f82c');
// Uncomment the next line to set your page title
//$owa->setPageTitle('somepagetitle');
// Set other page properties
//$owa->setProperty('foo', 'bar');
//$owa->trackPageView();
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
   function stripslashes_deep($value)
   {
       $value = is_array($value) ?
                   array_map('stripslashes_deep', $value) :
                   stripslashes($value);

       return $value;
   }
   $_POST 		=	array_map('stripslashes_deep', $_POST);
   $_GET 			= array_map('stripslashes_deep', $_GET);
   $_REQUEST	= array_map('stripslashes_deep', $_REQUEST);
   $_COOKIE		= array_map('stripslashes_deep', $_COOKIE);
}
*/
/*
End Strip all slashes from request variables (includes cookies)
*/
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
$GLOBALS['php7'] = true;
$php7 = true;

if (isset($_SERVER['HTTP_HOST'])) {                // not set for command line calls
    switch ($_SERVER['HTTP_HOST']) {

        case 'cncapps':
            $server_type = MAIN_CONFIG_SERVER_TYPE_LIVE;

            break;
        case 'cncdev7:85':
            $server_type = MAIN_CONFIG_SERVER_TYPE_DEVELOPMENT;

            break;
        case 'cnctest:86':
            $server_type = MAIN_CONFIG_SERVER_TYPE_TEST;
            break;
        case 'cncweb:88':
            $server_type = MAIN_CONFIG_SERVER_TYPE_WEBSITE;

    }

    $GLOBALS['isRunningFromCommandLine'] = false;

} else {                // command line call so assume live and force HTTP_HOST value
    $script_path = strtolower($argv[0]);

    $server_type = MAIN_CONFIG_SERVER_TYPE_LIVE;
    $GLOBALS['isRunningFromCommandLine'] = true;
    $_SERVER['HTTP_HOST'] = 'cncapps';
}


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
//            error_reporting(E_ALL);
        error_reporting(E_ALL & ~E_WARNING);
        ini_set(
            'display_errors',
            'on'
        );

        $GLOBALS['mail_options'] =
            array(
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


        $GLOBALS['request_mail_options'] =
            array(
                'host'     => 'cncmx01',
                'port'     => 143,
                'user'     => 'devasr',
                'password' => 'Unread01$'
            );
        break;

    case MAIN_CONFIG_SERVER_TYPE_LIVE:
        // email addresses
        define(
            'CONFIG_CATCHALL_EMAIL',
            'HelpdeskTestSystemEmails@' . CONFIG_PUBLIC_DOMAIN
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
        error_reporting(E_ALL & ~E_WARNING & ~E_STRICT);
        ini_set(
            'display_errors',
            'off'
        );

        $GLOBALS['request_mail_options'] =
            array(
                'host'     => 'cncmx01',
                'port'     => 143,
                'user'     => 'asr',
                'password' => 'Unread01$'
            );

        $GLOBALS['mail_options'] =
            array(
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
//            error_reporting(E_ALL & ~E_STRICT)
        error_reporting(E_ALL & ~E_WARNING);
        ini_set(
            'display_errors',
            'on'
        );

        $GLOBALS['mail_options'] =
            array(
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

        $GLOBALS['request_mail_options'] =
            array(
                'host'     => 'cncmx01',
                'port'     => 143,
                'user'     => 'devasr',
                'password' => 'Unread01$'
            );
        break;

    case MAIN_CONFIG_SERVER_TYPE_WEBSITE:

        define(
            "DB_NAME",
            "cncweb"
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
        error_reporting(E_ALL & ~E_WARNING);
        ini_set(
            'display_errors',
            'on'
        );

        $GLOBALS['mail_options'] =
            array(
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

        $GLOBALS['request_mail_options'] =
            array(
                'host'     => 'cncmx01',
                'port'     => 143,
                'user'     => 'devasr',
                'password' => 'Unread01$'
            );
        break;

} // end switch

define(
    'CONFIG_LDAP_DOMAINCONTROLLER',
    'cncdc01'
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
    "APPLICATION_DIR",
    BASE_DRIVE . "/cnccode"
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
$cfg['quote_path'] = BASE_DRIVE . "/htdocs/quotes";
define(
    "PHPLIB_SESSIONS_DIR",
    BASE_DRIVE . "/sessions/"
);

$GLOBALS['db_options'] =
    array(
        'type'       => 'db',
        'dsn'        => 'mysqli://' . DB_USER . ':' . DB_PASSWORD . '@' . DB_HOST . '/' . DB_NAME,
        'mail_table' => 'mail_queue'
    );


require BASE_DRIVE . '/vendor/autoload.php';
// disable DOMPDF's internal autoloader if you are using Composer
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
$GLOBALS['can_add_manager_comment'] =
    array(USER_AC, USER_GL, USER_GJ, USER_RH, USER_KA, 110);

/* 
When automated emails coming in from unrecognised email addresses with these domains,
do not attempt to match a customer whos contacts have the domain
*/
$GLOBALS['exclude_sr_email_domains'] =
    array('gmail.com', 'googlemail.com', 'hotmail.com', 'hotmail.co.uk', 'theaccessgroup.com');

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
    'CONFIG_MYSQL_DATE',
    'Y-m-d'
);
define(
    'CONFIG_MYSQL_TIME',
    'H:i:s'
);
define(
    'CONFIG_MYSQL_DATETIME',
    CONFIG_MYSQL_DATE . ' ' . CONFIG_MYSQL_TIME
);

$cfg["postToSco"] = FALSE;
$cfg["txt_chevron"] = "&gt;";

// System paths and URLs
$cfg["cnclogo_path"] = IMAGES_DIR . '/cnc_logo.png';
$cfg["cncaddress_path"] = IMAGES_DIR . '/cncaddress.gif';
$cfg["cncwatermark_path"] = IMAGES_DIR . '/CNC_watermarkActualSize.png';

$cfg["php_extension"] = ".php";
$cfg["html_extension"] = ".html";
$cfg["home"] = "index.php";
$cfg["path_templates"] = APPLICATION_DIR . "/templates";
$cfg["path_test_templates"] = APPLICATION_DIR . "/test_templates";
$cfg["path_lib"] = APPLICATION_DIR . "/lib";
$cfg["path_lang_strings"] = APPLICATION_DIR . "/localise_english.inc.php";
$cfg["path_test_strings"] = APPLICATION_DIR . "/localise_test.inc.php";
$cfg["path_dbe"] = APPLICATION_DIR . "/data_classes";
$cfg["path_bu"] = APPLICATION_DIR . "/business_classes";
$cfg["path_ct"] = APPLICATION_DIR . "/controller_classes";
$cfg["path_gc"] = SWEETCODE_DIR . "/generic_classes";
$cfg["path_func"] = APPLICATION_DIR . "/functions";
$cfg["path_db_backup"] = "\\\\cncnas2\\DATA\\CNCAPPS_Raw_Backup";
$cfg["path_uc"] = APPLICATION_DIR . "/utility_classes";
$cfg["path_phplib_classes"] = APPLICATION_DIR . "/lib";
$cfg["path_phpunit_classes"] = APPLICATION_DIR . "/phpunit_classes";
// --------------------------------------------------------------------------

$path = BASE_DRIVE . "/php/PEAR";          // this is our own modified pear lib
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once(BASE_DRIVE . "/phplib4/prepend.php"); // need to do this on live site

require_once($cfg["path_phplib_classes"] . DIRECTORY_SEPARATOR . "local4.inc.php");
$db = new dbSweetcode;

//$db->query("SET sql_mode = ''");    // strict mode off
//$pkdb= new dbSweetcode;
//$db->Debug = DEBUG;        // Turn this on if database debug output needed
?>