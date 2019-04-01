<?php
$field_length = 1;
?>
<input
	type="text"
	<?php print $business->getErrorText($field) ? 'class="errorBG"' : ''; ?>	
	name="<?php print $field_name ?>"
	id="<?php print $field_name ?>"
	value="<?php print SC_HTML::textDisplay( $value ) ?>"
	maxlength="<?php print $business->getMaxLength($field) ?>"
	size="<?php print $field_length ?>" 
	onBlur="<?php if ($ajax_validation) print SC_Ajax::validation($business, $field);	?>"
	<?php if( $on_change ) print 'onChange="' .$on_change .'"' ?>
	<?php if( $on_key_down ) print 'onKeyDown="' .$on_key_down .'"' ?>
>