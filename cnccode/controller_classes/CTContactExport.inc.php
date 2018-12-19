<?php /**
 * Contact controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUContactExport.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUSector.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEItem.inc.php');
require_once($cfg['path_dbe'] . '/DBEStandardText.inc.php');

class CTContactExport extends CTCNC
{
    /**
     * Dataset for contact record storage.
     *
     * @var     DSForm
     * @access  private
     */
    var $dsContact = '';
    var $noOfPCs =
        array(
            '0',
            '1-5',
            '6-10',
            '11-25',
            '26-50',
            '51-99',
            '100+'
        );
    var $prospectFlags =
        array(
            'Customers and Prospects' => '',
            'Prospects'               => 'Y',
            'Customers'               => 'N'
        );

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = [
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buContactExport = new BUContactExport($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch (@$_REQUEST['action']) {
            case '3cxExport':
                echo json_encode(
                    [
                        "base64Data" => $this->phone3CXExport()
                    ]
                );
//                echo "<a href='data:application/x-zip-compressed;base64," . . "' download='export.zip' >Download Zip</a>";
                exit;
            default:
                $this->export();
        }
        exit;
    }

    /**
     * see if parent form fields need to be populated
     * @access private
     */
    function export()
    {
        $this->setMethodName('search');

        $dsSearchForm = new DSForm ($this);
        $dsSearchForm->addColumn(
            'customerID',
            DA_ID,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'sendMailshotFlag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'mailshot1Flag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'mailshot2Flag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'mailshot3Flag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'mailshot4Flag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'mailshot5Flag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'mailshot6Flag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'mailshot7Flag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'mailshot8Flag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'mailshot9Flag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'mailshot10Flag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'exportEmailOnlyFlag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'broadbandRenewalFlag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'broadbandIsp',
            DA_STRING,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            'quotationRenewalFlag',
            DA_YN,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            'contractRenewalFlag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'contractItemID',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'quotationItemID',
            DA_YN,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            'prospectFlag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'noOfPCs',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'noOfServers',
            DA_INTEGER,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            'newCustomerFromDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'newCustomerToDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'droppedCustomerFromDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'droppedCustomerToDate',
            DA_DATE,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            'fromEmailAddress',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'emailSubject',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            'emailBody',
            DA_STRING,
            DA_ALLOW_NULL
        );

        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $sectorIDs = array();
        $quotationItemIDs = array();
        $contractItemIDs = array();

        if ($_REQUEST['Export'] || $_REQUEST['SendEmail']) {

            $dsSearchForm->populateFromArray($_REQUEST['searchForm']);

            if ($_REQUEST['quotationItemIDs']) {

                $quotationItemIDs = $_REQUEST['quotationItemIDs'];

            }

            if ($_REQUEST['contractItemIDs']) {

                $contractItemIDs = $_REQUEST['contractItemIDs'];

            }

            if ($_REQUEST['sectorIDs']) {

                $sectorIDs = $_REQUEST['sectorIDs'];

            }

            if ($_REQUEST['SendEmail']) {

                $dsSearchForm->setValue(
                    'exportEmailOnlyFlag',
                    0
                );

            }

            $results =
                $this->buContactExport->search(
                    $dsSearchForm,
                    $quotationItemIDs,
                    $contractItemIDs,
                    $sectorIDs
                );

            if ($_REQUEST['Export']) {

                $this->generateCSV($results);
                exit;

            } else {

                if (!$dsSearchForm->getValue('fromEmailAddress')) {
                    $dsSearchForm->setMessage(
                        'fromEmailAddress',
                        'Required'
                    );
                }
                if (!$dsSearchForm->getValue('emailSubject')) {
                    $dsSearchForm->setMessage(
                        'emailSubject',
                        'Required'
                    );
                }
                if (!$dsSearchForm->getValue('emailBody')) {
                    $dsSearchForm->setMessage(
                        'emailBody',
                        'Required'
                    );
                }
                if (count($dsSearchForm->message) == 0) {
                    $this->buContactExport->sendEmail(
                        $dsSearchForm,
                        $results
                    );
                }

            }

        }

        $this->setTemplateFiles(
            'ContactExport',
            'ContactExport.inc'
        );

        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCNC_ACT_SEARCH
            )
        );

        $urlCustomerPopup =
            $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        if ($dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue('customerID'),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $this->setPageTitle('Export Contacts');

        $this->template->set_var(
            array(
                'customerID'                   => $dsSearchForm->getValue('customerID'),
                'customerString'               => $customerString,
                'prospectFlagBothSelected'     => $dsSearchForm->getValue('prospectFlag') == '' ? 'SELECTED' : '',
                'prospectFlagProsepctSelected' => $dsSearchForm->getValue('prospectFlag') == 'Y' ? 'SELECTED' : '',
                'prospectFlagCustomerSelected' => $dsSearchForm->getValue('prospectFlag') == 'N' ? 'SELECTED' : '',
                'sendMailshotFlagChecked'      => Controller::htmlChecked($dsSearchForm->getValue('sendMailshotFlag')),
                'exportEmailOnlyFlagChecked'   => Controller::htmlChecked(
                    $dsSearchForm->getValue('exportEmailOnlyFlag')
                ),
                'mailshot1FlagChecked'         => Controller::htmlChecked($dsSearchForm->getValue('mailshot1Flag')),
                'mailshot2FlagChecked'         => Controller::htmlChecked($dsSearchForm->getValue('mailshot2Flag')),
                'mailshot3FlagChecked'         => Controller::htmlChecked($dsSearchForm->getValue('mailshot3Flag')),
                'mailshot4FlagChecked'         => Controller::htmlChecked($dsSearchForm->getValue('mailshot4Flag')),
                'mailshot5FlagChecked'         => Controller::htmlChecked($dsSearchForm->getValue('mailshot5Flag')),
                'mailshot6FlagChecked'         => Controller::htmlChecked($dsSearchForm->getValue('mailshot6Flag')),
                'mailshot7FlagChecked'         => Controller::htmlChecked($dsSearchForm->getValue('mailshot7Flag')),
                'mailshot8FlagChecked'         => Controller::htmlChecked($dsSearchForm->getValue('mailshot8Flag')),
                'mailshot9FlagChecked'         => Controller::htmlChecked($dsSearchForm->getValue('mailshot9Flag')),
                'mailshot10FlagChecked'        => Controller::htmlChecked($dsSearchForm->getValue('mailshot10Flag')),
                'mailshot1FlagDesc'            => Controller::htmlDisplayText($dsHeader->getValue('mailshot1FlagDesc')),
                'mailshot2FlagDesc'            => Controller::htmlDisplayText($dsHeader->getValue('mailshot2FlagDesc')),
                'mailshot3FlagDesc'            => Controller::htmlDisplayText($dsHeader->getValue('mailshot3FlagDesc')),
                'mailshot4FlagDesc'            => Controller::htmlDisplayText($dsHeader->getValue('mailshot4FlagDesc')),
                'mailshot5FlagDesc'            => Controller::htmlDisplayText($dsHeader->getValue('mailshot5FlagDesc')),
                'mailshot6FlagDesc'            => Controller::htmlDisplayText($dsHeader->getValue('mailshot6FlagDesc')),
                'mailshot7FlagDesc'            => Controller::htmlDisplayText($dsHeader->getValue('mailshot7FlagDesc')),
                'mailshot8FlagDesc'            => Controller::htmlDisplayText($dsHeader->getValue('mailshot8FlagDesc')),
                'mailshot9FlagDesc'            => Controller::htmlDisplayText($dsHeader->getValue('mailshot9FlagDesc')),
                'mailshot10FlagDesc'           => Controller::htmlDisplayText(
                    $dsHeader->getValue('mailshot10FlagDesc')
                ),
                'noOfPCs'                      => $dsSearchForm->getValue('noOfPCs'),
                'noOfServers'                  => $dsSearchForm->getValue('noOfServers'),
                'newCustomerFromDate'          => $dsSearchForm->getValue('newCustomerFromDate'),
                'newCustomerToDate'            => $dsSearchForm->getValue('newCustomerToDate'),
                'droppedCustomerFromDate'      => $dsSearchForm->getValue('droppedCustomerFromDate'),
                'droppedCustomerToDate'        => $dsSearchForm->getValue('droppedCustomerToDate'),
                'broadbandRenewalFlagChecked'
                                               => Controller::htmlChecked(
                    $dsSearchForm->getValue('broadbandRenewalFlag')
                ),
                'quotationRenewalFlagChecked'
                                               => Controller::htmlChecked(
                    $dsSearchForm->getValue('quotationRenewalFlag')
                ),
                'contractRenewalFlagChecked'
                                               => Controller::htmlChecked(
                    $dsSearchForm->getValue('contractRenewalFlag')
                ),
                'broadbandIsp'                 => $dsSearchForm->getValue('broadbandIsp'),
                'fromEmailAddress'             => $dsSearchForm->getValue('fromEmailAddress'),
                'emailSubject'                 => $dsSearchForm->getValue('emailSubject'),
                'emailBody'                    => $dsSearchForm->getValue('emailBody'),
                'fromEmailAddressMessage'      => $dsSearchForm->getMessage('fromEmailAddress'),
                'emailSubjectMessage'          => $dsSearchForm->getMessage('emailSubject'),
                'emailBodyMessage'             => $dsSearchForm->getMessage('emailBody'),
                'urlCustomerPopup'             => $urlCustomerPopup,
                'urlSubmit'                    => $urlSubmit
            )
        );

        // contract item selector

        $dbeItem = new DBEItem($this);
        $dbeItem->getRenewalTypeRows(2);

        $this->template->set_block(
            'ContactExport',
            'contractItemBlock',
            'contractItemRows'
        );

        while ($dbeItem->fetchNext()) {

            $itemChecked = (in_array(
                $dbeItem->getValue('itemID'),
                $contractItemIDs
            )) ? CT_CHECKED : '';

            $this->template->set_var(
                array(
                    'contractItemIDChecked'   => $itemChecked,
                    'contractItemID'          => $dbeItem->getValue('itemID'),
                    'contractItemDescription' => $dbeItem->getValue('description')
                )
            );
            $this->template->parse(
                'contractItemRows',
                'contractItemBlock',
                true
            );
        }

        // quotation item selector

        $dbeItem = new DBEItem($this);
        $dbeItem->getRenewalTypeRows(3);

        $this->template->set_block(
            'ContactExport',
            'quotationItemBlock',
            'quotationItemRows'
        );

        while ($dbeItem->fetchNext()) {

            $itemChecked = (in_array(
                $dbeItem->getValue('itemID'),
                $quotationItemIDs
            )) ? CT_CHECKED : '';

            $this->template->set_var(
                array(
                    'quotationItemIDChecked'   => $itemChecked,
                    'quotationItemID'          => $dbeItem->getValue('itemID'),
                    'quotationItemDescription' => $dbeItem->getValue('description')
                )
            );
            $this->template->parse(
                'quotationItemRows',
                'quotationItemBlock',
                true
            );
        }

// sectors
        $buSector = new BUSector($this);
        $this->template->set_block(
            'ContactExport',
            'sectorBlock',
            'sectors'
        );
        $buSector->getAll($dsSector);
        while ($dsSector->fetchNext()) {
            $this->template->set_var(
                array(
                    'sectorID'          => $dsSector->getValue("sectorID"),
                    'sectorDescription' => $dsSector->getValue("description"),
                    'sectorSelected'    => (in_array(
                        $dsSector->getValue('sectorID'),
                        $sectorIDs
                    )) ? CT_SELECTED : ''
                )
            );
            $this->template->parse(
                'sectors',
                'sectorBlock',
                true
            );
        }

        $this->template->set_block(
            'ContactExport',
            'noOfPCsBlock',
            'noOfPCs'
        );

        foreach ($this->noOfPCs as $index => $value) {
            $this->template->set_var(
                array(
                    'noOfPCsValue'    => $value,
                    'noOfPCsSelected' => $value == $dsSearchForm->getValue('noOfPCs') ? CT_SELECTED : ''
                )
            );
            $this->template->parse(
                'noOfPCs',
                'noOfPCsBlock',
                true
            );
        }

        $this->template->set_block(
            'ContactExport',
            'prospectFlagBlock',
            'prospectFlags'
        );

        foreach ($this->prospectFlags as $index => $value) {

            $this->template->set_var(
                array(
                    'prospectFlag'            => $value,
                    'prospectFlagDescription' => $index,
                    'prospectFlagSelected'    => $value == $dsSearchForm->getValue('prospectFlag') ? CT_SELECTED : ''
                )
            );
            $this->template->parse(
                'prospectFlags',
                'prospectFlagBlock',
                true
            );
        }

        $this->standardTextList(
            'ContactExport',
            'standardTextBlock'
        );

        $this->template->parse(
            'CONTENTS',
            'ContactExport',
            true
        );

        $this->parsePage();

    } // end function displaySearchForm

    function generateCSV($resultSet)
    {

        $fileName = 'contacts.csv';

        Header('Content-type: text/plain');
        Header('Content-Disposition: attachment; filename=' . $fileName);

        $firstRow = true;

        while ($row = $resultSet->fetch_array(MYSQLI_ASSOC)) {
            /*
            Column names in first row
            */
            if ($firstRow) {
                echo implode(
                        ',',
                        array_keys($row)
                    ) . "\n";
                $firstRow = false;
            }
            /*
            double-quote all values to allow for commas inside field values
            */
            foreach ($row AS $key => $value) {
                $row[$key] = '"' . $value . '"';
            }

            echo implode(
                    ',',
                    $row
                ) . "\n";
        }

        $this->pageClose();
        exit;
    }

    function standardTextList(
        $template,
        $block
    )
    {
        $dbeStandardText = new DBEStandardText($this);
        $dbeStandardText->setValue(
            'stt_standardtexttypeno',
            CONFIG_STANDARD_TEXT_TYPE_EMAIL
        );

        $dbeStandardText->getRowsByColumn(
            'stt_standardtexttypeno',
            'stt_desc'
        );

        $this->template->set_block(
            $template,
            $block,
            'rows'
        );

        while ($dbeStandardText->fetchNext()) {

            $this->template->set_var(
                array(
                    'standardTextContent'     => htmlentities($dbeStandardText->getValue('stt_text')),
                    'standardTextDescription' => $dbeStandardText->getValue('stt_desc')
                )
            );
            $this->template->parse(
                'rows',
                $block,
                true
            );
        }

    }

    private function phone3CXExport()
    {
        global $db;
        $query = "SELECT 
  contact.`con_first_name` AS firstName,
  contact.`con_last_name` AS lastName,
  customer.`cus_name` AS company,
  REPLACE(address.`add_phone`, ' ', '') AS business,
  REPLACE(contact.`con_phone`, ' ', '') AS business2,
  REPLACE(
    contact.`con_mobile_phone`,
    ' ',
    ''
  ) AS mobile,
  contact.`con_email` AS email 
FROM
  contact 
  LEFT JOIN address 
    ON contact.`con_siteno` = address.add_siteno 
    AND address.add_custno = contact.`con_custno` 
  LEFT JOIN customer 
    ON contact.`con_custno` = customer.`cus_custno` 
WHERE customer.`cus_referred` <> 'Y' 
  AND (
    address.`add_phone` 
    OR contact.`con_phone` 
    OR contact.`con_mobile_phone`
  ) 
  AND (
    con_mailshot = 'Y' 
    OR con_mailflag1 = 'Y' 
    OR con_mailflag2 = 'Y' 
    OR con_mailflag3 = 'Y' 
    OR con_mailflag4 = 'Y' 
    OR con_mailflag5 = 'Y' 
    OR con_mailflag6 = 'Y' 
    OR con_mailflag7 = 'Y' 
    OR con_mailflag8 = 'Y' 
    OR con_mailflag9 = 'Y' 
    OR con_mailflag10 = 'Y' 
    OR con_mailflag11 = 'Y'
  )";

        $db->query($query);

        $count = 0;

        $files = [];

        $zip = new ZipArchive();
        $zip->open(
            '3cxExport.zip',
            ZipArchive::CREATE | ZipArchive::OVERWRITE
        );
        $files[] = 'export0.csv';
        $file = fopen(
            'export0.csv',
            "w"
        );
        $header = [
            'FirstName',
            "LastName",
            "Company",
            "Mobile",
            "Mobile2",
            "Home",
            "Home2",
            "Business",
            "Business2",
            "Email",
            "Other",
            "BusinessFax",
            "HomeFax",
            "Pager"
        ];
        fputcsv(
            $file,
            $header
        );


        while ($db->next_record(MYSQLI_ASSOC)) {
            if ($count == 999) {
                // we need to close the previous file
                fclose($file);
                $zip->addFile($files[count($files) - 1]);
                $files[] = 'export' . count($files) . ".csv";
                $files++;
                $file = fopen(
                    $files[count($files) - 1],
                    'w'
                );
                $str = implode(
                        ",",
                        $header
                    ) . "\n";
                fwrite(
                    $file,
                    $str
                );

                $count = 0;
            }
            $data = [
                str_replace(
                    ',',
                    '',
                    $db->Record['firstName']
                ),
                str_replace(
                    ',',
                    '',
                    $db->Record['lastName']
                ),
                str_replace(
                    ',',
                    '',
                    $db->Record['company']
                ),
                str_replace(
                    ',',
                    '',
                    $db->Record['mobile']
                ),
                null,
                null,
                null,
                str_replace(
                    ',',
                    '',
                    $db->Record['business']
                ),
                str_replace(
                    ',',
                    '',
                    $db->Record['business2']
                ),
                str_replace(
                    ',',
                    '',
                    $db->Record['email']
                ),
                null,
                null,
                null,
                null
            ];
            $str = implode(
                ',',
                $data
            );
            fwrite(
                $file,
                $str
            );
            $count++;
        };
        // we need to close the previous file
        fclose($file);
        $zip->addFile($files[count($files) - 1]);


        $zip->close();

        foreach ($files as $file) {
            unlink($file);
        }

        $fileData = file_get_contents('3cxExport.zip');

        unlink('3cxExport.zip');

        return base64_encode($fileData);
    }
}// end of class
?>