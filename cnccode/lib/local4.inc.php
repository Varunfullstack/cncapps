<?php
/**
 * Database and Session Management Classes for CNC using PHPLib
 *
 * Note about PHP native sessions:
 *        The prepend.php file may be tweaked to use either native PHP4 or PHPLib sessions
 *        I have assumed that the best approach is to use native support for speed and automatic URL
 *        rewriting to include session ID.
 *
 * @author Karim Ahmed, Sweet Code Ltd
 *
 **/
require_once($cfg["path_func"] . "/Common.inc.php");
define('PHPLIB_CLASSNAME_DB', 'dbSweetcode');
define('PHPLIB_CLASSNAME_SESSION_CONTAINER', 'CtSweetcode');
define('PHPLIB_CLASSNAME_SESSION', 'seSweetcode');
define('PHPLIB_CLASSNAME_USER', 'usSweetcode');
define('PHPLIB_CLASSNAME_AUTH', 'auSweetcode');
define('PHPLIB_CLASSNAME_PERM', 'pmSweetcode');
define('PHPLIB_TABLE_SESSIONS', 'sessions');
define('PHPLIB_TABLE_AUTH', 'consultant');
define('PHPLIB_COLUMN_PERMS', 'cns_perms');
define('PHPLIB_COLUMN_USERID', 'cns_consno');
define('PHPLIB_COLUMN_USERNAME', 'cns_logname');
define('PHPLIB_COLUMN_PASSWORD', 'cns_password');
define('PHPLIB_PAGE_LOGIN', $cfg["path_templates"] . '/login.inc.html');
define('PHPLIB_PAGE_PERM_INVALID', $cfg["path_templates"] . '/perminvalid.inc.html');
define('PHPLIB_SESSION_VAR_INIT', APPLICATION_DIR . '/lib/sessioninit.inc.php');
define('PHPLIB_MSG_BAD_LOGIN', 'Please check User Name and Password. If all are correct please contact Gary or Graham');
define('PHPLIB_PERM_SALES', 'sales');
define('PHPLIB_PERM_ACCOUNTS', 'accounts');
define('PHPLIB_PERM_TECHNICAL', 'technical');
define('PHPLIB_PERM_MAINTENANCE', 'maintenance');
define('PHPLIB_PERM_REPORTS', 'reports');
define('PHPLIB_PERM_SUPERVISOR', 'supervisor');
define('PHPLIB_PERM_CUSTOMER', 'customer');
define('PHPLIB_PERM_RENEWALS', 'renewals');

/**
 * System needs to use a database class.
 **/
class dbSweetcode extends DB_Sql
{
    var $Host = DB_HOST;
    var $Database = DB_NAME;
    var $User = DB_USER;
    var $Password = DB_PASSWORD;


}

/**
 * Session needs to use a storage container (ct).
 **/
/*
class ctSweetcode extends CT_Sql {
  var $database_class = PHPLIB_CLASSNAME_DB;         ## Which database to connect...
  var $database_table = PHPLIB_TABLE_SESSIONS; 	## and find our session data in this table.
}
*/

class CtSweetcode extends CT_File
{
    var $file_path = PHPLIB_SESSIONS_DIR;  ## Path where to store the session files
}

class seSweetcode extends Session
{
    var $classname = PHPLIB_CLASSNAME_SESSION;
    var $cookiename = "";     ## defaults to classname
    var $magic = "Hocuspocus";  ## ID seed
    var $mode = "cookie";      ## We propagate session IDs with cookies
    var $fallback_mode = "get";
    var $lifetime = 480;                    ## 0 = do session cookies, else minutes
    var $that_class = PHPLIB_CLASSNAME_SESSION_CONTAINER;  ## name of data storage container
    var $auto_init = PHPLIB_SESSION_VAR_INIT;        ## initial session variables and values
    var $allowcache = "no";        ## "public", "private", or "no"
    var $gc_probability = 5;
}

class auSweetcode extends Auth
{
    var $classname = PHPLIB_CLASSNAME_AUTH;
    var $lifetime = 0;                    // never expire
    var $database_class = PHPLIB_CLASSNAME_DB;
    var $database_table = PHPLIB_TABLE_AUTH;

    function auth_loginform()
    {
        global $sess;
        global $_PHPLIB;
        global $cfg;
        //$GLOBALS['loginMessage'] = ''; // reset message
        include(PHPLIB_PAGE_LOGIN);
    }

    function authenticate_on_ldap($username, $password)
    {
        if (!$password) {
            return false;
        }
        $domain = CONFIG_LDAP_DOMAIN;
        $domaincontroller = CONFIG_LDAP_DOMAINCONTROLLER;

        $adServer = "ldap://" . $domaincontroller . "." . $domain . ".local";

        $ldap = ldap_connect($adServer);

        $ldaprdn = $domain . "\\" . $username;

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ldap, $ldaprdn, $password);

        if ($bind) {
            $ret = true;
        } else {
            $ret = false;
        }
        return $ret;
    }

    function auth_validatelogin()
    {

        if (isset($_POST["username"])) {
            $this->auth["uname"] = $_POST["username"];
        } else { // KA: added this so that PHP Notice isn't raised
            $_POST["username"] = '';
            $_POST["password"] = '';
        }

        $uid = false;
        /*
        Login from allowed client Ip range or localhost only
        */
        $allowedIpPattern = $this->get_allowed_ip_pattern();
        if ($GLOBALS ['server_type'] != MAIN_CONFIG_SERVER_TYPE_DEVELOPMENT &&
            !preg_match('/' . $allowedIpPattern . '/', $_SERVER['REMOTE_ADDR'])
        ) {
            $GLOBALS['loginMessage'] = 'Login blocked: You are not on the CNC network';
            return false;
        }

        if (
            ($GLOBALS ['server_type'] != MAIN_CONFIG_SERVER_TYPE_DEVELOPMENT
                || $GLOBALS['php7']
            ) &&
            !$this->authenticate_on_ldap(
                $_POST['username'],
                $_POST['password']
            )
        ) {
            $GLOBALS['loginMessage'] = 'Failed to authenticate';
            return false;
        }

        $this->db->query(sprintf("select %s, %s " .
            "        from %s " .
            "       where %s = '%s' ",
            PHPLIB_COLUMN_PERMS,
            PHPLIB_COLUMN_USERID,
            $this->database_table,
            PHPLIB_COLUMN_USERNAME,
            addslashes($_POST["username"])
        ));

        while ($this->db->next_record()) {
            if ($uid = $this->db->f(PHPLIB_COLUMN_USERID)) {
                $this->auth["perm"] = $this->db->f(PHPLIB_COLUMN_PERMS);

                $this->record_session_start(
                    $_SERVER['REMOTE_ADDR'],
                    $_POST['username'],
                    DATE('H:i:s')
                );

                $this->record_work_day_start_time($this->db->f(PHPLIB_COLUMN_USERID));
            } else {
                $GLOBALS['loginMessage'] = 'Failed to find credentials in database';

            }
        }
        return $uid;
    }


    function get_allowed_ip_pattern()
    {
        $ret = false;

        $this->db->query(
            "SELECT
        hed_allowed_client_ip_pattern
      FROM
        headert"
        );

        while ($this->db->next_record()) {
            $ret = $this->db->f('hed_allowed_client_ip_pattern');
        }
        return $ret;
    }

    function record_session_start(
        $ip,
        $user,
        $time
    )
    {
        $file_name = SAGE_EXPORT_DIR . '/session_log/' . date('Ymd') . '.csv';
        $handle = fopen($file_name, 'a+');
        fwrite(
            $handle,
            $ip . ',' .
            $user . ',' .
            $time . "\n"
        );
    }

    /**
     * Create record on user_time_log to record work day start time
     *
     * ONLY record if this is not a bank holiday and time is within work day hours
     *
     * @param mixed $userID
     */
    function record_work_day_start_time($userID)
    {

        $bankHolidays = common_getUkBankHolidays(date('Y'));

        /*
        Do not record if:
        */
        if (
            in_array(date('Y-m-d'), $bankHolidays) OR // holiday
            date('g') < 6 OR // before 6am
            date('g') > 18 OR // after 6pm
            date('N') > 5                                   // Sat or Sun
        ) {
            return;
        }

        $this->db->query(
            "SELECT
        team.level as teamLevel,
        consultant.standardDayHours
        
      FROM
        consultant
        JOIN team ON team.teamID = consultant.teamID
      WHERE
        cns_consno = $userID"
        );
        $this->db->next_record();
        $teamLevel = $this->db->Record['teamLevel'];
        $standardDayHours = $this->db->Record['standardDayHours'];

        $sql =

            "INSERT IGNORE INTO user_time_log
        (
        `userID`,
        `teamLevel`,
        `loggedDate`,
        `loggedHours`,
        `dayHours`,
        `startedTime` 
        ) 
      VALUES 
        (
          " . $userID . ",
          " . $teamLevel . ",
          DATE( NOW() ),
          0,
          " . $standardDayHours . ",
          TIME( NOW() )
        )";

        $this->db->query($sql);
    }
}

/**
 *    Permission levels
 * atomic so permissions may be combined in any way
 */
class pmSweetcode extends Perm
{
    var $classname = PHPLIB_CLASSNAME_PERM;
    var $permissions = array(
        PHPLIB_PERM_SALES => 1,
        PHPLIB_PERM_ACCOUNTS => 2,
        PHPLIB_PERM_TECHNICAL => 4,
        PHPLIB_PERM_MAINTENANCE => 8,
        PHPLIB_PERM_CUSTOMER => 16,
        PHPLIB_PERM_REPORTS => 32,
        PHPLIB_PERM_SUPERVISOR => 64,
        PHPLIB_PERM_RENEWALS => 128
    );

    function perm_invalid($does_have, $must_have)
    {
        global $perm, $auth, $sess;
        global $cfg;
        global $_PHPLIB;

        include(PHPLIB_PAGE_PERM_INVALID);
    }
}

?>