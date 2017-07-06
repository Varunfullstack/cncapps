<?php
/**
* HTML
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
define ('SC_HTML_TEXTAREA_COLUMNS',		5);
define ('SC_HTML_TEXTAREA_ROWS',			100);
define ('SC_HTML_MSG_NONE_SELECTED',	'None selected');
define ('SC_HTML_CHECKED',	'CHECKED');
require_once(CONFIG_PATH_SC_CLASSES . 'object.php');
require_once(CONFIG_PATH_SC_CLASSES . 'ajax.php');
class SC_HTML extends SC_Object{
	/**
	* Prepare string for text box tag (type=text)
	*/
	function textBox($text) {
		return trim(htmlspecialchars($text, ENT_QUOTES));
	}
	/**
	* Prepare string for text area tag (<textarea>)
	*/
	function textArea($text) {
		return trim($text);
	}
	function checkedValue($value) {
		return $value == 1 ? SC_HTML_CHECKED : '';
	}
	/**
	* Prepare string for display on HTML page
	*/
	function textDisplay($text) {
		return stripslashes(htmlspecialchars($text));
	}
	/**
	* Prepare and array of values for display on HTML page
	*/
	function textDisplayPrepare($object) {		// not passed by ref in case not an array
		if( is_array($object) ) {
			while( list($key,$val) = each($object) ){
				$object[$key] = htmlspecialchars(trim($val));
				$object[$key] = stripslashes(trim($val));
			}
		}
		else {
			$object = stripslashes(htmlspecialchars($object));
		}
		return $object;
	}
	/**
 	resizes images according to parameters passed in
	*/
	function imageResize($src, $dest, $width = 0, $height=0 ) {
		$quality=75; // needed for image quality!
		if ( file_exists($src) && isset($dest) ) {
			$srcSize   = getimagesize ($src);  // image src size 
			// to calculate aspect ratio use: ((Fixed/Height) * Width) - for both landscape and portrait, where Fixed is passed in   
			if( $width == 0 ) {
				// calc width
				$width = round( ($height / $srcSize[1]) * $srcSize[0] );
			}
			elseif( $height == 0 ) {
				// cal height
				$height = round(($width / $srcSize[0]) * $srcSize[1]);
			}
			$destImage = imagecreatetruecolor($width, $height); // true color image, with anti-aliasing 
			// now create image in format specified by dest
			switch(substr($dest,-4)) {
				case ".jpg":
				case "jpeg":
					$srcImage = imagecreatefromjpeg ($src); 
					imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $width, $height, $srcSize[0], $srcSize[1]); // resampling 
					imagejpeg($destImage, $dest, $quality); 
				break;
				case ".gif":
					print "<b class=error>GIF support not available yet</b>";
					break;
					// no support in GD 2 until 2-4 
					// see http://www.boutell.com/gd/faq.html
					$srcImage = imagecreatefromgif ($src); 
					imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $width, $height, $srcSize[0], $srcSize[1]); // resampling 
					imagegif($destImage, $dest); 
				break;
				case ".png":
					$srcImage = imagecreatefrompng ($src); 
					imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $width, $height, $srcSize[0], $srcSize[1]); // resampling 
					imagepng($destImage, $dest); 
				break;
			} // end switch
			imagedestroy($srcImage);
			imagedestroy($destImage);
		} //    if (file_exists($src)  && isset($dest))
	}
	function preformat($value){
		$method = 'preformat';
		return '<PRE>' . $value . '</PRE>';
	}
	function helpTooltip(&$business, $field){
		$method = 'helpTooltip';
		
		$ret = 	"<P><strong>" . SC_String::display($business->getLabel($field)) . "</strong></P>".
						"<P>" . $business->getHelpText($field) . "</P>";

		if ($business->isRequired($field)){
			$ret .=	"<p>" . SC_String::display('Required field') . "</p>";
		}

		if ($business->getMinLength($field) != ''){
			$ret .= SC_String::display('Minimum length') . ": " . $business->getMinLength($field) . "<BR>";
		}

		if ($business->getMaxLength($field) != ''){
			$ret .= SC_String::display('Maximum length') . ": " . $business->getMaxLength($field) . "<BR>";
		}
		return $ret;
	}
	function selected($field, $value){
		return $field == $value ? 'SELECTED' : '';
	}
	function helpMouseover(&$business, $field){
		return 'onmouseover="this.T_OPACITY=80;this.T_SHADOWWIDTH=5;this.T_STICKY=1;this.T_OFFSETY=-10;this.T_OFFSETX=15;return escape(\'' .	SC_HTML::helpTooltip($business, $field) . '\')"'; 
	}
	function readOnly($value)
	{
		return $value ? 'READONLY' : '';
	}
	/**
	*	form_object.php
	*
	*/
	function formObject(
		&$business,
		$field,
		$value = '',
		$form_field_name =false,
		$ajax_validation = true,
		$on_change = false,
		$on_key_down = false,
		$style = false,
		$max_length = false
	){
		if ( !$field ){
			SC_Object::staticRaiseError('form_object.php: $field not set');
		}
		if (!is_object($business)){
			SC_Object::staticRaiseError('form_object.php: $business not an object');
		}
		if ( $form_field_name ){
			$field_name = $form_field_name;
		}
		else{
			$field_name = $field;
		}
				
		$type 	= $business->getType($field);
		
		switch ($type) {
		
			case SC_DB_BOOL:
				require(CONFIG_PATH_SC_HTML . 'form_checkbox_object.php');
				break;
		
			case SC_DB_ID:
				require(CONFIG_PATH_SC_HTML . 'form_id_object.php');
				break;
		
			case SC_DB_EMAIL_BODY:
			case SC_DB_EMAIL_HEADERS:
				require(CONFIG_PATH_SC_HTML . 'form_textarea_object.php');
				break;
		
			case SC_DB_PASSWORD:
				require(CONFIG_PATH_SC_HTML . 'form_password_object.php');
				break;
		
			case SC_DB_REPEAT_PASSWORD:
				require(CONFIG_PATH_SC_HTML . 'form_repeat_password_object.php');
				break;
		
			case SC_DB_UK_DATE:
				require(CONFIG_PATH_SC_HTML . 'form_uk_date_object.php');
				break;
		
			default:
				if ( $business->isDropdown($field) ){
					require(CONFIG_PATH_SC_HTML . 'form_select_object.php');
				}
				else{			
					require(CONFIG_PATH_SC_HTML . 'form_text_object.php');
				}
				break;
		}
	}
	/**
	* try to work out where the form focus should go
	* A: On an approprite record input field
	* B: On the first field of the filter form
	*/
	function javascriptSetFormFocus( $business = false )
	{
		if ( $business ){
			$javascript =
				'try{' . 
					'document.forms.' .
					$business->getTableName() .	'.elements[\'' .	$business->getFormFocusField() . '\'].focus();' .
				'}' .
				'catch(e){}';
		}
		else{
			$javascript =
				'try{' .
					'document.forms[0].elements[0].focus();' .
				'}' . 
				'catch(e){}';
		}
		return $javascript;
	}
} // end class SC_HTML
?>