<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/.config.php' );
require_once( CONFIG_PATH_CNC_CLASSES .	'order.php' );
/*
require_once(CONFIG_PATH_SC_CLASSES .		'authenticate.php');
$authenticate = & new SC_Authenticate(new MPM_Organisation());
$authenticate->authenticate();
*/
$business = & new CNC_Order();
if ( !SC_HTTP::requestVar('is_ajax_request') ){
?>
	<HTML>
		<HEAD>
			<SCRIPT language="JavaScript" type="text/javascript" src=".javascript/ajax.js"></SCRIPT>
		</HEAD>
		<BODY>
<?php
}
		require( CONFIG_PATH_SC_HTML .	'page_list.php' );
if ( !SC_HTTP::requestVar('is_ajax_request') ){
?>
	</BODY>
</HTML>
<?php
}
?>
