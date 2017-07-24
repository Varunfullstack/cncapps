<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_MPM_CLASSES .	'organisation.php');
require_once(CONFIG_PATH_SC_CLASSES .	'page_view.php');
require_once(CONFIG_PATH_SC_CLASSES .		'authenticate.php');

$authenticate = new SC_Authenticate(new MPM_Organisation());
$authenticate->authenticate();

require_once(CONFIG_PATH_SC_CLASSES 		. 'page_cache.php');
$cache = new SC_PageCache();

require_once(CONFIG_PATH_SC_CLASSES			. 'http.php');
require_once(CONFIG_PATH_SC_CLASSES			. 'html.php');

$business = new MPM_Organisation();
SC_HTTP::convertRequestVars($business);

if ( SC_HTTP::isAjaxTableUpdate() ){
	$ajax = new SC_Ajax;
	$ajax->request( $_SERVER['REQUEST_URI'] );
}

$ajax_validation = false;

$title = $business->getDisplayName();

$order_by_tag = SC_HTTP::getScriptName() . '_order_by';

if ( SC_HTTP::requestVar('order_by') ){
	$_SESSION[$order_by_tag] = SC_HTTP::requestVar('order_by');
}
/*
to be implemented!!!
$field_sequence_tag = $business->getTableName() . '_field_sequence';

if ( SC_HTTP::requestVar('field_sequence') ){
	$_SESSION[$field_sequence_tag] = SC_HTTP::requestVar('field_sequence');
}
*/

$display_fields_tag = SC_HTTP::getScriptName() .  '_display_fields';
if ( SC_HTTP::requestVar('display_fields') ){
	$_SESSION[$display_fields_tag] = array();
	foreach ( SC_HTTP::requestVar('display_fields') as $field => $value ) {
		$_SESSION[$display_fields_tag][] = $field;
	}
}

// default
if (SC_HTTP::requestVar('display_all_fields')){
	$_SESSION[$display_fields_tag] = array();
	foreach ($business->fields as $field => $attributes){
		$_SESSION[$display_fields_tag][] = $field;
	}
}
else{
	if (
		!SC_HTTP::sessionVar($display_fields_tag) ||
		( isset($_REQUEST['display_fields']) &&	$_REQUEST['display_fields'] == '' )
	) {
		$_SESSION[$display_fields_tag] = array();
		$_SESSION[$display_fields_tag] =
			array(
				'organisation.name',
				'organisation.address_1',
				'organisation.town',
				'organisation.postcode',
				'organisation.correspondance_email'
			);
	}
}

$filter_tag = SC_HTTP::getScriptName() . '_filter';
if ( isset($_REQUEST['filter'] ) ){
	$_SESSION[ $filter_tag ] = SC_HTTP::requestVar('filter');
}

/*
default order name descending
*/
if ( !SC_HTTP::sessionVar('organisation_list_order_by') ){
	$_SESSION['organisation_list_order_by'] = 'organisation.display_sequence';
}
$results =	$business->getAllRows(
	0,
	SC_HTTP::sessionVar($order_by_tag),
	SC_HTTP::sessionVar($filter_tag)
);

$additional_width = 20;					// allow extra column space of non-form elements

if ( !SC_HTTP::isAjaxTableUpdate() ){
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
		<TITLE><?php print SC_String::display($title) ?></TITLE>
		<link href="mpm.css" rel="stylesheet" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<H1><?php print SC_String::display($title) ?></H1>
		<SCRIPT language="JavaScript" src=".javascript/ajax.js"></SCRIPT>
	</head>
	<body onLoad="<?php print SC_Ajax::setJavascriptIsEnabled() . SC_Ajax::tellServerAjaxIsEnabled() ?>">
	<?php
	$page_view = new SC_PageView;
	$filters =
		array(
			'page_view.script_name' => 'organisation_list'
		);
	$page_view_rows =
		$page_view->getAllRows(
			false,
			false,
			$filters
		);
	?>
	<table cellspacing="1" cellpadding="0" width="30%">
	<?php
	while ( $row = $page_view_rows->fetchAssoc() ){
	?>
		<TR>
			<TD>
				<a href="<?php print SC_HTTP::phpSelf() ?>"><?php print $row['page_view.name'] ?></a>
			</TD>
		</TR>
	<?php
	}
	?>
	</TABLE>
	<table cellspacing="1" cellpadding="1" width="100%">
		<?php
		// do the filters
		?>
			<form
				name="apply_filters"
				action="<?php print SC_HTTP::phpSelf() ?>"
				method="get"
				onSubmit="<?php print SC_Ajax::filter($business)?>"
			>
			<TR>
			<?php
			foreach ( $_SESSION[$display_fields_tag] as $field) {
			?>
				<TH class="filter" align="centre" width="<?php print $business->getPercentageWidth( $field, $_SESSION[$display_fields_tag], $additional_width) ?>%">
					<?php
					$key_down = SC_Ajax::filter($business, true);
					$style 		= 'width: 100%';						// to make the input width = TD width
					$value 		= SC_HTTP::sessionArrayVar($filter_tag, $field);

					SC_HTML::formObject(
						$business,
						$field,
						$value,
						'filter[' . $field . ']',
						false,
						false,
						$key_down,
						$style
						);
					?>
				</TH>
			<?php
			} // end foreach
			?>
			<TH align="center" class="filter" colspan="3" width=<?php print $business->getPercentageWidth( $additional_width, $_SESSION[$display_fields_tag], $additional_width) ?>%>
				<input type="hidden" name="javascript_is_enabled" value="0">
				<input
					type="submit"
					name="filter_button"
					value="Filter"
				>
			</form>

			<form
				name="clear_filters"
				action="<?php print SC_HTTP::phpSelf() ?>"
				method="get"
				onSubmit="<?php print SC_Ajax::clearFilters('document.forms[0]')?>"
			>
				<input type="hidden" name="filter" value="">    <!-- This clears the filter array -->
				<input type="hidden" name="javascript_is_enabled" value="0">
				<input
					type="submit"
					name="Clear"
					value="<?php print SC_String::display('Reset') ?>"
				>
			</form>
			</TH>
		</TR>
	</table>
	<div id="results_table">
<?php
} //if !SC_HTTP::isAjaxTableUpdate()
else{
	ob_start();
}
	// do the headings
?>
	<form
			name="display_fields_form_heading"
			action="<?php print SC_HTTP::phpSelf() ?>"
			method="get"
	>
	<table cellspacing="1" cellpadding="1" width="100%">
		<TR>
			<?php
			foreach ($_SESSION[$display_fields_tag] as $field) {

				$order_by = $field;

				// if are we already sorting by this and it isn't descending order use DESC
				if (
					$field == str_replace(' DESC', '' , SC_HTTP::sessionVar( $order_by_tag ) ) &&
					!strpos( SC_HTTP::sessionVar( $order_by_tag ), 'DESC' )
				){
					$order_by .= ' DESC';
				}
				?>
				<TD
					width="<?php print $business->getPercentageWidth( $field, $_SESSION[$display_fields_tag], $additional_width) ?>%"
					align="left"
					valign="top"
					class="column_heading"
				>
					<table cellspacing="1" cellpadding="1" width="100%">
						<TR>
							<TD nowrap>
								<?php
								if ( SC_Ajax::ajaxIsEnabled() ){
									?>
									<A
										href="javascript:"
										onClick="loadXMLDoc('<?php print SC_HTTP::phpSelf()?>?ajax_table_update=1&order_by=<?php print $order_by ?>')"
									>
									<?php
								}
								else{
									?>
									<A
										href = "<?php print SC_HTTP::phpSelf()?>?order_by=<?php print $order_by ?>"
									>
									<?php
								}
								/*
								if we are ordering by this field then display an icon
								*/
								if ($field == str_replace(' DESC', '' , SC_HTTP::sessionVar( $order_by_tag ) ) ) {
									if ( strpos( $order_by , 'DESC' ) ){
									?>
										<!-- descending order image -->
										<img src="images/down.gif" width="10" height="10" border="0">
									<?php
									}
									else{
									?>
										<!-- ascending order image -->
										<img src="images/up.gif" width="10" height="10" border="0">
									<?php
									}
								}
								?>
								<?php
								print SC_String::display( $business->getLabel( $field ), SC_STRING_FMT_UC_WORDS );
								?>
								</A>
							</TD>
							<TD align="right">
								<?php
								if ( SC_Ajax::ajaxIsEnabled() ){
									?>
									<input
										type="checkbox"
										class="checkbox"
										id="display_fields[<?php print $field ?>]"
										onCheck="form.submit()"
										onUncheck="form.submit()"
										onChange="form.submit()"
										onClick="form.submit()"
										name="display_fields[<?php print $field ?>]"
										<?php print in_array($field, $_SESSION[$display_fields_tag]) ? SC_HTML_CHECKED : '' ?>
										value="1"
									>
									<?php
								}
								else{
									?>
									&nbsp;
									<?php
								}
								?>
							</TD>
						</TR>
					</TABLE>
				<?php
			} // end foreach
			?>
				</TD>
				<TD class="column_heading" width="<?php print $business->getPercentageWidth( $additional_width, $_SESSION[$display_fields_tag], $additional_width) ?>%">&nbsp;</TD>
			</TR>
<!-- The rows -->
		<?PHP
		while ( $row = $results->fetchAssoc() ){
			?>
			<tr <?php if ( !$row['organisation.active'] ) print 'class="inactive"' ?> onMouseOver="this.bgColor='#FFFFCC';" onMouseOut="this.bgColor='';">
			<?php

			foreach ( $row as $field => $value ){
				if ( !in_array($field, SC_HTTP::sessionVar($display_fields_tag) ) ){
					continue;
				}
			?>
				<td nowrap align="left" width="<?php print $business->getPercentageWidth( $field, $_SESSION[$display_fields_tag], $additional_width) ?>%">
					<?php
					$value = ($field == 'organisation.sent_time' & $value == '') ? 'UNSENT' : $value;
					if ($field == 'organisation.name') {
					?>
						<a href="organisation.php?organisation.organisation_id=<?php print $row['organisation.organisation_id'] . '&cancel_page='. SC_HTTP::phpSelf() . '&save_page=' . SC_HTTP::phpSelf() ?>"><?php print SC_String::display( $value )?></a>
					<?php
					}
					else{
						print SC_String::display( $value );
					}
					?>
				</td>
			<?php
			} // end foreach

			?>
				<td align="center" width="<?php print $business->getPercentageWidth( $additional_width, $_SESSION[$display_fields_tag], $additional_width) ?>%">
					<a href="organisation.php?organisation.organisation_id=<?php print $row['organisation.organisation_id'] . '&cancel_page='. SC_HTTP::phpSelf() . '&save_page=' . SC_HTTP::phpSelf() ?>">Edit</a>
				</td>
			</TR>
		<?php
		} // while ( $row = $result_set->fetchAssoc()
		?>
	</table>
		<input type="hidden" name="javascript_is_enabled" value="0">
	</form>
	<P>
	<!-- create link -->
	<a href="organisation.php?cancel_page=<?php print SC_HTTP::phpSelf() . '&save_page=' . SC_HTTP::phpSelf() ?>">Create new organisation</a>
	</P>
	<form
		name="display_fields_form"
		action="<?php print SC_HTTP::phpSelf() ?>"
		method="get"
	>
	<!-- 		onSubmit="<?php //print SC_Ajax::fieldSelect($business)?>" -->
	<table cellspacing="1" cellpadding="0" width="30%">
			<TR>
				<TH colspan="1" class="column_heading">
					Field
				</TH>
				<TH colspan="1" class="column_heading">
					Show
				</TH>
				<TH colspan="1" class="column_heading">
					Order
				</TH>
				<TH colspan="1" class="column_heading">
					Filter
				</TH>
			</TR>
			<?php
			foreach ($business->fields as $field => $attributes){
			?>
			<TR onMouseOver="this.bgColor='#FFFFCC';" onMouseOut="this.bgColor='';">
				<TD class="formLabel" width = "50%">
					<?php
					print SC_String::display( $business->getLabel( $field ), SC_STRING_FMT_UC_WORDS );
					?>
				</TD>

			<TD width = "15%">
				<div align="center">
				<input
					type="checkbox"
					class="checkbox"
					id="display_fields[<?php print $field ?>]"
					onCheck="form.submit()"
					onUncheck="form.submit()"
					onChange="form.submit()"
					onClick="form.submit()"
					name="display_fields[<?php print $field ?>]"
					<?php print in_array($field, $_SESSION[$display_fields_tag]) ? SC_HTML_CHECKED : '' ?>
					value="1"
				>
				<!--
						onCheck="<?php //print SC_Ajax::fieldSelect($business, true) ?>"
						onUncheck="<?php //print SC_Ajax::fieldSelect($business, true) ?>"
						onChange="<?php //print SC_Ajax::fieldSelect($business, true) ?>"
						onClick="<?php //print SC_Ajax::fieldSelect($business, true) ?>"
				-->
				</div>
			</TD>
			<TD width = "5%">
				<div align="center">
				<?php
				if ( $field == str_replace(' DESC', '' , SC_HTTP::sessionVar( $order_by_tag ) ) ) {
					$order_by = SC_HTTP::sessionVar( $order_by_tag );
					if ( strpos( $order_by , 'DESC' ) ){
						?>
						<!-- ascending order image -->
						<img src="images/up.gif" width="10" height="10" border="0">
						<?php
					}
					else{
						?>
						<!-- descending order image -->
						<img src="images/down.gif" width="10" height="10" border="0">
						<?php
					}
				}
				?>
				</div>
			</TD>
			<TD width = "30%">
				<div align="center">
				<?php
					print SC_HTTP::sessionArrayVar($filter_tag, $field);
				?>
				</div>
			</TD>
		</TR>
			<?php
			}
			?>
			<TR>
				<TD colspan="4">
				</TD>
			</TR>
	</TABLE>
	<input type="hidden" name="javascript_is_enabled" value="0">
	<input
		type="submit"
		name="select_button"
		value="Select"
	>
</form>
<form
	name="reset_fields"
	action="<?php print SC_HTTP::phpSelf() ?>"
	method="get"
>
	<input type="hidden" name="display_fields" value="">    <!-- This clears the display_fields array -->
	<input type="hidden" name="javascript_is_enabled" value="0">
	<input
		type="submit"
		name="Reset"
		value="<?php print SC_String::display('Reset') ?>"
	>
</form>
<form
	name="display_all_fields"
	action="<?php print SC_HTTP::phpSelf() ?>"
	method="get"
>
	<input type="hidden" name="display_all_fields" value="1">    <!-- This clears the display_fields array -->
	<input type="hidden" name="javascript_is_enabled" value="0">
	<input
		type="submit"
		name="display_all_button"
		value="<?php print SC_String::display('Show All') ?>"
	>
</form>
	</div>
<?php
if ( !SC_HTTP::isAjaxTableUpdate() ){
?>
	</body>
	</html>
	<?php
	$cache->end();
	?>
<?php
} //if (!SC_HTTP::isAjaxTableUpdate())
else{
//	$_SESSION['ajax_is_enabled'] = true;
	$contents = str_replace( '\'', '\\\'', ob_get_contents() );
	$contents = str_replace( CR, '', $contents );
	ob_end_clean();
	$javascript =
		"var im = document.getElementById('results_table');". CR . TAB .
		"im.innerHTML = '"	.	$contents . "';";
	print $ajax->response($javascript);
}
?>
