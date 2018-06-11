<html>
<head>
    <title><?php echo Controller::htmlDisplayText($dsCustomer->getValue('name')); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="form.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="1">
    <tr>
        <td width="56%" height="167" valign="bottom">
            <div align="center">
                <h1>Client Information Form</h1>
            </div>
        </td>
        <td width="44%" align="right" valign="top">
            <div align="right"><img src="images/cnc_logo.png" alt="CNC Logo" height="157" align="top"></div>
        </td>
    </tr>
</table>
<BR/>
<table width="120%" border="1" cellpadding="3" bordercolor="#999999" class="information">
    <tr>
        <td width="26%" class="label">Company Name:</td>
        <td colspan="5"><?php echo Controller::htmlDisplayText($dsCustomer->getValue('name')); ?></td>
    </tr>
    <tr>
        <td valign="top" class="label">Address:</td>
        <td colspan="5" valign="top">
            <P class="addressLine"><?php echo Controller::htmlDisplayText($dsSite->getValue(DBESite::add1)); ?></P>
            <P class="addressLine"><?php echo Controller::htmlDisplayText($dsSite->getValue(DBESite::add2)); ?></P>
            <P class="addressLine"><?php echo Controller::htmlDisplayText($dsSite->getValue(DBESite::add3)); ?></P>
            <P class="addressLine"><?php echo Controller::htmlDisplayText($dsSite->getValue(DBESite::town)); ?></P>
            <P class="addressLine"><?php echo Controller::htmlDisplayText($dsSite->getValue(DBESite::county)); ?></P>
            <P class="addressLine"><?php echo Controller::htmlDisplayText($dsSite->getValue(DBESite::postcode)); ?></P>
        </td>
    </tr>
    <tr>
        <td class="label">Contact Name:</td>
        <td colspan="5"><?php echo Controller::htmlDisplayText($dsContact->getValue('title') . ' ' . $dsContact->getValue('firstName') . ' ' . $dsContact->getValue('lastName')); ?></td>
    </tr>
    <tr>
        <td class="label">Telephone Number:</td>
        <td colspan="5"><?php echo Controller::htmlDisplayText($phone); ?></td>
    </tr>
    <tr>
        <td class="label">Fax Number:</td>
        <td colspan="5"><?php echo Controller::htmlDisplayText($dsContact->getValue('fax')); ?></td>
    </tr>
    <tr>
        <td class="label">Email:</td>
        <td colspan="5"><?php echo Controller::htmlDisplayText($dsContact->getValue('email')); ?></td>
    </tr>
    <tr>
        <td class="label">Company Representative:</td>
        <td colspan="5"><?php echo Controller::htmlDisplayText($dbeAttendeeUser->getValue('name')); ?></td>
    </tr>
    <tr>
        <td class="label">Date &amp; Time of Meeting:</td>
        <td colspan="5"><?php echo Controller::htmlDisplayText($_REQUEST['meetingDate'] . ' ' . $_REQUEST['meetingTime']); ?></td>
    </tr>
    <tr>
        <td colspan="6" valign="top" <?php if (!$newClient) echo 'height="510px"'; ?> ><p><strong>Reason For
                    Contact:</strong></p>
            <p style="font-size : 10pt"><?php echo Controller::formatForHTML($_REQUEST['meetingReason']) ?></p>
        </td>
    </tr>
    <?php
    if ($newClient) {
        ?>
        <tr>
            <td class="label">Server OS:</td>
            <td width="27%">&nbsp;</td>
            <td width="20%" class="label">No of Servers:</td>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Server Applications:</td>
            <td colspan="5">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Number of Workstations:</td>
            <td>&nbsp;</td>
            <td class="label">O/S:</td>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Cabling Structure:</td>
            <td>&nbsp;</td>
            <td class="label">Speed:</td>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Anti-virus:</td>
            <td colspan="5">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Fax Software:</td>
            <td colspan="5">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Accounts Software:</td>
            <td>&nbsp;</td>
            <td class="label">CIM Software:</td>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Existing Supplier:</td>
            <td>&nbsp;</td>
            <td class="label">Contract:</td>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Internet Provider:</td>
            <td colspan="5">&nbsp;</td>
        </tr>
        <tr>
            <td valign="top" class="label">Internet Connection:</td>
            <td colspan="5" valign="top"><p>Analogue / ISDN Router / ISDN
                    TA</p>
                <p>ADSL(________K) Leased Line(_________K)</p></td>
        </tr>
        <tr>
            <td class="label">Hardware Manufacturer:</td>
            <td colspan="5">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Telephone System:</td>
            <td>&nbsp;</td>
            <td class="label">Lines:</td>
            <td width="6%">&nbsp;</td>
            <td width="8%" class="label">Extn:</td>
            <td width="13%">&nbsp;</td>
        </tr>
        <?php
    }
    ?>
</table>
<P class="pageBreak"></P>
<table width="100%" border="0" cellspacing="0" cellpadding="1">
    <tr>
        <td width="56%" height="167px" valign="bottom">
            <div align="center">
                <h1>Client Information Form</h1>
            </div>
        </td>
        <td width="44%" align="right" valign="top">
            <div align="right"><img src="images/cnclogo.jpeg" alt="CNC Logo" width="421" height="157" align="top"></div>
        </td>
    </tr>
</table>
<br/>
<table border="1" cellpadding="3" bordercolor="#999999" class="information">
    <tr>
        <td height="780px" class="labelLeft" valign="top">Action Required/Notes:</td>
    </tr>
</table>
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