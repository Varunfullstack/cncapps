<?php
/*
*	Cookie classes
*
* NOTE: Requires >= PHP 4.0.2

*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/

require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_SC_CLASSES . 'object.php');
require_once(CONFIG_PATH_SC_CLASSES . 'encryption.php');
require_once(CONFIG_PATH_SC_CLASSES . 'http.php');

define('COOKIE_GLUE',  '|'); 			// to divide the vars inside the cookie

// cookie format info
define('SC_AUTHCOOKIE_NAME',  'USERAUTH_CNCHELP');
define('SC_AUTHCOOKIE_MY_VERSION',  '1.6');			// >>>>> UPDATE IF CLASS CHANGES <<<<!!!
// when to expire the cookie and force login (seconds since create time)
define('SC_AUTHCOOKIE_EXPIRATION',  '28800');
// when to reissue the cookie to avoid unecessary expiration (seconds since create time)
define('SC_AUTHCOOKIE_REISSUE_TIME',  '28700');

/**
*	SC_AuthCookie
* @access public
* NOTE: >> If you change this class then make sure to update SC_AUTHCOOKIE_MY_VERSION so that
* the system ignores old versions of the cookie from clients <<
*/
class SC_AuthCookie extends SC_Object{
	/*
	Private
	*/
	var $created='';								// date
	var $user_id='';								// id for our cookie
	var $version='';								// so that we can version-control our cookie (see NOTE above)
	var $encryption='';							// encryption object
	/*
	Public
	*/
	var $user_data='';							// container for user data such as address, name etc
	/**
	* @access public
	*/
	function SC_AuthCookie($user_id = false, $user_data = array()) {
		$this->__construct($user_id, $user_data);
	}
	/**
	* @access public
	*/
	function __construct($user_id = false, $user_data = array()) {
		parent::__construct();
		$this->encryption = new SC_Encryption();
		if ($user_id) {													// id passed in so creating new cookie maybe with user data
			$this->user_id		= $user_id;
			$this->user_data	= $user_data;
			return;
		}
		else{																		// existing cookie
			if (array_key_exists( SC_AUTHCOOKIE_NAME, $_COOKIE) ) {
				$buffer = $this->_unpackage(SC_HTTP::cookieVar(SC_AUTHCOOKIE_NAME));
			}
			else{
				return false;
			}
		}
	}
	/**
	* @access public
	*/
	function set() {
		$cookie = $this->_package();
		setcookie(SC_AUTHCOOKIE_NAME, $cookie);
	}
	/**
	* @access public
	*/
	function validate() {
		if (!$this->version || !$this->created || !$this->user_id) {
			return false;
		}
		if ($this->version != SC_AUTHCOOKIE_MY_VERSION) {
			return false;
		}
		if (time() - $this->created > SC_AUTHCOOKIE_EXPIRATION) {
			return false;
		}
		else if (time() - $this->created > SC_AUTHCOOKIE_REISSUE_TIME) {
			$this->set();
		}
		return true;		// valid!
	}	
	/**
	* @access public
	*/
	function logout() {
		setcookie(SC_AUTHCOOKIE_NAME, '');
	}
	/**
	* @access private
	*/
	function _package() {
		$parts = array(
			SC_AUTHCOOKIE_MY_VERSION,
			time(),
			$this->user_id,
			serialize($this->user_data)
		);
		$cookie = implode(COOKIE_GLUE, $parts);
		return $this->encryption->encrypt($cookie);
	}
	/**
	* @access private
	*/
	function _unpackage($cookie) {
		$buffer = $this->encryption->decrypt($cookie);
		list(
			$this->version,
			$this->created,
			$this->user_id,
			$serialized_user_data
		) = explode(COOKIE_GLUE, $buffer);

		$this->user_data = unserialize($serialized_user_data);
		
		if (
			$this->version != SC_AUTHCOOKIE_MY_VERSION ||
			!$this->created ||
			!$this->user_id
			)
		{
			return false;
		}
		else{
			return true;
		}
	}
	/**
	* @access private
	*/
	function _reissue() {
		$this->created = time();
	}
}
/*
* SC_SessionCookie
*
* Container for session data
*
* @access Public
*/
class SC_SessionCookie  extends SC_Object{
	var $encryption='';
	var $name='';

	function SC_SessionCookie($name='MY_SESSION') {
		$this->encryption = new SC_Encryption();
		$this->name = $name;
	}
	/**
	* Read contents from the cookie
	*
	* @access public
	* @return mixed Contents of cookie
	*/
	function read() {
		if (isset($_COOKIE[$this->name])){
			$ret = unserialize(stripslashes($this->encryption->decrypt(SC_HTTP::cookieVar($this->name))));
		}
		else{
			$ret = '';
		}
		return $ret;
	}
	/**
	* write
	*
	* Write contents to the cookie
	*
	* @param mixed Contents to write to cookie
	* @param integer delay until expiry.
	* @access public
	*/
	function write( $contents, $expiration=3600) {
		setcookie($this->name, $this->encryption->encrypt(serialize($contents)), time() + $expiration); 
	}
	/**
	* destroy
	*
	* @access public
	*/
	function destroy() {
		setcookie($this->name, "", 0); 
	}
}
?>