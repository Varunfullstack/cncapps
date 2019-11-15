<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg['path_ct'] . '/CTContact.inc.php');
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

$thing = null;
// find all the active customers
$buCustomer = new BUCustomer($thing);
$dsCustomers = new DataSet($thing);
$buCustomer->getActiveCustomers($dsCustomers, true);
$dbeContact = new DBEContact($thing);

$customersFailingValidation = [];

while ($dsCustomers->fetchNext()) {
    $customerID = $dsCustomers->getValue(DBECustomer::customerID);

    $validationErrors = [
        "contactErrors"  => [],
        "customerErrors" => [],
        "siteErrors"     => [],
        "customerID"     => $customerID,
        "customerName"   => $dsCustomers->getValue(DBECustomer::name)
    ];
    $dsContacts = new DataSet($thing);
    $buCustomer->getContactsByCustomerID($customerID, $dsContacts);
    $atLeastOneAccount = false;
    $atLeastOneInvoice = false;
    $atLeastOneAtMostOneStatement = false;
    $atLeastOneMain = false;
    $atLeastOneReview = false;
    $statementCount = 0;
    $atLeastOneTopUp = !$buCustomer->hasPrepayContract($customerID);
    $atLeastOneReport = false;

    while ($dsContacts->fetchNext()) {
        $contactErrors = [];

        $contactID = $dsContacts->getValue(DBEContact::contactID);
        if (!$dsContacts->getValue(DBEContact::firstName)) {
            $contactErrors[] = "First Name Required";
        }

        if (!$dsContacts->getValue(DBEContact::lastName)) {
            $contactErrors[] = "Last Name Required";
        }

        if (!$dsContacts->getValue(DBEContact::title)) {
            $contactErrors[] = "Title Required";
        }

        if ($email = $dsContacts->getValue(DBEContact::email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $contactErrors[] = "Invalid Email";
            } else {
                if (!$dbeContact->validateUniqueEmail($email, $contactID)) {
                    $contactErrors[] = "Duplicated Email";
                }
            }
        }

        if ($dsContacts->getValue(DBEContact::phone) && !preg_match(
                "/^\d+$/",
                $dsContacts->getValue(DBEContact::phone)
            )) {
            $contactErrors[] = "Invalid Phone Number: " . $dsContacts->getValue(DBEContact::phone);
        }

        if ($dsContacts->getValue(DBEContact::mobilePhone) && !preg_match(
                "/^\d+$/",
                $dsContacts->getValue(DBEContact::mobilePhone)
            )) {
            $contactErrors[] = "Invalid Mobile Phone Number: " . $dsContacts->getValue(DBEContact::mobilePhone);
        }

        if ($dsContacts->getValue(DBEContact::accountsFlag) == 'Y' && !$atLeastOneAccount) {
            $atLeastOneAccount = true;
        }

        if ($dsContacts->getValue(DBEContact::mailshot2Flag) == 'Y' && !$atLeastOneInvoice) {
            $atLeastOneInvoice = true;
        }

        if ($dsContacts->getValue(DBEContact::mailshot4Flag) == 'Y') {
            if (!$atLeastOneAtMostOneStatement && !$statementCount) {
                $atLeastOneAtMostOneStatement = true;
                $statementCount++;
            } else {
                $atLeastOneAtMostOneStatement = false;
            }
        }

        if ($dsContacts->getValue(
                DBEContact::supportLevel
            ) == DBEContact::supportLevelMain && !$atLeastOneMain) {
            $atLeastOneMain = true;
        }

        if ($dsContacts->getValue(DBEContact::reviewUser) == 'Y' && !$atLeastOneReview) {
            $atLeastOneReview = true;
        }

        if ($dsContacts->getValue(DBEContact::mailshot8Flag) == 'Y' && !$atLeastOneTopUp) {
            $atLeastOneTopUp = true;
        }

        if ($dsContacts->getValue(DBEContact::mailshot9Flag) == 'Y' && !$atLeastOneReport) {
            $atLeastOneReport = true;
        }

        if (count($contactErrors)) {
            $validationErrors['contactErrors'][] = [
                "contactID" => $contactID,
                "firstName" => $dsContacts->getValue(DBEContact::firstName),
                "lastName"  => $dsContacts->getValue(DBEContact::lastName),
                "errors"    => $contactErrors
            ];
        }
    }
    $dbeSite = new DBESite($thing);
    $dbeSite->setValue(DBESite::customerID, $customerID);
    $dbeSite->getRowsByCustomerID();
    $siteErrors = [];
    while ($dbeSite->fetchNext()) {
        if (!$dbeSite->getValue(DBESite::maxTravelHours)) {
            $siteErrors[] = "Max Travel hours must be greater than 0";
        }
        if ($dbeSite->getValue(DBESite::phone) && !preg_match(
                "/^\d+$/",
                $dbeSite->getValue(DBESite::phone)
            )) {
            $siteErrors[] = "Invalid Phone Number: " . $dbeSite->getValue(DBESite::phone);
        }

        if (count($siteErrors)) {
            $validationErrors['siteErrors'][] = [
                "postCode" => $dbeSite->getValue(DBESite::postcode),
                "errors"   => $siteErrors
            ];
        }
    }


    // we went through all the contacts

    if (!$atLeastOneAccount) {
        $validationErrors['customerErrors'][] = "At least one contact must have Account flag checked";
    }

    if (!$atLeastOneInvoice) {
        $validationErrors['customerErrors'][] = "At least one contact must have Invoice flag checked";
    }

    if (!$atLeastOneAtMostOneStatement) {
        $validationErrors['customerErrors'][] = "At most and at least one contact must have Statement flag checked";
    }

    if (!$atLeastOneMain) {
        $validationErrors['customerErrors'][] = "At least one contact must have Main as Support Level";
    }

    if (!$atLeastOneReview) {
        $validationErrors['customerErrors'][] = "At least one contact must have Review flag checked";
    }

    if (!$atLeastOneTopUp) {
        $validationErrors['customerErrors'][] = "At least one contact must have TopUp flag checked";
    }

    if (!$atLeastOneReport) {
        $validationErrors['customerErrors'][] = "At least one contact must have Report flag checked";
    }

    if (count($validationErrors['customerErrors']) || count($validationErrors['contactErrors'])) {
        $customersFailingValidation[] = $validationErrors;
    }
}

if (!count($customersFailingValidation)) {
    echo 'No Errors were found';
    return;
}
$buMail = new BUMail($thing);

$template = new Template(
    EMAIL_TEMPLATE_DIR,
    "remove"
);
$template->set_file(
    'page',
    'ContactValidationFailedEmail.html'
);
$template->setBlock('page', 'individualContactValidationErrorBlock', 'individualContactErrors');
$template->setBlock('page', 'individualSiteValidationErrorBlock', 'individualSiteErrors');
$template->setBlock('page', 'contactBlock', 'contacts');
$template->setBlock('page', 'sitesBlock', 'sites');
$template->setBlock('page', 'customerWideBlock', 'customerErrors');
$template->setBlock('page', 'customersBlock', 'customers');

foreach ($customersFailingValidation as $customerErrors) {

    $template->setVar(
        [
            "customerNameLink" => "<a href='$_SERVER[HTTP_HOST]/Customer.php?action=dispEdit&customerID=$customerErrors[customerID]'>$customerErrors[customerName]</a>"
        ]
    );

    if (!count($customerErrors["customerErrors"])) {
        $template->setVar('customerErrors', "");
    } else {
        $firstValidation = true;
        foreach ($customerErrors["customerErrors"] as $customerFailedValidation) {
            $template->setVar(
                [
                    "customerWideValidation" => $customerErrors['customerName'] . $customerFailedValidation
                ]
            );
            $template->parse('customerErrors', 'customerWideBlock', !$firstValidation);
            if ($firstValidation) {
                $firstValidation = false;
            }
        }
    }

    if (!count($customerErrors['contactErrors'])) {
        $template->setVar('contacts', "");
    } else {

        $firstContact = true;
        foreach ($customerErrors['contactErrors'] as $contact) {
            $template->setVar(
                [
                    "firstName" => $contact['firstName'],
                    "lastName"  => $contact['lastName']
                ]
            );

            $template->parse('contacts', 'contactBlock', !$firstContact);
            if ($firstContact) {
                $firstContact = false;
            }

            $firstValidation = true;
            foreach ($contact['errors'] as $error) {
                $template->setVar(
                    ["contactValidationError" => $error]
                );
                $template->parse(
                    'individualContactErrors',
                    'individualContactValidationErrorBlock',
                    !$firstValidation
                );
                if ($firstValidation) {
                    $firstValidation = false;
                }
            }

        }
    }
    if (!count($customerErrors['siteErrors'])) {
        $template->setVar('sites', "");
    } else {

        $firstSite = true;
        foreach ($customerErrors['siteErrors'] as $site) {
            $template->setVar(
                [
                    "postCode" => $site['postCode'],
                ]
            );

            $template->parse('sites', 'sitesBlock', !$firstSite);
            if ($firstSite) {
                $firstSite = false;
            }

            $firstValidation = true;
            foreach ($site['errors'] as $error) {
                $template->setVar(
                    ["siteValidationError" => $error]
                );
                $template->parse(
                    'individualSiteErrors',
                    'individualSiteValidationErrorBlock',
                    !$firstValidation
                );
                if ($firstValidation) {
                    $firstValidation = false;
                }
            }

        }
    }
    $template->parse('customers', 'customersBlock', true);
}

$template->parse('OUTPUT', 'page');
$body = $template->getVar('OUTPUT');
echo $body;
$senderEmail = "sales@cnc-ltd.co.uk";
$toEmail = "contactvalidation@cnc-ltd.co.uk";
$subject = "Customers with invalid contact configurations";
$hdrs = array(
    'From'         => $senderEmail,
    'To'           => $toEmail,
    'Subject'      => $subject,
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);

$buMail->mime->setHTMLBody($body);

$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset'  => 'UTF-8',
    'html_charset'  => 'UTF-8',
    'head_charset'  => 'UTF-8'
);
$body = $buMail->mime->get($mime_params);

$hdrs = $buMail->mime->headers($hdrs);

$buMail->putInQueue(
    $senderEmail,
    $toEmail,
    $hdrs,
    $body
);