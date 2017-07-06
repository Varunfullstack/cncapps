<?php
$field_length = $business->getMaxLength($field);
?>
<input
	type="text"
	<?php print $business->getErrorText($field) ? 'class="errorBG"' : ''; ?>	
	name="<?php print $field_name ?>"
	id="<?php print $field_name ?>"
	value="<?php print SC_HTML::textDisplay( $value ) ?>"
	maxlength="<?php print $field_length ?>"
	size="<?php print $field_length / 2.5 ?>" 
	onBlur="<?php if ($ajax_validation) print SC_Ajax::validation($business, $field);	?>"
	<?php if( $on_change ) print 'onChange="' .$on_change .'"' ?>
	<?php if( $on_key_down ) print 'onKeyDown="' .$on_key_down .'"' ?>
>
<!-- Removed cause javascript doesn't like field names with dot in them
<a href="javascript:;" onClick="popUpCalendar(this, '<?php //print $field ?>', 'dd/mm/yyyy')"><img src="images/calendar.gif" width="24" height="22" hspace="0" vspace="0" border="0" align="absmiddle"></a>
-->