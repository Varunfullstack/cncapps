<?php
require_once("config.inc.php");
require_once($cfg['path_dbe'] . '/DBEUser.inc.php');
GLOBAL $cfg;
$thing = null;
page_open(
    array(
        'sess' => PHPLIB_CLASSNAME_SESSION,
        'auth' => PHPLIB_CLASSNAME_AUTH,
        '',
        ''
    )
);
$dbeUser = new DBEUser($thing);
$dbeUser->setValue(
    'userID',
    $GLOBALS['auth']->is_authenticated()
);
$dbeUser->getRow();

$dbeUserList = new DBEUser($thing);
$dbeUserList->getRows();

if (!isset($_REQUEST['attendeeUserID'])) {
    $_REQUEST['attendeeUserID'] = CONFIG_DEFAULT_MEETING_USERID;
}
if (!isset($_REQUEST['meetingDate'])) {
    $_REQUEST['meetingDate'] = date("l, jS F Y");
}
?>
    <html>
    <head>
        <title>Client Information Form</title>
        <meta http-equiv="Content-Type"
              content="text/html; charset=utf-8"
        >
        <link href="screen.css"
              rel="stylesheet"
              type="text/css"
        >
    </head>
    <body>
    <h1>Client Information Form</h1>
    <?php
    if (!isset($_REQUEST['contactName'])) {
        die ('contact name not passed');
    }
    if (!isset($_REQUEST['contactID'])) {
        die ('contactID not passed');
    }
    ?>
    <h2><?php echo stripslashes($_REQUEST['contactName']) ?></h2>
    <form name="clientInformation"
          method="post"
          action="ClientInformation.php"
    >
        <input type="hidden"
               name="contactID"
               value="<?php echo $_REQUEST['contactID'] ?>"
        >
        <table width="45%"
               border="0"
               class="singleBorder"
        >
            <tr>
                <td width="17%"
                    nowrap
                    class="promptText"
                > Date of Meeting:
                </td>
                <td width="22%"><input name="meetingDate"
                                       type="text"
                                       value="<?php echo $_REQUEST['meetingDate']; ?>"
                                       size="50"
                                       maxlength="50"
                    >
                </td>
                <td width="3%">&nbsp;</td>
                <td width="4%"
                    class="promptText"
                >Time:
                </td>
                <td width="54%">
                    <input name="meetingTime"
                           type="text"
                           id="meetingTime"
                           size="10"
                           maxlength="10"
                           value="<?php echo $_REQUEST['meetingTime']; ?>"
                    >
                </td>
            </tr>
            <tr>
                <td class="promptText">Attendee</td>
                <td colspan="4">
                    <select type="text"
                            name="attendeeUserID"
                    >
                        <?php while ($dbeUserList->fetchNext()) { ?>
                            <option <?php echo ($_REQUEST['attendeeUserID'] == $dbeUserList->getValue(
                                    'userID'
                                )) ? 'SELECTED' : ''; ?>
                                    value="<?php echo $dbeUserList->getValue('userID') ?>"
                            >
                                <?php echo $dbeUserList->getValue('firstName') . ' ' . $dbeUserList->getValue(
                                        'lastName'
                                    ) ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="promptText">Reason For Contact:</td>
                <td colspan="4"><textarea name="meetingReason"
                                          cols="50"
                                          rows="2"
                    ><?php echo $_REQUEST['meetingReason']; ?></textarea>
                </td>
            </tr>
            <tr>
                <td class="promptText">&nbsp;</td>
                <td colspan="4">
                    <input type="submit"
                           name="Submit"
                           value="Generate"
                    >
                    <input type="submit"
                           name="Confirmation"
                           value="Send Confirmation"
                    >
                    <?php echo $_REQUEST['emailStatus'] ?>
                </td>
            </tr>
        </table>
        <p>&nbsp;</p></form>
    <BR/>
    </body>
    </html>
<?php page_close(); ?>