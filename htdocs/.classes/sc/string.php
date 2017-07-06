<?php
/**
* String classes
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
/*
format constants.
Binary so that they can be combined bitwise into one variable using &
e.g.
$format = SC_STRING_FMT_LOWERCASE & SC_STRING_FMT_FIRST_PARAGRAPH;
*/
define('SC_STRING_FMT_LOWERCASE',											1);										
define('SC_STRING_FMT_UPPERCASE',											2);
define('SC_STRING_FMT_UC_WORDS',											4);
define('SC_STRING_FMT_UPPERCASE_FIRST_LETTER',				8);
define('SC_STRING_FMT_FIRST_PARAGRAPH',								16);
define('SC_UNUSED_1',																	32);
define('SC_UNUSED_2',																	64);
define('SC_UNUSED_3',																	128);

require_once(CONFIG_PATH_SC_CLASSES . 'object.php');
class SC_String extends SC_Object{
	/**
	* strReplaceFirst
	*
	* Replace 1st occurence of a given string $search with $replace in $subject
	* @param string $search string to search for
	* @param string $replace string to replace with
	* @param string $subject Subject
	* @return string New string
	*/
	function strReplaceFirst ( $search, $replace, $subject ) {
		return substr_replace (
			$subject,
			$replace,
			strpos($subject, $search),
			strlen($search)
		);
	}
	/**
	* strReplace
	*
	* Replace every occurence of a given string $search with $replace in $subject
	* @param string $search string to search for
	* @param string $replace string to replace with
	* @param string $subject Subject
	* @return string New string
	*/
	function strReplace( $search, $replace, $subject ) {
		return str_replace(
			$search,
			$replace,
			$subject
		);
	}
	/**
	* convert a string to hex code - can be used to blocks email spiders
	*/
  function strToHex($string, $prefix = "%") {
		$string_length = strlen($string);
		for($i = 0; $i < $string_length; $i++) {
			$return .= $prefix . dechex(ord($string[$i]));
		}
		return $return;
	}
	/**
	* create a clean string that can be used as variable/file/folder name
	*/
	function cleanForName($string){
		$string = trim(strtolower($string));
		$string = SC_String::strReplace(",","-",$string);
		$string = SC_String::strReplace(".","-",$string);
		$string = SC_String::strReplace(" ","-",$string);
		$string = SC_String::strReplace("+","and",$string);
		$string = SC_String::strReplace("'","-",$string);
		$string = SC_String::strReplace("\"","-",$string);
		$string = SC_String::strReplace("$","",$string);
		$string = SC_String::strReplace("\\","",$string);
		$string = SC_String::strReplace("/","-",$string);
		$string = SC_String::strReplace("(","",$string);
		$string = SC_String::strReplace(")","",$string);
		$string = SC_String::strReplace("&","and",$string);
		return trim(strtolower($string));
	}
	function reverseStrrchr($haystack, $needle)
	{
		return strrpos($haystack, $needle) ? substr($haystack, 0, strrpos($haystack, $needle) ) : false;
	}
	function strrchr($haystack, $needle)
	{
		return strrpos($haystack, $needle) ? substr($haystack, strrpos($haystack, $needle) + 1, strlen($haystack) ) : false;
	}
	/**
	* Check email is valid
	*/
	function isEmail($address, $min=false, $max=false) {
		 //Debug or not?
		 $debug = 0;
		 
		 $address = trim($address);
	
			// do the basic Reg Exp Matching for simple validation
			if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $address)) { 
				$return = false;
			} else { 
				$return = true;
			} 
	 		return ( $return & strlen($address) <= $max &  strlen($address) >= $min);

	}
	function isString($value, $min=false, $max=false) {
		return is_string($value);
	}
	function isAddress($value, $min=false, $max=false) {
		trim($value);
		return ( is_string($value) & strlen($value) <= $max &  strlen($value) >= $min);
	}
	/*
	* isInteger
	*
	* Is this a valid integer
	* @access public
	*/
	function isInteger($value, $min=false, $max=false) {
		return ( is_numeric($value) && $value <= $max &  $value >= $min);
	}
	/*
	* isPhone
	*
	* Is this a valid phone no
	* @access public
	*/
	function isPhone($value, $min=false, $max=false) {
		trim($value);
		return ( strlen($value) <= $max &  strlen($value) >= $min);
	}
	/*
	* isPassword
	*
	* Is this a valid password
	* @access public
	*/
	function isPassword($value, $min=false, $max=false) {
		trim($value);
		return ( is_string($value) & strlen($value) <= $max &  strlen($value) >= $min);
	}
	/*
	* isRepeatPassword
	*
	* Is this a valid repeat password?
	* @access public
	*/
	function isRepeatPassword($value, $min=false, $max=false) {
		trim($value);
		$repeat =		SC_String::reverseStrrchr($value, ':');
		$password =	SC_String::strrchr($value, ':');
		return (
			SC_String::isPassword($repeat, $min, $max)  &
			($repeat == $password)
		);
	}
	/*
	* isBoolean
	*
	* A strange test, I know but PHP does some strange things with datatypes
	*
	* Is this a valid boolean
	* @access public
	*/
	function isBoolean($value=0, $min=false, $max=false) {
		$value = ( $value == null || $value == '' ) ? 0 : $value;
		return is_numeric($value);
	}
	function isDecimal($value=0, $min=false, $max=false) {
		return ( is_numeric($value) & $value <= $max &  $value >= $min);
	}
	function isPostcode($value, $min=false, $max=false){
		trim($value);
		return ( is_string($value) & strlen($value) <= $max &  strlen($value) >= $min);
	}
	function isTown($value, $min=false, $max=false){
		trim($value);
		return ( is_string($value) & strlen($value) <= $max &  strlen($value) >= $min);
	}
	function isName($value, $min=false, $max=false){
		trim($value);
		return ( is_string($value) & strlen($value) <= $max &  strlen($value) >= $min);
	}
	function strToUpper($value){
		trim($value);
		return strtoupper($value);
	}
	/**
	* format to 2 dps and trim leading and trailing zeros
	*
	*/
	function formatDecimal2($value)
	{
		return number_format( $value, 2, '.', ',' );
	}
	function strToLower($value){
		trim($value);
		return strtolower($value);
	}
	function ucWords($value){
		trim($value);
		return ucwords($value);
	}
	/*
	* display
	*
	* Print translated(if translation exists in $_SESSION['strings']) , formatted string  
	* @access public
	* @param	string	$original
	* @param	string	optional formatting to be applied (boolean: may combine options)
	* @return	string	translated formatted string
	*/
	function display($original, $formatting=false) {
		trim($original);
		if ( $original == '' ){
			return '';
		}
		/*
		If language translation is enabled then check the strings session var for a key/value
		*/
		if ( CONFIG_LANGUAGE_TRANSLATION_IS_ON ) {
			$new_string = SC_String::translate( $original ); // e.g. 'en, fr'
		}
		else{																								// no translation
			$new_string = $original;
		}
		/*
		Apply formating
		*/
		if ( $formatting & SC_STRING_FMT_LOWERCASE ){							// whole string to lower case
			$new_string = strtolower($new_string);
		}
		else{
			if ( $formatting & SC_STRING_FMT_UPPERCASE ){							// whole string to lower case
				$new_string = strtoupper($new_string);
			}
			else{
				if ( $formatting & SC_STRING_FMT_UC_WORDS ){
					$new_string = ucwords($new_string);
				}
				else{
					if ( $formatting & SC_STRING_FMT_UPPERCASE_FIRST_LETTER ){
						// TODO -----------------------------------
					}
				}
			}
		}	
		if ($formatting & SC_STRING_FMT_FIRST_PARAGRAPH){
			// TODO -----------------------------------
		}
		return $new_string;
	}	// end display
	function translate($string){
		if (array_key_exists(strtolower($string), $_SESSION['translation_array'])) {
			return $_SESSION['translation_array'][strtolower($string)];
		}
		else{
			return $string;
		}
	}
	function createTranslationArray() {
		require('language_strings.php');
		unset($_SESSION['translation_array']);
		foreach ($GLOBALS['_LANGUAGE_STRINGS'] as $string => $language_array) {
				if (array_key_exists($_SESSION['language'], $language_array)) {
				$_SESSION['translation_array'][$string] = $language_array[$_SESSION['language']];
			}
		}
	}
}	// end of class SC_String
?>