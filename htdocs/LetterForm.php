<?php
/**
 * DM Letter
 */
require_once("config.inc.php");
require_once($cfg['path_dbe'] . '/DBEUser.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once('../phplib4/template_PEAR.inc');
include($_SERVER['DOCUMENT_ROOT'] . '/fckeditor/fckeditor.php');
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
$thing = null;
$templateName = $_REQUEST['letterTemplate'];

$templatePath = LETTER_TEMPLATE_DIR . "/custom/" . $templateName;

if (!file_exists($templatePath)) {
    die ($templatePath . ' not found');
}

/* set up template */
$template = new Template_PHPLIB(
    LETTER_TEMPLATE_DIR . "/custom/",
    'remove'
);

$template->setFile(
    'Page',
    $templateName
);

$buCustomer = new BUCustomer($thing);
$buCustomer->getContactByID(
    $_REQUEST['contactID'],
    $dsContact
);
$dsContact->fetchNext();
$dsCustomer = new DataSet($thing);
$buCustomer->getCustomerByID(
    $dsContact->getValue(DBEContact::customerID),
    $dsCustomer
);
$dsCustomer->fetchNext();

$name = $dsContact->getValue(DBEContact::title) . ' ' . $dsContact->getValue(DBEContact::lastName);

$dsSite = new DataSet($thing);
$buCustomer->getSiteByCustomerIDSiteNo(
    $dsContact->getValue(DBEContact::customerID),
    $dsContact->getValue(DBEContact::siteNo),
    $dsSite
);

if ($dsContact->getValue(DBEContact::firstName)) {
    $firstName = $dsContact->getValue(DBEContact::firstName);
    $addressee = $dsContact->getValue(DBEContact::title) . ' ' . $firstName[0] . ' ' . $dsContact->getValue(
            DBEContact::lastName
        );
} else {
    $addressee = $dsContact->getValue(DBEContact::title) . ' ' . $dsContact->getValue(DBEContact::lastName);
}

$dbeUser = new DBEUser($thing);
$dbeUser->setValue(
    DBEUser::userID,
    $GLOBALS['auth']->is_authenticated()
);
$dbeUser->getRow();

$address =
    $dsCustomer->getValue(DBECustomer::name) . "<BR/>" .
    $dsSite->getValue(DBESite::add1) . "<BR/>";

if ($dsSite->getValue(DBESite::add2) != '') {
    $address .= $dsSite->getValue(DBESite::add2) . "<BR/>";
}
if ($dsSite->getValue(DBESite::add3) != '') {
    $address .= $dsSite->getValue(DBESite::add3) . "<BR/>";
}
$address .= $dsSite->getValue(DBESite::town) . "<BR/>";
if ($dsSite->getValue(DBESite::county) != '') {
    $address .= $dsSite->getValue(DBESite::county) . "<BR/>";
}
$address .= $dsSite->getValue(DBESite::postcode) . "<BR/>";


$template->setVar(
    array(
        'title'         => $dsContact->getValue(DBEContact::title),
        'name'          => $name,
        'firstName'     => $dsContact->getValue(DBEContact::firstName),
        'lastName'      => $dsContact->getValue(DBEContact::lastName),
        'addressee'     => $addressee,
        'customer'      => $dsCustomer->getValue(DBECustomer::name),
        'address'       => $address,
        'date'          => date('l, jS F Y'),
        'userFirstName' => $dbeUser->getValue(DBEUser::firstName),
        'userLastName'  => $dbeUser->getValue(DBEUser::lastName),
        'userJobTitle'  => $dbeUser->getValue(DBEUser::jobTitle)
    )
);

$template->parse(
    'output',
    'Page',
    true
);
$file = $template->getVar('output');

$FCKEditor = new FCKeditor('letterText');
$FCKEditor->BasePath = '/FCKeditor/';
$FCKEditor->Height = '800px';
$FCKEditor->Value = $file;
?>
    <html lang="en">
    <head>
        <title>Client Letter</title>
        <meta http-equiv="Content-Type"
              content="text/html; charset=utf-8"
        >
        <link href="LetterForm.css"
              rel="stylesheet"
              type="text/css"
        >
    </head>
    <body>
    <h1><?php echo $templateName ?>
        to <?php echo $dsContact->getValue(DBEContact::firstName) . ' ' . $dsContact->getValue(
                DBEContact::lastName
            ) ?></h1>
    <h2><?php echo stripslashes($_REQUEST['contactName']) ?></h2>
    <!--suppress HtmlDeprecatedAttribute -->
    <table width="800"
           border="0"
           class="singleBorder"
    >
        <tr>
            <td class="promptText">
                <?php $FCKEditor->Create() ?>
            </td>
        </tr>
    </table>
    <p>&nbsp;</p>
    </body>
    </html>
<?php page_close(); ?>