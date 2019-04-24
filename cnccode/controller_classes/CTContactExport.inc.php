<?php /**
 * Contact controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUContactExport.inc.php');
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUSector.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEItem.inc.php');
require_once($cfg['path_dbe'] . '/DBEStandardText.inc.php');

class CTContactExport extends CTCNC
{
    const searchFormCustomerID = 'customerID';
    const searchFormSendMailshotFlag = 'sendMailshotFlag';
    const searchFormMailshot2Flag = 'mailshot2Flag';
    const searchFormMailshot3Flag = 'mailshot3Flag';
    const searchFormMailshot4Flag = 'mailshot4Flag';
    const searchFormMailshot8Flag = 'mailshot8Flag';
    const searchFormMailshot9Flag = 'mailshot9Flag';
    const searchFormExportEmailOnlyFlag = 'exportEmailOnlyFlag';
    const searchFormBroadbandRenewalFlag = 'broadbandRenewalFlag';
    const searchFormBroadbandIsp = 'broadbandIsp';
    const searchFormQuotationRenewalFlag = 'quotationRenewalFlag';
    const searchFormContractRenewalFlag = 'contractRenewalFlag';
    const searchFormContractItemID = 'contractItemID';
    const searchFormQuotationItemID = 'quotationItemID';
    const searchFormProspectFlag = 'prospectFlag';
    const searchFormNoOfPCs = 'noOfPCs';
    const searchFormNoOfServers = 'noOfServers';
    const searchFormNewCustomerFromDate = 'newCustomerFromDate';
    const searchFormNewCustomerToDate = 'newCustomerToDate';
    const searchFormDroppedCustomerFromDate = 'droppedCustomerFromDate';
    const searchFormDroppedCustomerToDate = 'droppedCustomerToDate';
    const searchFormFromEmailAddress = 'fromEmailAddress';
    const searchFormEmailSubject = 'emailSubject';
    const searchFormEmailBody = 'emailBody';
    const searchFormSupportLevel = 'supportLevel';
    const searchFormReviewUser = 'reviewUser';
    const searchFormHrUser = 'hrUser';


    /**
     * Dataset for contact record storage.
     *
     * @var     DSForm
     * @access  private
     */
    public $dsContact;
    public $noOfPCs =
        array(
            '0',
            '1-5',
            '6-10',
            '11-25',
            '26-50',
            '51-99',
            '100+'
        );
    public $prospectFlags =
        array(
            'Customers and Prospects' => null,
            'Prospects'               => 'Y',
            'Customers'               => 'N'
        );
    /**
     * @var BUContactExport
     */
    public $buContactExport;

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
     * @throws Exception
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
     * @throws Exception
     */
    function export()
    {
        $this->setMethodName('search');

        $dsSearchForm = new DSForm ($this);
        $dsSearchForm->addColumn(
            self::searchFormCustomerID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormSendMailshotFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormMailshot2Flag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormMailshot3Flag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormMailshot4Flag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormMailshot8Flag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormMailshot9Flag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormExportEmailOnlyFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormBroadbandRenewalFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormBroadbandIsp,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            self::searchFormQuotationRenewalFlag,
            DA_YN,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            self::searchFormContractRenewalFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormContractItemID,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormQuotationItemID,
            DA_YN,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            self::searchFormProspectFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormNoOfPCs,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormNoOfServers,
            DA_INTEGER,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            self::searchFormNewCustomerFromDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormNewCustomerToDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormDroppedCustomerFromDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormDroppedCustomerToDate,
            DA_DATE,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            self::searchFormFromEmailAddress,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormEmailSubject,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormEmailBody,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            self::searchFormSupportLevel,
            DA_ARRAY,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            self::searchFormReviewUser,
            DA_YN_FLAG,
            DA_ALLOW_NULL
        );

        $dsSearchForm->addColumn(
            self::searchFormHrUser,
            DA_YN_FLAG,
            DA_ALLOW_NULL
        );


        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);

        $sectorIDs = array();
        $quotationItemIDs = array();
        $contractItemIDs = array();

        if ($_REQUEST['Export'] || $_REQUEST['SendEmail']) {

            if (isset($_REQUEST['searchForm'][1]['supportLevel'])) {
                $_REQUEST['searchForm'][1]['supportLevel'] = json_encode($_REQUEST['searchForm'][1]['supportLevel']);
            }
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
                    self::searchFormExportEmailOnlyFlag,
                    false
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

                if (!$dsSearchForm->getValue(self::searchFormFromEmailAddress)) {
                    $dsSearchForm->setMessage(
                        self::searchFormFromEmailAddress,
                        'Required'
                    );
                }
                if (!$dsSearchForm->getValue(self::searchFormEmailSubject)) {
                    $dsSearchForm->setMessage(
                        self::searchFormEmailSubject,
                        'Required'
                    );
                }
                if (!$dsSearchForm->getValue(self::searchFormEmailBody)) {
                    $dsSearchForm->setMessage(
                        self::searchFormEmailBody,
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

        $urlSubmit = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCNC_ACT_SEARCH
            )
        );

        $urlCustomerPopup =
            Controller::buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $customerString = null;
        if ($dsSearchForm->getValue(self::searchFormCustomerID)) {
            $buCustomer = new BUCustomer ($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(self::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $this->template->set_block(
            'ContactExport',
            'supportLevelBlock',
            'selectSupportLevel'
        );

        $buContact = new BUContact($this);
        $buContact->supportLevelDropDown(
            '--',
            $this->template,
            'supportLevelSelected',
            'supportLevelValue',
            'supportLevelDescription',
            'selectSupportLevel',
            'supportLevelBlock'
        );

        $this->setPageTitle('Export Contacts');

        $this->template->set_var(
            array(
                'customerID'                   => $dsSearchForm->getValue(self::searchFormCustomerID),
                'customerString'               => $customerString,
                'prospectFlagBothSelected'     => $dsSearchForm->getValue(
                    self::searchFormProspectFlag
                ) == null ? 'SELECTED' : null,
                'prospectFlagProspectSelected' => $dsSearchForm->getValue(
                    self::searchFormProspectFlag
                ) == 'Y' ? 'SELECTED' : null,
                'prospectFlagCustomerSelected' => $dsSearchForm->getValue(
                    self::searchFormProspectFlag
                ) == 'N' ? 'SELECTED' : null,
                'sendMailshotFlagChecked'      => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormSendMailshotFlag)
                ),
                'exportEmailOnlyFlagChecked'   => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormExportEmailOnlyFlag)
                ),
                'mailshot2FlagChecked'         => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormMailshot2Flag)
                ),
                'mailshot3FlagChecked'         => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormMailshot3Flag)
                ),
                'mailshot4FlagChecked'         => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormMailshot4Flag)
                ),
                'mailshot8FlagChecked'         => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormMailshot8Flag)
                ),
                'mailshot9FlagChecked'         => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormMailshot9Flag)
                ),
                'mailshot2FlagDesc'            => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot2FlagDesc)
                ),
                'mailshot3FlagDesc'            => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot3FlagDesc)
                ),
                'mailshot4FlagDesc'            => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot4FlagDesc)
                ),
                'mailshot8FlagDesc'            => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot8FlagDesc)
                ),
                'mailshot9FlagDesc'            => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot9FlagDesc)
                ),
                'reviewUserChecked'            => Controller::htmlChecked(
                    $dsSearchForm->getValue(DBEContact::reviewUser)
                ),
                'hrUserChecked'                => Controller::htmlChecked(
                    $dsSearchForm->getValue(DBEContact::hrUser)
                ),
                'noOfPCs'                      => $dsSearchForm->getValue(self::searchFormNoOfPCs),
                'noOfServers'                  => $dsSearchForm->getValue(self::searchFormNoOfServers),
                'newCustomerFromDate'          => $dsSearchForm->getValue(self::searchFormNewCustomerFromDate),
                'newCustomerToDate'            => $dsSearchForm->getValue(self::searchFormNewCustomerToDate),
                'droppedCustomerFromDate'      => $dsSearchForm->getValue(self::searchFormDroppedCustomerFromDate),
                'droppedCustomerToDate'        => $dsSearchForm->getValue(self::searchFormDroppedCustomerToDate),
                'broadbandRenewalFlagChecked'
                                               => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormBroadbandRenewalFlag)
                ),
                'quotationRenewalFlagChecked'
                                               => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormQuotationRenewalFlag)
                ),
                'contractRenewalFlagChecked'
                                               => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormContractRenewalFlag)
                ),
                'broadbandIsp'                 => $dsSearchForm->getValue(self::searchFormBroadbandIsp),
                'fromEmailAddress'             => $dsSearchForm->getValue(self::searchFormFromEmailAddress),
                'emailSubject'                 => $dsSearchForm->getValue(self::searchFormEmailSubject),
                'emailBody'                    => $dsSearchForm->getValue(self::searchFormEmailBody),
                'fromEmailAddressMessage'      => $dsSearchForm->getMessage(self::searchFormFromEmailAddress),
                'emailSubjectMessage'          => $dsSearchForm->getMessage(self::searchFormEmailSubject),
                'emailBodyMessage'             => $dsSearchForm->getMessage(self::searchFormEmailBody),
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
                $dbeItem->getValue(DBEItem::itemID),
                $contractItemIDs
            )) ? CT_CHECKED : null;

            $this->template->set_var(
                array(
                    'contractItemIDChecked'   => $itemChecked,
                    'contractItemID'          => $dbeItem->getValue(DBEItem::itemID),
                    'contractItemDescription' => $dbeItem->getValue(DBEItem::description)
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
                $dbeItem->getValue(DBEItem::itemID),
                $quotationItemIDs
            )) ? CT_CHECKED : null;

            $this->template->set_var(
                array(
                    'quotationItemIDChecked'   => $itemChecked,
                    'quotationItemID'          => $dbeItem->getValue(DBEItem::itemID),
                    'quotationItemDescription' => $dbeItem->getValue(DBEItem::description)
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
        $dsSector = new DataSet($this);
        $this->template->set_block(
            'ContactExport',
            'sectorBlock',
            'sectors'
        );
        $buSector->getAll($dsSector);
        while ($dsSector->fetchNext()) {
            $this->template->set_var(
                array(
                    'sectorID'          => $dsSector->getValue(DBESector::sectorID),
                    'sectorDescription' => $dsSector->getValue(DBESector::description),
                    'sectorSelected'    => (in_array(
                        $dsSector->getValue(DBESector::sectorID),
                        $sectorIDs
                    )) ? CT_SELECTED : null
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
                    'noOfPCsSelected' => $value == $dsSearchForm->getValue(self::searchFormNoOfPCs) ? CT_SELECTED : null
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
                    'prospectFlagSelected'    => $value == $dsSearchForm->getValue(
                        self::searchFormProspectFlag
                    ) ? CT_SELECTED : null
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

    /**
     * @param $resultSet mysqli_result
     */
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
            DBEStandardText::stt_standardtexttypeno,
            CONFIG_STANDARD_TEXT_TYPE_EMAIL
        );

        $dbeStandardText->getRowsByColumn(
            DBEStandardText::stt_standardtexttypeno,
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
                    'standardTextContent'     => htmlentities($dbeStandardText->getValue(DBEStandardText::stt_text)),
                    'standardTextDescription' => $dbeStandardText->getValue(DBEStandardText::stt_desc)
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
    OR con_mailflag2 = 'Y' 
    OR con_mailflag3 = 'Y' 
    OR con_mailflag4 = 'Y' 
    OR con_mailflag8 = 'Y' 
    OR con_mailflag9 = 'Y' 
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
                    ' ',
                    $db->Record['firstName']
                ),
                str_replace(
                    ',',
                    ' ',
                    $db->Record['lastName']
                ),
                str_replace(
                    ',',
                    ' ',
                    $db->Record['company']
                ),
                str_replace(
                    ',',
                    ' ',
                    $db->Record['mobile']
                ),
                null,
                null,
                null,
                str_replace(
                    ',',
                    ' ',
                    $db->Record['business']
                ),
                str_replace(
                    ',',
                    ' ',
                    $db->Record['business2']
                ),
                str_replace(
                    ',',
                    ' ',
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
                ) . "\n";
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
}