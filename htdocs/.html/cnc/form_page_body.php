<?php
print
	'onLoad="document.forms[0].elements[\'' .	$business->getFormFocusField() . '\'].focus();' .
	'document.forms[0].javascript_is_enabled.value=\'1\'"';
?>