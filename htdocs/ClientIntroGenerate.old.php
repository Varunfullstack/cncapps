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
require_once($cfg['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg['path_dbe'] . '/DBEUser.inc.php');
require_once($cfg['path_gc'] . '/Controller.inc.php');
$buCustomer = new BUCustomer($this);
$buCustomer->getContactByID($_REQUEST['contactID'], $dsContact);
$dsContact->fetchNext();

$buCustomer->getCustomerByID($dsContact->getValue('customerID'), $dsCustomer);
$dsCustomer->fetchNext();
$buCustomer->getSiteByCustomerIDSiteNo($dsContact->getValue('customerID'), $dsContact->getValue('siteNo'), $dsSite);
$dsContact->fetchNext();

$dbeUser=new DBEUser($this);
$dbeUser->setValue('userID', $GLOBALS['auth']->is_authenticated());
$dbeUser->getRow();
?>
<html>
<head>
<title><?php echo Controller::htmlDisplayText($dsCustomer->getValue('name')); ?></title>
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
<?php
/*
Address and date
*/
$firstName = $dsContact->getValue('firstName');
$addressee = $dsContact->getValue('title').' '.$firstName[0].' '.$dsContact->getValue('lastName');
echo Controller::htmlDisplayText($addressee).'<BR/>';
echo Controller::htmlDisplayText($dsCustomer->getValue('name')).'<BR/>';
echo Controller::htmlDisplayText($dsSite->getValue(DBESite::Add1)).'<BR/>';
if ($dsSite->getValue(DBESite::Add2) != ''){
	echo Controller::htmlDisplayText($dsSite->getValue(DBESite::Add2)).'<BR/>';
}
if ($dsSite->getValue(DBESite::Add3) != ''){
	echo Controller::htmlDisplayText($dsSite->getValue(DBESite::Add3)).'<BR/>';
}
echo Controller::htmlDisplayText($dsSite->getValue(DBESite::Town)).'<BR/>';
if ($dsSite->getValue(DBESite::County) != ''){
	echo Controller::htmlDisplayText($dsSite->getValue(DBESite::County)).'<BR/>';
}
echo Controller::htmlDisplayText($dsSite->getValue(DBESite::Postcode)).'<BR/>';
/*
Body
*/
?>
</P>
<P><?php echo date('l, jS F Y'); ?></P>
<P align="justify"><?php echo Controller::formatForHTML($_REQUEST['letterText']); ?></P>
<P>Yours sincerely,<P>
<P>
For and on behalf of<BR/>
COMPUTER & NETWORK CONSULTANTS LTD
</P>
<BR/>
<BR/>
<BR/>
<P>
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