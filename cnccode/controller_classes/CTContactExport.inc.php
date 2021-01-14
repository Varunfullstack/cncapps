<?php /**
 * Contact controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_bu'] . '/BUContactExport.inc.php');
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUSector.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEItem.inc.php');
require_once($cfg['path_dbe'] . '/DBEStandardText.inc.php');

class CTContactExport extends CTCNC
{
    const searchFormCustomerID          = 'customerID';
    const searchFormSendMailshotFlag    = 'sendMailshotFlag';
    const searchFormMailshot2Flag       = 'mailshot2Flag';
    const searchFormMailshot3Flag       = 'mailshot3Flag';
    const searchFormMailshot8Flag       = 'mailshot8Flag';
    const searchFormMailshot9Flag       = 'mailshot9Flag';
    const searchFormMailshot11Flag      = 'mailshot11Flag';
    const searchFormExportEmailOnlyFlag = 'exportEmailOnlyFlag';
    const searchFormContractItemID      = 'contractItemID';
    const searchFormProspectFlag        = 'prospectFlag';
    const searchFormFromEmailAddress    = 'fromEmailAddress';
    const searchFormEmailSubject        = 'emailSubject';
    const searchFormEmailBody           = 'emailBody';
    const searchFormSupportLevel        = 'supportLevel';
    const searchFormReviewUser          = 'reviewUser';
    const searchFormHrUser              = 'hrUser';
    const searchCriteria                = 'searchCriteria';
    const searchFormReferredFlag        = "referredFlag";


    /**
     * Dataset for contact record storage.
     *
     * @var     DSForm
     * @access  private
     */
    public $dsContact;
    public $prospectFlags = array(
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
        $roles = SENIOR_MANAGEMENT_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(904);
        $this->buContactExport = new BUContactExport($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch (@$this->getAction()) {
            case '3cxExport':
                echo json_encode(
                    [
                        "base64Data" => $this->phone3CXExport()
                    ]
                );
                exit;
            default:
                $this->export();
        }
        exit;
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
  AND `active`";
        $db->query($query);
        $count = 0;
        $files = [];
        $zip   = new ZipArchive();
        $zip->open(
            '3cxExport.zip',
            ZipArchive::CREATE | ZipArchive::OVERWRITE
        );
        $files[] = 'export0.csv';
        $file    = fopen(
            'export0.csv',
            "w"
        );
        $header  = [
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
        $maxRows = 499;
        while ($db->next_record(MYSQLI_ASSOC)) {
            if ($count == $maxRows) {
                // we need to close the previous file
                fclose($file);
                $zip->addFile($files[count($files) - 1]);
                $files[] = 'export' . count($files) . ".csv";
                $files++;
                $file = fopen(
                    $files[count($files) - 1],
                    'w'
                );
                $str  = implode(
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
            $str  = implode(
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
    } // end function displaySearchForm

    /**
     * see if parent form fields need to be populated
     * @access private
     * @throws Exception
     */
    function export()
    {
        $this->setMethodName('search');
        $dsSearchForm = new DSForm ($this);
        $dsSearchForm->addColumn(self::searchCriteria, DA_STRING, DA_NOT_NULL);
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
            self::searchFormReferredFlag,
            DA_YN,
            DA_ALLOW_NULL,
            'N'
        );
        $dsSearchForm->addColumn(
            self::searchFormMailshot3Flag,
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
            self::searchFormMailshot11Flag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormExportEmailOnlyFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormContractItemID,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormProspectFlag,
            DA_YN,
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
        $dsSearchForm->addColumn(
            DBEContact::active,
            DA_BOOLEAN,
            DA_NOT_NULL,
            false
        );
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $contractItemIDs = array();
        $searchCriteria  = 'AND';
        if ($this->getParam('Export') || $this->getParam('SendEmail')) {

            $searchForm = $this->getParam('searchForm')[1];
            foreach ($searchForm as $key => $value) {
                if ($searchForm[$key] === '') {
                    $searchForm[$key] = null;
                }
            }
            if (empty($searchForm[self::searchFormReferredFlag])) {
                $searchForm[self::searchFormReferredFlag] = 'N';
            }
            if (isset($searchForm['supportLevel'])) {
                $searchForm['supportLevel'] = json_encode($searchForm['supportLevel']);
            }
            $dsSearchForm->populateFromArray([$searchForm]);
            if ($this->getParam('contractItemIDs')) {
                $contractItemIDs = $this->getParam('contractItemIDs');
            }
            if ($this->getParam('SendEmail')) {
                $dsSearchForm->setValue(
                    self::searchFormExportEmailOnlyFlag,
                    'N'
                );
            }
            $dsSearchForm->setValue(self::searchCriteria, $this->getParam('searchCriteria'));
            $searchCriteria = $this->getParam('searchCriteria');
            $results = $this->buContactExport->search(
                $dsSearchForm,
                $contractItemIDs
            );
            if ($this->getParam('Export')) {
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
        $urlSubmit        = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCNC_ACT_SEARCH
            )
        );
        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $customerString   = null;
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
                'mailshot8FlagChecked'         => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormMailshot8Flag)
                ),
                'mailshot9FlagChecked'         => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormMailshot9Flag)
                ),
                'mailshot11FlagChecked'        => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormMailshot11Flag)
                ),
                'mailshot2FlagDesc'            => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot2FlagDesc)
                ),
                'mailshot3FlagDesc'            => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot3FlagDesc)
                ),
                'mailshot8FlagDesc'            => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot8FlagDesc)
                ),
                'mailshot9FlagDesc'            => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot9FlagDesc)
                ),
                'mailshot11FlagDesc'           => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot11FlagDesc)
                ),
                'reviewUserChecked'            => Controller::htmlChecked(
                    $dsSearchForm->getValue(DBEContact::reviewUser)
                ),
                'referredFlagChecked'          => Controller::htmlChecked(
                    $dsSearchForm->getValue(self::searchFormReferredFlag)
                ),
                'activeChecked'                => $dsSearchForm->getValue(DBEContact::active) ? 'checked' : 'null',
                'hrUserChecked'                => Controller::htmlChecked(
                    $dsSearchForm->getValue(DBEContact::hrUser)
                ),
                'fromEmailAddress'             => $dsSearchForm->getValue(self::searchFormFromEmailAddress),
                'emailSubject'                 => $dsSearchForm->getValue(self::searchFormEmailSubject),
                'emailBody'                    => $dsSearchForm->getValue(self::searchFormEmailBody),
                'fromEmailAddressMessage'      => $dsSearchForm->getMessage(self::searchFormFromEmailAddress),
                'emailSubjectMessage'          => $dsSearchForm->getMessage(self::searchFormEmailSubject),
                'emailBodyMessage'             => $dsSearchForm->getMessage(self::searchFormEmailBody),
                'urlCustomerPopup'             => $urlCustomerPopup,
                'urlSubmit'                    => $urlSubmit,
                "andSelected"                  => $searchCriteria == 'AND' ? 'selected' : null,
                "orSelected"                   => $searchCriteria == 'OR' ? 'selected' : null,
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
        $this->template->parse(
            'CONTENTS',
            'ContactExport',
            true
        );
        $this->parsePage();

    }

    /**
     * @param $resultSet mysqli_result
     */
    function generateCSV($resultSet)
    {
        if (!$resultSet) {
            return;
        }
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
            foreach ($row as $key => $value) {
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

    function standardTextList($template,
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
}