<?php
/**
*	Encryption
*
*	Provide encryption for any string
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
/*
This is our key to existing data and so must NOT be changed once live otherwise
we will not be able to decypher existing data! There shouldn't be any reason to 
change it unless it becomes known to a 3rd-party or this class resused for other
systems.
*/
require_once(CONFIG_PATH_SC_CLASSES . 'object.php');

define('SC_ENCRYPTION_KEY',			'hiu3e9834r989hj');
define('SC_ENCRYPTION_CYPHER',	'blowfish');
define('SC_ENCRYPTION_MODE',		'cfb');

class SC_Encryption extends SC_Object {
	/**
	*	$td
	*
	* Handle to the mcrypt module
	* @access private
	*/
	var $td='';
	/**
	*	String $key Encryption key
	* @access private
	*/
	var $key='';
	/**
	*	Boolean $mcrypt_available If we have the mcrypt lib available
	* @access private
	*/
	var $mcrypt_available='';
	/**
	*	Encryption
	*
	* PHP V4/5 cross-compatibility
	* @param	string	$cypher	User-supplied cypher(optional)
	* @param	string	$key		User-supplied key(optional)
	* @param	string	$mode		User-supplied mode(optional)
	* @return string	Encrypted text
	* @access public
	*/
	function SC_Encryption($cypher='', $key='', $mode=''){
		$this->__construct($cypher, $key, $mode);
	}
	/**
	* __construct
	*
	* @param	string	$cypher	User-supplied cypher(optional)
	* @param	string	$key		User-supplied key(optional)
	* @param	string	$mode		User-supplied mode(optional)
	* @return string	Encrypted text
	* @access private
	*/
	function __construct($cypher='', $key='', $mode=''){
		parent::__construct();
		$this->key = $key ? '' : SC_ENCRYPTION_KEY;
	
		if (function_exists('mcrypt_encrypt')) {
			$this->td = mcrypt_module_open (
				$cypher ? '' : SC_ENCRYPTION_CYPHER,
				'',
				$mode ? '' : SC_ENCRYPTION_MODE,
				''
			);
			$this->mcrypt_available = true;
		} else {
			$this->mcrypt_available = false;
		}
	}
	/**
	* encrypt
	*
	* Encrypt plain text using the encryption parameters
	* NOTE: mcrypt_enc_get_iv_size requires => PHP 4.0.2
	*
	* @access public
	* @param	string	Plain text to be encrypted
	* @return string	Encrypted text
	*/
	function encrypt($plaintext) {
		if ($this->mcrypt_available) {
			$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size ($this->td), MCRYPT_RAND);
			mcrypt_generic_init ($this->td, $this->key, $iv);
			$crypttext = mcrypt_generic ($this->td, $plaintext);
			mcrypt_generic_deinit ($this->td);
			return $iv.$crypttext;
		}
		else{
			return $plaintext;					// could do something else here
		}
	}
	/**
	* decrypt
	*
	* Decrypt plain text using the encryption parameters
	* NOTE: mcrypt_enc_get_iv_size requires => PHP 4.0.2
	*
	* @access public
	* @param	string	Encrypted text
	* @return	string	Plain text
	*/
	function decrypt($crypttext) {
		if ($this->mcrypt_available) {
			$iv_size = mcrypt_enc_get_iv_size ($this->td);
			$iv = substr($crypttext, 0, $iv_size);
			$crypttext = substr($crypttext, $iv_size);
			mcrypt_generic_init ($this->td, $this->key, $iv);
			$plaintext = mdecrypt_generic ($this->td, $crypttext);
			mcrypt_generic_deinit ($this->td);
			return $plaintext;
		}
		else{
			return $crypttext;
		}
	}
} // end of class SC_Encryption
?>