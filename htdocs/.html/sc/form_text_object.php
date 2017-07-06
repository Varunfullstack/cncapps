<?php
// should we interpret maxLength as string length or as value length when
// deciding the display field size?
if (
	$business->isNumeric($field)
){
	$field_length = strlen($business->getMaxLength($field));
}
else{
	$field_length = $business->getMaxLength($field);
}
if ( !isset($max_length) || !$max_length ){
	$max_length = $field_length;
}

?>
<input
	type="text"
	<?php print $business->getErrorText($field) ? 'class="errorBG"' : ''; ?>	
	name="<?php print $field_name ?>"
	id="<?php print $field_name ?>"
	value="<?php print SC_HTML::textDisplay( $value ) ?>"
	maxlength="<?php print $max_length ?>"
	size="<?php print $field_length / 2.5 ?>" 
	onBlur="<?php if ( $ajax_validation ) print SC_Ajax::validation($business, $field);	?>"
	<?php if( $on_change ) print 'onChange="' .$on_change .'"' ?>
	<?php if( $on_key_down ) print 'onKeyDown="' .$on_key_down .'"' ?>
	<?php if( $style ) print 'style="' .$style .'"' ?>
>
