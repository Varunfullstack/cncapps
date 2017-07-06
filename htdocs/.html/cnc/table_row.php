<tr> 
	<td class="b"><?php		print $business->getLabel($field) ?></td>
	<td>&nbsp;</td>
	<td class="tableValue"><?php	print SC_HTML::textDisplay($business->getValue($field)) ?></td>
	<td class="formError"><?php		print SC_HTML::textDisplay($business->getErrorText($field)) ?></td>
</tr>
