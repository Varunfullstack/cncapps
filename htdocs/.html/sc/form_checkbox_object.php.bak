<input
	class="checkbox"
	type="checkbox"
	<?php print $business->getErrorText($field) ? 'class="errorBG"' : ''; ?>	
	name="<?php print $field_name ?>"
	id="<?php print $field_name ?>"
	<?php print SC_HTML::checkedValue( $value ) ?>
	value="1"
	onBlur="<?php if ($ajax_validation) print SC_Ajax::validation($business, $field);	?>"
	<?php if( $on_change ) print 'onChange="' .$on_change .'"' ?>
	<?php if( $on_key_down ) print 'onKeyDown="' .$on_key_down .'"' ?>
>