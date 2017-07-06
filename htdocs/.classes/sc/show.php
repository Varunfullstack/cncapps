<?php
/*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
require_once(CONFIG_PATH_SC_CLASSES . 'object.php');
class SC_Show extends SC_Object{
	function phpSource($url){
		// No file param specified
		if (!isset($url) || (isset($url) && !is_string($url))) {
			 echo "<h2>SC_Show: No page URL specified for</h2>";
			 exit;
		}
		$page_name = $_SERVER['DOCUMENT_ROOT'] . $url;
		// Provide some feedback based on the file found
		if (!$page_name || @is_dir($page_name)) {
			 echo "<p>SC_Show: Invalid file or folder specified</p>\n";
		} elseif (file_exists($page_name)) {
			print highlight_file($page_name, true);
 /*
 If we decide to allow online editing via web pages, this code may be useful:
<?php
			$line_count = count(file($page_name));
?>
			<form name="edit" action="update_source.php" method="post">
					<input type="hidden" name="page_name" value=<?php print $page_name ?>>
					<?php
						include("FCKeditor/fckeditor.php") ;
						$oFCKeditor = new FCKeditor('code') ;
						$oFCKeditor->BasePath = '/FCKeditor/';
						$oFCKeditor->Width  = '100%' ;
						$oFCKeditor->Height = $line_count * 16;
						$oFCKeditor->Value = highlight_file($page_name, true);
//						$oFCKeditor->Value = str_replace("\r", "\r\n", file_get_contents($page_name));
						$oFCKeditor->Create() ;
					?>
				<p> 
<!--					<input type="submit" name="Save" value="Save">  NOT WORKING YET!!
							NOTE: when we do want to update php files we will have to undo the highlight file
							formatting again BEFORE writing to the file!
-->
				</p>
			</form>
		<?php
*/
		} else {
			 echo "<p>SC_Show: This file does not exist.</p>\n";
		}
	}
}	// end of class SC_Show
?>