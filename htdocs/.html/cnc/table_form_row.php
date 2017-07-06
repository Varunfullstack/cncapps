<?php
/**
*	table_form_row.php
*
* NOTE: We use caches for the parts of the page that are static
*
* This renders a form row by determining what input object is required using the business::type
*/
?>
<tr>
	<td class="b" valign="top">
		<?php	print SC_String::display($business->getLabel($field), SC_STRING_FMT_UC_WORDS) ?>
	</td><td class="formError">
		<?php	print $business->requiredFlag($field) ?>
	</td>
	<?php
	$id_td 	= $field . '_td';								// HTML id of the page element
	?>
	<td id="<?php print $id_td ?>"  >
	<?php
	if (!isset($ajax_validation)){
		$ajax_validation = true;
	}
	SC_HTML::formObject($business, $field, $business->getValue($field), false, $ajax_validation);
	?>
	</td>
	<td
		<?php
		if ($business->hasHelp($field) && $business->isDisplayingHelp() ){
			print SC_HTML::helpMouseover($business, $field);
		?>
		<img src="images/help_icon.gif">
		<?php
		}
		?>
	>
	</td>
	<td class="formError" id="<?php print $field . '_error' ?>">
		<?php	print $business->getErrorText($field) ?>
	</td>
</tr>