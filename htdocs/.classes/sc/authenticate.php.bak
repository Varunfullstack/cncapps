<?php
/**
*	SC_Authenticate class
*
* class to hold, authenticate and maintain currently logged-in orgainisation (AKA user)
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_SC_CLASSES . 'object.php');
require_once(CONFIG_PATH_SC_CLASSES . 'cookie.php');
require_once(CONFIG_PATH_SC_CLASSES . 'db.php');
require_once(CONFIG_PATH_SC_CLASSES . 'http.php');

class SC_Authenticate extends SC_Object {

	var $cookie = '';			// the container
	var $user = array();	// array of user data

	/**
	* @access public
	* @param <undefined class>	$user	Instance of any class that exposes method
	* bool checkCredentials($name, $password) to validate users
	*/
	function SC_Authenticate(&$user) {
		$this->__construct($user);
	}
	function __construct(&$user) {
		parent::__construct();
		if (!is_object($user)){
			$this->raiseError('$user not passed');
		}
		if (!method_exists ($user, 'checkCredentials')) {
			$this->raiseError('$user does not expose required checkCredentials method');
		}
		if (!method_exists ($user, 'setPassword')) {
			$this->raiseError('$user does not expose required setPassword method');
		}
		if (!method_exists ($user, 'setUsername')) {
			$this->raiseError('$user does not expose required setUsername method');
		}
		if (!method_exists ($user, 'getUserID')) {
			$this->raiseError('$user does not expose required userID method');
		}
		$this->user = & $user;
	}
	/**
	*	authenticate()
	* Call at top of every page that requires authentication
	*
	* @access public
	*/
	function authenticate() {
		$this->cookie = new SC_AuthCookie();
		if (!$this->cookie  || !$this->cookie->validate() ){
			SC_HTTP::redirect(CONFIG_PAGE_LOGIN . '?originating_uri=' . SC_HTTP::serverVar('REQUEST_URI'));
		}
		else{
			$this->user->row = $this->cookie->user_data;
		}
	}
	function setUsername($value){
		$this->user->setUsername($value);
	}
	function getUsername(){
		return $this->user->getUsername();
	}
	function getUserID(){
		return $this->user->getUserID();
	}
	function setPassword($value){
		$this->user->setPassword($value);
	}
	/**
	*	checkCredentials()
	* authenticat passed user name against DB
	*
	* @access public
	*/
	function checkCredentials() {
		
		$ret = false;
		
		if ($this->user->checkCredentials()){
			if ($this->cookie = new SC_AuthCookie($this->user->getUsername(), $this->user->row) ){
				$this->cookie->set();
				$ret = true;
			}
		}
		return $ret;
	}
} //end class SC_Authenticate
?>