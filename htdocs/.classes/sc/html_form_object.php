<?php
/**
* HTML form objects
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
* @access static
*/
define ('SC_HTML_FORM_OBJECT_TEXTAREA_COLUMNS',		5);
define ('SC_HTML_FORM_OBJECT_TEXTAREA_ROWS',			100);
define ('SC_HTML_FORM_OBJECT_MSG_NONE_SELECTED',	'None selected');
define ('SC_HTML_FORM_OBJECT_CHECKED',	'CHECKED');
require_once(CONFIG_PATH_SC_CLASSES . 'object.php');
require_once(CONFIG_PATH_SC_CLASSES . 'ajax.php');

class SC_HTMLFormObject extends SC_Object{

	var $business;							// object ref
	var $field_name;
	var $value 				= false;
	var $is_invalid		= false;
	var $min_length		= false;
	var $max_length		= false;
	var $on_change		= false;	
	var $on_key_down	= false;
	var $on_blur			= false;
	var $ajax_validation
										= false;
	var $class				= false;

	function SC_HTMLFormObject()
	{
		$this->__construct();
	}
	function __construct()
	{
		parent::__construct();
	}

	function render(
			$field_name,
			$value,
			$on_change				= false,	
			$on_key_down			= false,

			$business					= false,						// have to make it a copy so it can be false :-(
			$field						= false,

			$is_invalid				= false,
			$size							= false,
			$on_blur					= false,
			$max_length				= false,
			$class						= false,
			$validate					= false
	)
	{
		// for all instances
		$this->field_name		=	$field_name;
		$this->value				= $value;
		$this->on_change		= $on_change;
		$this->on_key_down	= $on_key_down;
		$this->on_blur			= $on_blur;
		$this->validate			= $validate;
		
		// for business objects
		if ( $business ){
			$this->business					= $business;

			$this->field						=	$field;

			$this->is_invalid				= $business->getErrorText($this->field) ? true : false;
			$this->size							= $business->getMaxLength($this->field) / 2.5;
			if (!$max_length){
				$this->max_length				= $business->getMaxLength($this->field) / 2.5;
			}
			$this->class						= $this->is_invalid ? 'errorBG' : '';	
		}
		else{
		// for non business objects
			$this->is_invalid		= $is_invalid;
			$this->size					= $size;
			$this->on_blur			= $on_blur;
			$this->max_length		= $max_length;
			$this->class				= $class;	
		}
	}
}
?>