<?php
/**
* class to handle PHP ajax operations
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
require_once($_SERVER['DOCUMENT_ROOT'] .	'/.config.php');
require_once(CONFIG_PATH_SC_CLASSES .		'object.php');
require_once(CONFIG_PATH_SC_CLASSES .		'http.php');
class SC_Ajax extends SC_Object{
	var $start_time;
	/*
	* Logs requests
	*/
	function request($request_uri=false){
		$_SESSION['ajax_is_enabled'] = true;
		$_SESSION['javascript_is_enabled'] = true;
		$request_uri .= '&is_ajax_request=1';
		if (SC_HTTP::sessionVar('show_ajax')){
			$this->start_time = microtime();
			$display_uri = str_replace( '&', CR . TAB . '&', $request_uri);
			$display_uri = str_replace( '?', CR . TAB . '?', $display_uri);
			@$_SESSION['ajax_request_log'] .= '<HR><PRE>Ajax PHP Request:' . CR . TAB . $display_uri  . '</PRE>';
		}
	}
	/*
	* Logs responses
	*/
	function response($response = false){
		$response .= SC_Ajax::setJavascriptIsEnabled();
		if (SC_HTTP::sessionVar('show_ajax')){
			$duration = microtime() - $this->start_time;
			@$_SESSION['ajax_request_log'] .= '<HR><PRE>Ajax Javascript Response (' . $duration . ' seconds) :' . CR . TAB . urlencode($response) . '</PRE>';
		}
		return $response;
	}
	function validation(&$business, $field = false){
		if (!$field) {
			$this->raiseError('$field not passed');
		}
		return
			'loadXMLDoc(\''.
				'validate_field.php' .
					'?class_name='	. get_class($business) .
					'&pk_value='		. $business->getPKValue() .
					'&field='				. $field . '\'' .
					' + buildFormFieldList(this.form) )';
	}
	function filter(&$business, $is_form_element=false){
		if ($is_form_element) {
		 $form_ref = 'this.form';
		}
		else{
		 $form_ref = 'this';
		}
		$javascript = 
			'loadXMLDoc(\''.
				SC_HTTP::phpSelf() .
				'?ajax_table_update=1\'';

		$javascript .=	' + buildFormFieldList(' . $form_ref . ') );';
				
		if ($form_ref != 'this.form' ) {
			$javascript .=
				$form_ref . '.elements[getFirstFocusElement(' . $form_ref . ')].focus();';
			$javascript .= 'return false;';

		}
		return $javascript;
	}
	function fieldSelect(&$business, $is_form_element=false){
		if ($is_form_element) {
		 $form_ref = 'this.form';
		}
		else{
		 $form_ref = 'this';
		}
		$javascript = 
			'loadXMLDoc(\''.
				SC_HTTP::phpSelf() .
				'?ajax_table_update=1\'';

		$javascript .=	' + buildFormDisplayFieldList(' . $form_ref . ') );';
				
		if ($form_ref != 'this.form' ) {
			$javascript .=
				$form_ref . '.elements[getFirstFocusElement(' . $form_ref . ')].focus();';
			$javascript .= 'return false;';
		}
		return $javascript;
	}
	function setJavascriptIsEnabled()
	{
		return
			'javascript:setJavascriptIsEnabled();';
	}
	function setAjaxIsEnabled()
	{
		return
			'javascript:setAjaxIsEnabled();';
	}
	function tellServerAjaxIsEnabled()
	{
		return
			'javascript:tellServerAjaxIsEnabled();';
	}
	function saveUserSession()
	{
		global $authenticate;
		return
			'javascript:saveUserSession(\''. $authenticate->getUserID() .'\');';
	}
	function ajaxIsEnabled()
	{
//		return
	//		'javascript:ajaxIsEnabled();';
		return SC_HTTP::sessionVar('ajax_is_enabled');
	}
	function pageResponse( $div_id, $contents )
	{
		$contents = str_replace( '\'', '\\\'', $contents );
		$contents = str_replace( "\r", '', $contents );
		$contents = str_replace( "\n", '', $contents );
		$javascript =
			'var im = document.getElementById(\'' . $div_id . '\');' . CR . TAB .
			'im.innerHTML = \''	.	$contents . '\';';
		return $this->response($javascript);
	}	
	function startPageProcesses()
	{
		return 'javascript:startPageProcesses();';
	}
	function killPageProcesses()
	{
		return 'javascript:killPageProcesses();';
	}
	function logoutAlert()
	{
		$javascript =
			'alert(\'You are no longer logged in! Refresh this page or click logout to log back in\');';
		return $javascript;
	}
	function renderLink( $link ){
/*
		if (	SC_HTTP::ajaxIsEnabled() && SC_HTTP::browserIsExplorer()  ){
			$ret =
					'href="javascript:;"onClick="loadXMLDoc(\'' . $link . '\')"';
		}
		else{
*/
			$ret =	'href="' . $link . '"';
//		}
		return $ret;
	}
}
?>