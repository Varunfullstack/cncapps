<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/.config.php' );
require_once( CONFIG_PATH_SC_CLASSES .	'page_view.php' );
/*
require_once(CONFIG_PATH_SC_CLASSES .		'authenticate.php');
$authenticate = & new SC_Authenticate(new MPM_Organisation());
$authenticate->authenticate();
*/
$business = & new SC_PageView();
require( CONFIG_PATH_SC_HTML .	'page_list.php' );
?>