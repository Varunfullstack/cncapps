<P><strong><?php print $business->getLabel($field) ?></strong></P>
<P><?php print $business->getHelpText($field) ?></P> 
<?php
if ($business->isRequired($field)){
?>
	<p>Required field</p>
<?php
}
?>