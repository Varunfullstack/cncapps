<?php
/**
*	page_cache.php
*
* Handles the caching of page contents at either a whole page level or at a section level
*
*		TODO: start(): STRIP OFF debug FLAGS FROM SC_HTTP::requestURIBase() TO
*		AVOID SCREWING THE CACHE FILE NAME!
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
define('SC_PAGECACHE_EXPIRE_HOURS', 24);	// default 24 hour page cache

require_once(CONFIG_PATH_SC_CLASSES 		.	'object.php');
require_once(CONFIG_PATH_SC_CLASSES 		.	'file.php');
require_once(CONFIG_PATH_SC_CLASSES 		.	'http.php');
class SC_PageCache extends SC_Object{
	var $start_time;
	var $cache_file_pointer;
	var $cache_file_temp;
	var $cache_file;
	var $generating_page_cache = false;
	var $using_page_cache = false;
	var $using_section_cache = false;
	
	function SC_PageCache(){
		$this->__construct();
	}
	function __construct(){
		parent::__construct();
		if (SC_HTTP::sessionVar('show_timings') ){
			$this->start_time = microtime();			// here in case we don't cache this page using start()
		}
		ob_start();
	}
	function reset(){
		SC_File::rmr(CONFIG_PATH_CACHE);
	}
	/*
	* Whole page cache
	*/
	function start($expiry=false){
		/*
		Avoid caching of obviously dynamic requests: i.e. with POST vars
		If you want to disable whole page caching set nocache=1 on the
		query.
		e.g. http://mpm.local/organisation.php?organisation_id=1&nocache=1
		*/
		if (
			SC_HTTP::requestVar('Cancel') ||
			SC_HTTP::requestVar('show_timings') ||
			SC_HTTP::requestVar('show_source') ||
			( !count($_POST) && !SC_HTTP::requestVar('nocache') )
		) {

			if (!$expiry){
				$expiry = SC_PAGECACHE_EXPIRE_HOURS;				// default cache file expiry time
			}
			/*
			TODO: STRIP OFF THESE FLAGS FROM SC_HTTP::requestURIBase() TO
			AVOID SCREWING THE CACHE FILE NAME!
				Cancel
				show_timings=n
				show_source=n
			*/
			$this->file =
										CONFIG_PATH_CACHE .
										SC_HTTP::sessionVar('language') .
										'.' .
										SC_HTTP::requestURIBase() .
										'.htm';


			// do we have a current cached version of this page already?
			if (
				$this->using_page_cache =
					file_exists($this->file) &&
					(time() - filemtime($this->file)) < 60 * 60 * $expiry
			) {
				$execute_time = microtime();
				require($this->file);		// include the cache file
				$page_time = microtime();
				/*
				Any diagnostics to show?
				*/
				if (SC_HTTP::sessionVar('show_timings') ){
					print $this->pageTiming($execute_time, $page_time);
				}
				if (SC_HTTP::sessionVar('show_queries') ){
					print $this->getQueries();
				}
				if (SC_HTTP::sessionVar('show_source') ){
					require(CONFIG_PATH_SC_CLASSES 		.	'show.php');
					SC_Show::phpSource($_SERVER['PHP_SELF']);
				}
				exit;										// quit the http request to send the page back
			}
			else{											// Get ready to cache page after generation
				$this->generating_page_cache	= true;
				$this->file_temp							=	$this->file . "." . getmypid();
				$this->pointer								= fopen($this->file_temp, "w");
			}
		} 	//	if ( !count($_POST) & !SC_HTTP::getVar('nocache') )
	}	
	function end(){
		$execute_time = microtime();
		if ( $this->generating_page_cache ) {
			$page = ob_get_contents();			// get the page from the output buffer
			ob_end_flush();									// output the buffer and clean it
			$page_time = microtime();

			fwrite($this->pointer, $page);	// write the page to the cache file
			fclose($this->pointer);
			/*
			@ supresses errors in case someone else got there 1st. 
			We don't care if it fails occaisionally
			*/
			@unlink($this->file);					
			@rename($this->file_temp, $this->file);
		}
		else{
			ob_flush();
			$page_time = microtime();
		}

		if (SC_HTTP::sessionVar('show_timings') ){
			print $this->pageTiming($execute_time, $page_time);
		}
		if (SC_HTTP::sessionVar('show_queries') ){
			print $this->getQueries();
		}
		if (SC_HTTP::sessionVar('show_source') ){
			require_once(CONFIG_PATH_SC_CLASSES 		.	'show.php');
			SC_Show::phpSource($_SERVER['PHP_SELF']);
		}
	}
	function sectionStart($section_name, $expiry=false){

		$ret = false;					// default return value = found in cache
		
		if (!$expiry){
			$expiry = SC_PAGECACHE_EXPIRE_HOURS;		// use default
		}
		$cache_file = CONFIG_PATH_CACHE . SC_HTTP::sessionVar('language') . '_' . basename(SC_HTTP::phpSelf(), '.php') . '_' . $section_name. '.htm';

		if (
			file_exists($cache_file) &&
			(time() - filemtime($cache_file)) < 60 * 60 * $expiry
		) {
			$this->using_section_cache = true;
			print file_get_contents($cache_file);					// found in cache
		}
		else{																						// Not in the cache
			$this->using_section_cache = false;
			$cache_file_temp = $cache_file . "." . getmypid();
			$cache_file_pointer = fopen($cache_file_temp, "w");		// our temp cache file
			ob_start();
			$ret = array(
				'pointer' 	=> $cache_file_pointer,
				'file_temp' => $cache_file_temp,
				'file' 			=> $cache_file
			);
		}
		return $ret;
	}	
	function sectionEnd(&$cache){
		if ( is_array($cache) ) {
			$file = ob_get_contents();							// isn't buffering great?!
			fwrite($cache['pointer'], $file);
			fclose($cache['pointer']);
	
			// discard any existing cache file 
			if(file_exists($cache['file'])) {
				@unlink($cache['file']);
			}
			// rename our temp cache file making it available to everyone else now
			@rename($cache['file_temp'], $cache['file']);
			ob_end_flush();													// output the buffer and clean
		}
	}
	/*
	* 
	*/
	function pageTiming($execute_time = 0, $page_time = 0) {

		$execute_duration =  $execute_time - $this->start_time;
		$page_duration =  $page_time - $execute_time;

		$ret = '<FONT class="info"> ';

		if ( $this->using_page_cache ) {
			$ret .= 'Cached page';
		}
		else{
			if ( $this->using_section_cache ) {
				$ret .= 'Page with cached section(s)';
			}
			else {
				$ret .= 'Non cached page';
			}
		}

		if (
			($execute_duration > 0.2 && $this->using_page_cache) ||
			($execute_duration > 0.5 && $this->using_section_cache) ||
			($execute_duration > 1 && !$this->using_section_cache)
		){
			$slow_to_execute = true;
		}
		else{
			$slow_to_execute = false;
		}

		$ret .= ' executed in ' . $execute_duration . ' seconds </FONT>';
		$ret .= '<BR><FONT class="info">Returned from server in </FONT>';
		if ($slow_to_execute){
			$ret .= '<FONT class="errorMessage">';
		}
		else{
			$ret .= '<FONT class="info">';
		}
		$ret .=  $page_duration . '</FONT> <FONT class="info">seconds</FONT>';
		if ($this->using_page_cache){
			$ret .=
				'<BR><FONT class="info">Cached at ' . date('Y-m-d h:m:i', filemtime($this->file)) . "</FONT>";
		}
		return $ret;
	}
	function getQueries(){
		if (SC_HTTP::sessionVar('show_queries')){
			@$ret = $_SESSION['queries'];
			unset($_SESSION['queries']);
		}
		else{
			$ret = '';
		}
		return $ret;
	}
} // end class SC_PageCache
?>