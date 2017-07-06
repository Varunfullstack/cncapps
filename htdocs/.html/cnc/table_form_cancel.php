<?php
if ( !$cancel_page = SC_HTTP::sessionVar( $business->getTableName() . '_cancel_page' ) ){
	$cancel_page = SC_HTTP::phpSelf() . '?' . $business->getPKName() . '=' . $business->getPKValue();
}

if ( SC_HTTP::sessionVar('javascript_is_enabled') ){
?>
	<input
		type="button"
		name="Cancel"
		value="<?php print SC_String::display('Cancel') ?>"
		onClick="document.location='<?php print  $cancel_page ?>'"
	>
	</form>
<?php
}
else{
?>
	</form>
	<form action="<?php print $cancel_page ?>" method="post">
		<input type="submit" name="Cancel" value="<?php print SC_String::display('Cancel') ?>"/>
	</form>
<?php
}
?>
