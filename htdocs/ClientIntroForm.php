<?php
require_once("config.inc.php");
require_once($cfg['path_dbe'] . '/DBEUser.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerNew.inc.php');
GLOBAL $cfg;
page_open(
	array(
		'sess' => PHPLIB_CLASSNAME_SESSION,
		'auth' => PHPLIB_CLASSNAME_AUTH,
		'',
		''
	)
);

if (!isset($_REQUEST['contactID'])) {
	die ('contactID not passed');
}
$buCustomer = new BUCustomer($this);
$buCustomer->getContactByID($_REQUEST['contactID'], $dsContact);
$dsContact->fetchNext();

$buCustomer->getCustomerByID($dsContact->getValue(DBEContact::customerID), $dsCustomer);
$dsCustomer->fetchNext();

$name = $dsContact->getValue(DBEContact::title) . ' ' . $dsContact->getValue(DBEContact::lastName);
$letterBody = file_get_contents('letter_templates/new_client_introduction.txt');
$firstName = $dsContact->getValue(DBEContact::firstName);
$addressee = $dsContact->getValue(DBEContact::title).' '.$firstName[0].' '.$dsContact->getValue(DBEContact::lastName);

$buCustomer->getSiteByCustomerIDSiteNo($dsContact->getValue(DBEContact::customerID), $dsContact->getValue(DBEContact::siteNo), $dsSite);

$address =
	$addressee . "\r\n" .
	$dsCustomer->getValue(DBECustomer::name) . "\r\n" .
	$dsSite->getValue(DBESite::add1) . "\r\n";

if ($dsSite->getValue(DBESite::add2) != ''){
	$address .= $dsSite->getValue(DBESite::add2) . "\r\n";
}
if ($dsSite->getValue(DBESite::add3) != ''){
	$address .= $dsSite->getValue(DBESite::add3) . "\r\n";
}
$address .= $dsSite->getValue(DBESite::town) . "\r\n";
if ($dsSite->getValue(DBESite::county) != ''){
	$address .= $dsSite->getValue(DBESite::county) . "\r\n";
}
$address .= $dsSite->getValue(DBESite::postcode) . "\r\n";
?>
<html>
<head>
<title>Client Introduction Letter</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="screen.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Client Introduction Letter to <?php echo $dsContact->getValue(DBEContact::firstName).' '.$dsContact->getValue(DBEContact::lastName) ?></h1>
<h2><?php echo stripslashes($_REQUEST['contactName'])?></h2>
<form name="introductionLetter" method="post" action="ClientIntroGenerate.php">
	<input type="hidden" name="contactID" value="<?php echo $_REQUEST['contactID']?>">
	<input type="hidden" name="userID" value="<?php echo $GLOBALS['auth']->is_authenticated() ?>">
	<table width="100%" border="0" class="singleBorder">
		<tr>
			<td class="promptText"> <div align="left">
					<textarea name="letterText" cols="150" rows="30">
<?php echo $address . "\n\r" . date('l, jS F Y') ."\n\r" . "Dear " . $name . ",\n\r\n\r" . $letterBody;?>
					</textarea>
				</div></td>
		</tr>
		<tr>
			<td class="promptText"> <div align="left">
					<input type="submit" name="Submit" value="Generate Letter">
				</div></td>
		</tr>
	</table>
</form>
</body>
</html>
<?php page_close(); ?>