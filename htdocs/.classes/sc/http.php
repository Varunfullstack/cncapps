<?php
/**
* HTTP class
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
require_once(CONFIG_PATH_SC_CLASSES . 'object.php');
require_once(CONFIG_PATH_SC_CLASSES . 'file.php');
class SC_HTTP extends SC_Object {
	function requestVar($var, $default_value='') {
		return isset($_REQUEST[$var]) ? $_REQUEST[$var] : $default_value;
	}
	function requestFilterVar($var, $default_value='') {
		return isset($_REQUEST['filter'][$var]) ? $_REQUEST['filter'][$var] : $default_value;
	}
	function sessionArrayVar($array_name, $var, $default_value='') {
		return isset($_SESSION[$array_name][$var]) ? $_SESSION[$array_name][$var] : $default_value;
	}
	function getVar($var, $default_value='') {
		return isset($_GET[$var]) ? $_GET[$var] : $default_value;
	}
	function postVar($var, $default_value='') {
		return isset($_POST[$var]) ? $_POST[$var] : $default_value;
	}
	function serverVar($var, $default_value='') {
		return isset($_SERVER[$var]) ? $_SERVER[$var] : $default_value;
	}
	function cookieVar($var, $default_value='') {
		return isset($_COOKIE[$var]) ? $_COOKIE[$var] : $default_value;
	}
	function sessionVar($var, $default_value='') {
		return isset($_SESSION[$var]) ? $_SESSION[$var] : $default_value;
	}
	function redirect($page) {
		header("Location: ".$page);
		exit;
	}
	function redirectSelf() {
		SC_HTTP::redirect( SC_HTTP::serverVar('PHP_SELF') );
		exit;
	}
	function phpSelf() {
		return SC_HTTP::serverVar('PHP_SELF');
	}
	function requestURI() {
		return SC_HTTP::serverVar('REQUEST_URI');
	}
	function queryString() {
		return SC_HTTP::serverVar('QUERY_STRING');
	}
	function getScriptName()
	{
		$ret = str_replace('.php', '',SC_HTTP::serverVar('SCRIPT_NAME') );
		$ret = str_replace('/', '',$ret );
		return $ret;
	}
	function requestURIBase() {
		$ret = str_replace('/', '', SC_HTTP::serverVar('REQUEST_URI'));
		$ret = str_replace('.php', '', $ret);
		$ret = str_replace('?', '-', $ret);
		$ret = str_replace('&', '-', $ret);
		return $ret;
	}
	function convertRequestVars(&$business){
		// change _ in field names in request to .
		foreach($business->fields as $field => $attributes){
			$encoded_field_name = str_replace('.', '_', $field);
			$encoded_filter_name = $encoded_field_name . '_filter';
			$filter_name = $field . '_filter';
			if (array_key_exists($encoded_field_name, $_REQUEST)){
				$_REQUEST[$field] = $_REQUEST[$encoded_field_name];
			}
			if (array_key_exists($encoded_filter_name, $_REQUEST)){
				$_REQUEST[$filter_name] = $_REQUEST[$encoded_filter_name];
			}
		}
	}

	function setAbsentCheckboxesFalse(&$business)
	{
		// and deal with HTTP not checked = no field crap!
		// we will allways assume that no check-box field means set to false so...
		// MAKE SURE YOU ALWAYS PASS THEM FROM YOUR FORM!!!
		foreach($business->fields as $field => $attributes){
			if (
				$attributes['type'] == SC_DB_BOOL &&
				!array_key_exists($field, $_REQUEST)
			){
				$_REQUEST[$field] = 0;
			}
		}

	}
	/**
	* Conventional HTTP request
	*/
	function dealWithRequest(&$business) {
		$update_failed_message = false;

		SC_HTTP::convertRequestVars($business);

		$pk_value = SC_HTTP::requestVar($business->getPKName());

		SC_HTTP::setSaveCancelPage($business, $pk_value);

		if (SC_HTTP::requestVar('Save') != ''){				// Save button clicked so attempt update
			if ($pk_value != ''){
				$business->getRow($pk_value);								// get existing row from db
			}
			SC_HTTP::setAbsentCheckboxesFalse($business);
			if ( !$update_failed_message = $business->updateRowFrom($_REQUEST) ){
				require_once(CONFIG_PATH_SC_CLASSES . 'page_cache.php');
				if ( $business->validateFields() && $business->update() ) {
					SC_PageCache::reset();
					$save_page_session_var = SC_HTTP::getScriptName() . '_save_page';
					SC_HTTP::redirect( $_SESSION[$save_page_session_var] );
				}
				else{
					$update_failed_message = SC_DB_MSG_PROBLEMS_ON_FORM;
				}
			}
		}
		else{
			if ($pk_value != ''){
				$business->getRow($pk_value);								// get existing row from db
			}
			$business->updateRowFrom($_REQUEST);
		}
		return $update_failed_message;
	}

	function setSaveCancelPage(&$business, $pk_value)
	{

		$cancel_page_session_var = SC_HTTP::getScriptName() . '_cancel_page';
		if ( SC_HTTP::requestVar( 'cancel_page' ) ) {
			$_SESSION[$cancel_page_session_var] = SC_HTTP::requestVar( 'cancel_page' );
		}
		else{
			if ( !SC_HTTP::sessionVar( $cancel_page_session_var ) ){
				$_SESSION[$cancel_page_session_var] = SC_HTTP::phpSelf() . '?' . $business->getPKName() . '=' . $pk_value;
			}
		}

		$save_page_session_var = SC_HTTP::getScriptName() . '_save_page';
		if ( SC_HTTP::requestVar( 'save_page' ) ) {
			$_SESSION[	$save_page_session_var ] = SC_HTTP::requestVar( 'save_page' );
		}
		else{
			if (!SC_HTTP::sessionVar($save_page_session_var)){
				$_SESSION[$save_page_session_var] = SC_HTTP::phpSelf() . '?' . $business->getPKName() . '=' . $pk_value;
			}
		}
	}
	function browserIsExplorer()
	{
		return ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')  );
	}
	function javascriptIsEnabled()
	{
		return SC_HTTP::sessionVar( 'javascript_is_enabled' );
	}
	function ajaxIsEnabled()
	{
		return SC_HTTP::sessionVar( 'ajax_is_enabled' ) && SC_HTTP::javascriptIsEnabled();
	}
	function isAjaxTableUpdate()
	{
		return SC_HTTP::requestVar('ajax_table_update');
	}
	function isAjaxRequest()
	{
		return SC_HTTP::requestVar('is_ajax_request');
	}
}
?>