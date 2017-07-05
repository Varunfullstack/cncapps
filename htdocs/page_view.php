<?php
/*
The order and position of the require statements mean that class definitions are included
only when we know we will need them.
*/
require_once($_SERVER['DOCUMENT_ROOT']	. '/.config.php');
require_once(CONFIG_PATH_SC_CLASSES 		. 'page_cache.php');
require_once(CONFIG_PATH_CNC_CLASSES 		. 'html.php');
$cache = &new SC_PageCache();
//$cache->start();
/*
Create the business object and handle the HTTP request
*/
require_once(CONFIG_PATH_SC_CLASSES			. 'html.php');
require_once(CONFIG_PATH_SC_CLASSES			. 'ajax.php');
require_once(CONFIG_PATH_SC_CLASSES			. 'string.php');

require_once(CONFIG_PATH_SC_CLASSES 		.	'page_view.php');
$business = & new SC_PageView();

$update_failed_message = SC_HTTP::dealWithRequest($business);

/*
Render the page
*/
?>
<HTML>
<?php
// Use the HEAD section cache for this page
if ( $cache_head = $cache->sectionStart('HEAD', 24) ) {		// 24 hour section cache
?>
	<HEAD>
		<TITLE><?php print SC_String::display($business->name) ?></TITLE>
		<link href="cnc.css" rel="stylesheet" type="text/css">
		<H1><?php print SC_String::display($business->name) ?></H1>
		<SCRIPT language="JavaScript" src=".javascript/ajax.js"></SCRIPT>
	</HEAD>
<?php
	$cache->sectionEnd($cache_head);
}
?>
	<BODY	<?php require(CONFIG_PATH_CNC_HTML . 'form_page_body.php');?> >
			<?php
			$form_name = 'page_view';
			require(CONFIG_PATH_CNC_HTML . 'form_errors.php');
			?>
			<form action="<?php print SC_HTTP::serverVar('PHP_SELF') ?>" method="post" enctype="application/x-www-form-urlencoded" name="page_view">
			<table border="0" cellspacing="1" cellpadding="1">
				<input name="page_view.page_view_id" type="hidden" value="<?php print SC_HTML::textDisplay($business->getValue('page_view.page_view_id')) ?>"> 
				<input name="page_view.modified_date" type="hidden" value="<?php print SC_HTML::textDisplay($business->getValue('page_view.modified_date')) ?>"> 
				<input type="hidden" name="javascript_is_enabled" value="0">
				<?php
				$field = 'page_view.page_view_id';
				require(CONFIG_PATH_CNC_HTML . 'table_row.php');				

				// the order of the array dictates the display order of fields
				$fields = array(
					'page_view.name',
					'page_view.script_name',
					'page_view.display_fields',
					'page_view.order_by',
					'page_view.filters'
				);
				
				foreach ($fields as $field) {
					require(CONFIG_PATH_CNC_HTML . 'table_form_row.php');				
				}
								
				?>
			</table>
			<?php
			require(CONFIG_PATH_CNC_HTML . 'table_form_submit.php');
			require(CONFIG_PATH_CNC_HTML . 'table_form_cancel.php');
			?>
<!--		<script language="JavaScript" type="text/javascript" src=".javascript/wz_tooltip.js"></script>-->
	</BODY>
</HTML>
<?php
$cache->end();
?>