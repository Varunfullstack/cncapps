<select	name="<?php print $field_name ?>"
	id="<?php print $field_name ?>"
	<?php print $business->getErrorText($field) ? 'class="errorBG"' : ''; ?>	
	value="<?php print $value ?>"
	maxlength="<?php print $business->getMaxLength($field) ?>"
	size="<?php print $business->getMaxLength($field) / 2.5  ?>" 
	onBlur="<?php if ($ajax_validation) print SC_Ajax::validation($business, $field);	?>"
	<?php if( $on_change ) print 'onChange="' .$on_change .'"' ?>
	<?php if( $on_key_down ) print 'onKeyDown="' .$on_key_down .'"' ?>
>
<?php
	if(!$business->isRequired($field)){
		if ( isset($none_description) ) {
		?>
			<option value=""><?php print $none_description ?></option>
		<?php
		}
		else{
		?>
			<option value=""><?php print SC_HTML_MSG_NONE_SELECTED ?></option>
		<?php
		}
	}
	if ($select_options = $business->getSelectOptions($field)){
		foreach ($select_options as $index => $row ) {
			?>
			<option <?php print SC_HTML::selected($row['value'], $business->getValue($field)) ?> value="<?php print $row['value'] ?>"><?php print $row['description'] ?></option>
			<?php
		}
	}
?>
</select>