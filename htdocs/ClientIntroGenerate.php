<?php
/**
* Client introduction letter
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once("config.inc.php");
GLOBAL $cfg;
page_open(
	array(
		'sess' => PHPLIB_CLASSNAME_SESSION,
		'auth' => PHPLIB_CLASSNAME_AUTH,
		'',
		''
	)
);
require_once($cfg['path_dbe'] . '/DBEUser.inc.php');
require_once($cfg['path_gc'] . '/Controller.inc.php');
$dbeUser=new DBEUser($this);
$dbeUser->setValue('userID', $_REQUEST['userID']);
$dbeUser->getRow();
?>
<html>
<head>
<title>Introduction Letter</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="form.css" rel="stylesheet" type="text/css">
</head>
<body style=font-size:"10pt">
<!-- 9 lines at top for headed paper -->
<BR/>
<BR/>
<BR/>
<BR/>
<BR/>
<BR/>
<BR/>
<BR/>
<P>
<P align="justify"><?php echo Controller::formatForHTML($_REQUEST['letterText']); ?></P>
Yours sincerely,<BR/>
For and on behalf of<BR/>
COMPUTER & NETWORK CONSULTANTS LTD
<BR/>
<?php
if ( $dbeUser->getValue('signatureFilename') ){
	?>
	<img height="120px" src="/images/<?php echo $dbeUser->getValue('signatureFilename') ?>">
	<?php
}
else{
	?>
	<BR/>
	<BR/>
	<BR/>
	<BR/>
	<?php
}
?>
<BR/>
<?php echo Controller::htmlDisplayText($dbeUser->getValue('firstName').' '.$dbeUser->getValue('lastName')); ?>
<BR/>
<STRONG>
<?php echo Controller::htmlDisplayText($dbeUser->getValue('jobTitle')); ?>
</STRONG>
</P>
</body>
</html>
<script type="text/javascript" language="javascript1.2">
<!--
// Do print the page
if (typeof(window.print) != 'undefined') {
    window.print();
}
//-->
</script>