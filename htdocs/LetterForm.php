<?php
/**
DM Leter
*/
require_once("config.inc.php");
require_once($cfg['path_dbe'] . '/DBEUser.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerNew.inc.php');
require_once('../phplib4/template_PEAR.inc');
include( $_SERVER['DOCUMENT_ROOT'] . '/fckeditor/fckeditor.php') ;
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
if (!isset($_REQUEST['letterTemplate'])) {
	die ('letterTemplate not passed');
}

$templateName = $_REQUEST['letterTemplate'];

$templatePath = LETTER_TEMPLATE_DIR . "/custom/" . $templateName ;

if (!file_exists( $templatePath ) ){
	die ($templatePath . ' not found');
}

/* set up template */
$template=new Template_PHPLIB( LETTER_TEMPLATE_DIR . "/custom/", 'remove');

$template->setFile( 'Page', $templateName );

$buCustomer = new BUCustomer($this);
$buCustomer->getContactByID($_REQUEST['contactID'], $dsContact);
$dsContact->fetchNext();

$buCustomer->getCustomerByID($dsContact->getValue('customerID'), $dsCustomer);
$dsCustomer->fetchNext();

$name = $dsContact->getValue('title') . ' ' . $dsContact->getValue('lastName');


$buCustomer->getSiteByCustomerIDSiteNo($dsContact->getValue('customerID'), $dsContact->getValue('siteNo'), $dsSite);

if ( $dsContact->getValue('firstName') ){
	$firstName = $dsContact->getValue('firstName');
	$addressee = $dsContact->getValue('title').' '.$firstName[0].' '.$dsContact->getValue('lastName');
}
else{
	$addressee = $dsContact->getValue('title'). ' ' .$dsContact->getValue('lastName');
}

$dbeUser=new DBEUser($this);
$dbeUser->setValue('userID', $GLOBALS['auth']->is_authenticated() );
$dbeUser->getRow();

$address =
	$dsCustomer->getValue('name') . "<BR/>" .
	$dsSite->getValue('add1') . "<BR/>";

if ($dsSite->getValue('add2') != ''){
	$address .= $dsSite->getValue('add2') . "<BR/>";
}
if ($dsSite->getValue('add3') != ''){
	$address .= $dsSite->getValue('add3') . "<BR/>";
}
$address .= $dsSite->getValue('town') . "<BR/>";
if ($dsSite->getValue('county') != ''){
	$address .= $dsSite->getValue('county') . "<BR/>";
}
$address .= $dsSite->getValue('postcode') . "<BR/>";


$template->setVar(
	array(
		'title'	=> $dsContact->getValue('title'),
		'name'	=> $name,
		'formalName'	=> $formalName,
		'firstName'	=> $dsContact->getValue('firstName'),
		'lastName'	=> $dsContact->getValue('lastName'),
		'addressee'	=> $addressee,
		'customer'	=> $dsCustomer->getValue('name'),
		'address'		=> $address,
		'date'			=> date('l, jS F Y'),
		'userFirstName'	=> $dbeUser->getValue('firstName'),
		'userLastName'	=> $dbeUser->getValue('lastName'),
		'userJobTitle'	=> $dbeUser->getValue('jobTitle')
	)
);

$template->parse('output', 'Page', true);
$file = $template->getVar('output');

$oFCKeditor = new FCKeditor('letterText') ;
$oFCKeditor->BasePath = '/FCKeditor/';
$oFCKeditor->Height = '800px';
$oFCKeditor->Value = $file; 
/*
$oFCKeditor->Value		= $address . "<P>" . date('l, jS F Y') ."</P><P>" . "Dear " . $dsContact->getValue('title').' '.$dsContact->getValue('lastName'). ",</P>" . $letterBody;
*/
?>
<html>
<head>
<title>Client Letter</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="LetterForm.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1><?php echo $templateName ?> to <?php echo $dsContact->getValue('firstName').' '.$dsContact->getValue('lastName') ?></h1>
<h2><?php echo stripslashes($_REQUEST['contactName'])?></h2>
<table width="800" border="0" class="singleBorder">
	<tr>
		<td class="promptText">
		<?php $oFCKeditor->Create() ?>
		</td>
	</tr>
</table>
<p>&nbsp;</p>
</body>
</html>
<?php page_close(); ?>