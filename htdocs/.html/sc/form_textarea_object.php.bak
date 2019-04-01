<?php
$field_length = $business->getMaxLength($field);
?>
<textarea	<?php print $business->getErrorText($field) ? 'class="errorBG"' : ''; ?>	
	name="<?php print $field_name ?>"
	id="<?php print $field_name ?>"
	size="<?php print $field_length / 2 ?>" 
	maxlength="<?php print $field_length ?>"
	cols="80"
	rows="10"
	<?php if( $on_change ) print 'onChange="' .$on_change .'"' ?>
	<?php if( $on_key_down ) print 'onKeyDown="' .$on_key_down .'"' ?>
	<?php SC_HTML::readOnly($business->canEdit($field)); ?>
>
<?php print SC_HTML::textArea( $value ) ?>
</textarea>
