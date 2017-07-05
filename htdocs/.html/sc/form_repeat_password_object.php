<input
	type="password"
	<?php print $error_text ? 'class="errorBG"' : ''; ?>	
	name="<?php print $field_name ?>"
	id="<?php print $field_name ?>"
	value="<?php print SC_HTML::textDisplay( $value ) ?>"
	maxlength="<?php print $business->getMaxLength($field) ?>"
	size="<?php print $business->getMaxLength($field) / 2.5 ?>" 
	<?php if( $on_change ) print 'onChange="' .$on_change .'"' ?>
	<?php if( $on_key_down ) print 'onKeyDown="' .$on_key_down .'"' ?>
>