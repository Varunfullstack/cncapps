<?php
/**
 * Customer Review Meetings business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 *
 */

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use setasign\FpdiProtection\FpdiProtection;

global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_bu"] . "/BUCustomer.inc.php");
require_once($cfg["path_bu"] . "/BURenewal.inc.php");
require_once($cfg["path_bu"] . "/BUCustomerAnalysisReport.inc.php");
require_once($cfg["path_dbe"] . "/DBEContact.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUCustomerReviewMeeting extends Business
{

    const searchFormCustomerID = 'customerID';
    const searchFormStartYearMonth = 'startYearMonth';
    const searchFormEndYearMonth = 'endYearMonth';
    const searchFormMeetingDate = 'meetingDate';

    const templateClientName = 'clientName';
    const templateReviewMeetingDate = 'reviewMeetingDate';

    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function generateEmails()
    {

        $this->setMethodName('generateEmails');

        $dbeContact = new DBEContact($this);

        $buSite = new BUSite($this);

        /* customers with a review meeting due within next 6 weeks */
        $sql =
            "SELECT
          cus_custno AS customerID,
          cus_name AS customerName,
          cns_logname AS accountManagerUsername,
          DATE_FORMAT( cus_last_review_meeting_date, '%d/%m/%Y') AS lastMeetingDate,
          DATE_FORMAT( DATE_ADD( cus_last_review_meeting_date, INTERVAL cus_review_meeting_frequency_months MONTH ), '%d/%m/%Y') AS nextMeetingDate,
          DATE_FORMAT( DATE_ADD( cus_last_review_meeting_date, INTERVAL cus_review_meeting_frequency_months MONTH ), '%Y%m%d') AS nextMeetingDateYmd          
        FROM
          customer
          JOIN consultant ON cns_consno = cus_account_manager_consno
        WHERE
          DATE_SUB( DATE_ADD( cus_last_review_meeting_date, INTERVAL cus_review_meeting_frequency_months MONTH ),INTERVAL 6 WEEK ) <= NOW()
          AND cus_review_meeting_email_sent_flag = 'N'
        ";

        $results = $this->db->query($sql);

        $customers = array();

        while ($row = $results->fetch_assoc()) {

            $customers[] = $row;
        }

        foreach ($customers as $customer) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
            );
            $template->set_file(
                'page',
                'CustomerReviewMeetingEmail.inc.html'
            );

            $urlCustomer =
                SITE_URL . '/Customer.php?customerID=' . $customer['customerID'] . '&action=dispEdit';

            $template->setVar(
                array(
                    'urlCustomer'     => $urlCustomer,
                    'lastMeetingDate' => $customer['lastMeetingDate'],
                    'nextMeetingDate' => $customer['nextMeetingDate']
                )
            );

            $template->set_block(
                'page',
                'contactBlock',
                'contacts'
            );

            /* contacts with DM flag set */
            $sql =
                "SELECT
            con_first_name AS firstName,
            con_last_name AS lastName,
            con_phone AS ddiPhone,
            con_mobile_phone AS mobilePhone,
            con_email AS emailAddress
          FROM
            contact
          WHERE
            con_discontinued <> 'Y'
            and reviewUser = 'Y'
            AND con_custno = " . $customer['customerID'];

            $results = $this->db->query($sql);

            while ($row = $results->fetch_assoc()) {

                $template->setVar(
                    array(
                        'firstName'    => $row['firstName'],
                        'lastName'     => $row['lastName'],
                        'ddiPhone'     => $row['ddiPhone'],
                        'mobilePhone'  => $row['mobilePhone'],
                        'emailAddress' => $row['emailAddress']
                    )
                );
                $template->parse(
                    'contacts',
                    'contactBlock',
                    true
                );
            }

            $template->parse(
                'output',
                'page',
                true
            );

            $body = $template->get_var('output');


            /* create a calendar attachment */
            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
            );
            $template->set_file(
                'page',
                'CustomerReviewMeeting.inc.ics'
            );
            $dbeContact->getMainSupportRowsByCustomerID($customer['customerID']);
            if ($dbeContact->fetchNext()) {
                $dsSite = new DataSet($this);
                $buSite->getSiteByID(
                    $customer['customerID'],
                    $dbeContact->getValue(DBEContact::siteNo),
                    $dsSite
                );
                /*
                SUMMARY;LANGUAGE=en-gb:Review Meeting - {customerName} {contactName} {contactPhone}
                LOCATION:{add1} {add2} {add3} {town} {county} {postcode}
                */
                if ($dbeContact->getValue(DBEContact::phone)) {
                    $phone = $dbeContact->getValue(DBEContact::phone);
                } else {
                    $phone = $dbeContact->getValue(DBEContact::mobilePhone);
                }

                $template->set_var(
                    array(
                        'add1'         => $dsSite->getValue(DBESite::add1),
                        'add2'         => $dsSite->getValue(DBESite::add2),
                        'add3'         => $dsSite->getValue(DBESite::add3),
                        'town'         => $dsSite->getValue(DBESite::town),
                        'county'       => $dsSite->getValue(DBESite::county),
                        'postcode'     => $dsSite->getValue(DBESite::postcode),
                        'contactPhone' => $phone,
                        'contactName'  => $dbeContact->getValue(DBEContact::firstName) . ' ' . $dbeContact->getValue(
                                DBEContact::lastName
                            )
                    )
                );

            }

            $template->set_var(
                array(
                    'nowYYYYMMDD'  => date('Ymd'),
                    'nowHHMMSS'    => date('His'),
                    'customerName' => $customer['customerName']
                )
            );

            $template->parse(
                'output',
                'page',
                true
            );
            $icsFile = $template->get_var('output');

            $buMail = new BUMail($this);

            $senderEmail = CONFIG_SALES_EMAIL;

            $subject = 'Review meeting with ' . $customer['customerName'] . ' due by ' . $customer['nextMeetingDate'];

            $toEmail = $customer['accountManagerUsername'] . '@' . CONFIG_PUBLIC_DOMAIN;

            $hdrs = array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => $subject,
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $buMail->mime->setHTMLBody($body);

            $buMail->mime->addAttachment(
                $icsFile,
                'text/calendar',
                'meeting.ics',
                false
            );

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
                $body      // to SD Managers
            );

            /* @todo set flag */
            $sql =
                "UPDATE
          customer
        SET
            cus_review_meeting_email_sent_flag = 'Y'
        WHERE
            cus_custno =" . $customer['customerID'];

            $this->db->query($sql);


        } // end customers

    } // end function

    /**
     * @param $customerID
     * @param $htmlBody
     * @param $meetingDate
     * @return bool
     * @throws Exception
     */
    public function generateAgendaPdf($customerID,
                                      $htmlBody,
                                      $meetingDate
    )
    {
        $buCustomer = new BUCustomer($this);

        $documentFolderPath = $buCustomer->getCustomerFolderPath($customerID);

        $reviewMeetingFolderPath = $documentFolderPath . '/Review Meetings';

        @mkdir(
            $reviewMeetingFolderPath,
            '0777',
            true
        );  // ensure folder exists

        require_once BASE_DRIVE . '/vendor/autoload.php';

        $fileId = uniqid();
        $tempFilePath = 'c:\\Temp\\' . $fileId . '.html';

        file_put_contents(
            $tempFilePath,
            $htmlBody
        );

        $meetingDate = new DateTime($meetingDate);

        $meetingDateDmy = $meetingDate->format('d-m-Y');
        $path = $reviewMeetingFolderPath . '/Agenda ' . $meetingDateDmy;
        $filePath = $path . '.pdf';

        $descriptors = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'a'),
        );

        $command = "c: && cd \"C:\\Program Files\\wkhtmltopdf\\bin\" && wkhtmltopdf $tempFilePath \"$filePath\"";
        $process = proc_open(
            $command,
            $descriptors,
            $pipes
        );
        $_error = null;
        if (is_resource($process)) {
            stream_get_contents($pipes[1]);
            $_stdErr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $_exitCode = proc_close($process);

            if ($_exitCode !== 0) {
                $_error = $_stdErr ? $_stdErr : "Failed without error message: $command";
            }
        }
        if ($_error) {
            throw new Exception(json_encode($_error));
        } else {
//            unlink($tempFilePath);
            return true;
        }
    }

    /**
     * Create a PDF file of customer profit figures and save to documentation
     * folder
     * @param $customerID
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @param $meetingDate
     * @return false|mixed|string|string[]|null
     * @throws Exception
     */
    public function generateSalesPdf($customerID,
                                     DateTimeInterface $startDate,
                                     DateTimeInterface $endDate,
                                     $meetingDate
    )
    {
        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            $customerID,
            $dsCustomer
        );

        $buCustomerAnalysisReport = new BUCustomerAnalysisReport($this);

        $documentFolderPath = $buCustomer->getCustomerFolderPath($customerID);

        $reviewMeetingFolderPath = $documentFolderPath . '/Review Meetings';

        $template = new Template (
            $GLOBALS ["cfg"] ["path_templates"],
            "remove"
        );

        $template->set_file(
            'page',
            'CustomerReviewMeetingSalesDocument.inc.html'
        );
        $dsSearchForm = new DSForm($this);
        $this->initialiseSearchForm($dsSearchForm);

        $dsSearchForm->setValue(
            self::searchFormCustomerID,
            $customerID
        );
        $dsSearchForm->setValue(
            self::searchFormStartYearMonth,
            $startDate->format('m/Y')
        );
        $dsSearchForm->setValue(
            self::searchFormEndYearMonth,
            $endDate->format('m/Y')
        );

        $results = $buCustomerAnalysisReport->getResults($dsSearchForm);

        $template->set_block(
            'page',
            'contractsBlock',
            'contracts'
        );
        $totalSales = 0;
        $totalCost = 0;
        $totalLabour = 0;
        $totalLabourHours = 0;


        foreach ($results as $contractName => $row) {

            if ($row['profit'] <= 0) {
                $profitAlertClass = 'profitAlert';
            } else {
                $profitAlertClass = '';
            }

            $template->set_var(
                array(
                    'contract'         => $contractName,
                    'sales'            => number_format(
                        $row['sales'],
                        2
                    ),
                    'cost'             => number_format(
                        $row['cost'],
                        2
                    ),
                    'labour'           => number_format(
                        $row['labourCost'],
                        2
                    ),
                    'profit'           => number_format(
                        $row['profit'],
                        2
                    ),
                    'profitPercent'    => $row['profitPercent'],
                    'labourHours'      => $row['labourHours'],
                    'profitAlertClass' => $profitAlertClass,
                    'directDebit'      => $row['directDebit'] ? 'yes' : 'no'
                )
            );
            $template->parse(
                'contracts',
                'contractsBlock',
                true
            );

            $totalSales += $row['sales'];
            $totalCost += $row['cost'];
            $totalLabour += $row['labourCost'];
            $totalLabourHours += $row['labourHours'];
        }
        $template->set_var(
            array(
                'customerName'       => $dsCustomer->getValue(DBECustomer::name),
                'siteURL'            => SITE_URL,
                'startYearMonth'     => $startDate->format('Y-m'),
                'meetingDate'        => $meetingDate,
                'endYearMonth'       => $endDate->format('Y-m'),
                'totalSales'         => number_format(
                    $totalSales,
                    2
                ),
                'totalCost'          => number_format(
                    $totalCost,
                    2
                ),
                'totalLabour'        => number_format(
                    $totalLabour,
                    2

                ),
                'totalProfit'        => number_format(
                    $totalSales - $totalCost - $totalLabour,
                    2
                ),
                'totalProfitPercent' => number_format(
                    100 - (($totalCost + $totalLabour) / $totalSales) * 100,
                    2
                ),
                'totalLabourHours'   => number_format(
                    $totalLabourHours,
                    2
                ),
                "waterMarkURL"       => SITE_URL . '/images/CNC_watermarkActualSize.png'
            )
        );
        /*
        renewals
        */
        $buRenewal = new BURenewal($this);

        $items = $buRenewal->getRenewalsAndExternalItemsByCustomer(
            $customerID,
            new Controller(
                null, $nothing, $nothing, $nothing, $nothing
            ),
            true
        );

        $lastItemTypeDescription = false;

        $template->set_block(
            'page',
            'itemBlock',
            'items'
        );

        foreach ($items as $key => $item) {
            $itemTypeDescription[$key] = $item['itemTypeDescription'];
        }
        array_multisort(
            $itemTypeDescription,
            SORT_ASC,
            $items
        );


        $totalCostPrice = 0;
        $totalSalePrice = 0;

        foreach ($items as $item) {

            if ($item['itemTypeDescription'] != $lastItemTypeDescription) {
                $itemTypeHeader = '<tr><td colspan="8"><h3>' . $item['itemTypeDescription'] . '</h3></td></tr>';
            } else {
                $itemTypeHeader = '';
            }

            $template->set_var(
                array(
                    'itemTypeHeader' => $itemTypeHeader
                )
            );

            $lastItemTypeDescription = $item['itemTypeDescription'];

            $coveredItemsString = '';

            if (isset($item['coveredItems']) && is_array($item['coveredItems']) && count($item['coveredItems']) > 0) {

                foreach ($item['coveredItems'] as $coveredItem) {

                    $coveredItemsString .= '<br/>' . $coveredItem;
                    $template->set_var(
                        array(
                            'coveredItemsString' => $coveredItemsString
                        )
                    );
                }
            }

            if (is_null($item['customerItemID'])) {
                $itemClass = 'externalItem';
                $salePrice = '';
                $costPrice = '';
            } else {
                $itemClass = '';

                $salePrice = Controller::formatNumber($item['salePrice']);

                $costPrice = Controller::formatNumber($item['costPrice']);

                $totalCostPrice += $item['costPrice'];

                $totalSalePrice += $item['salePrice'];
            }


            $template->set_var(
                array(
                    'notes'               => $item['notes'],
                    'description'         => Controller::htmlDisplayText($item['description']),
                    'itemTypeDescription' => Controller::htmlDisplayText($item['itemTypeDescription']),
                    'expiryDate'          => Controller::htmlDisplayText($item['expiryDate']),
                    'salePrice'           => $salePrice,
                    'costPrice'           => $costPrice,
                    'customerItemID'      => $item['customerItemID'],
                    'coveredItemsString'  => $coveredItemsString,
                    'itemClass'           => $itemClass,
                    'directDebit'         => $item['directDebit'] ? 'yes' : 'no'
                )
            );

            $template->parse(
                'items',
                'itemBlock',
                true
            );
        }

        $template->set_var(
            array(
                'totalSalePrice' => Controller::formatNumber($totalSalePrice),
                'totalCostPrice' => Controller::formatNumber($totalCostPrice)
            )
        );

        /*
        end renewals
        */

        $template->parse(
            'output',
            'page',
            true
        );

        $htmlPage = $template->get_var('output');

        @mkdir(
            $reviewMeetingFolderPath,
            '0777',
            true
        );  // ensure folder exists

        require_once BASE_DRIVE . '/vendor/autoload.php';

        $options = new Options();
        $options->set(
            'isRemoteEnabled',
            true
        );
        $dompdf = new Dompdf($options);

        $dompdf->setPaper(
            'A4',
            'portrait'
        );

        $dompdf->setBasePath(BASE_DRIVE . '/htdocs');   // so we can get the images and css

        $htmlPage = mb_convert_encoding(
            $htmlPage,
            'HTML-ENTITIES',
            'UTF-8'
        );

        $dompdf->loadHtml($htmlPage);

        $dompdf->render();

        $meetingDateDmy = substr(
                $meetingDate,
                8,
                2
            ) . '-' . substr(
                $meetingDate,
                5,
                2
            ) . '-' . substr(
                $meetingDate,
                0,
                4
            );

        $dompdf->add_info(
            'Title',
            'Renewal Report ' . $meetingDateDmy
        );

        $dompdf->add_info(
            'Author',
            'CNC Ltd'
        );

        $dompdf->add_info(
            'Subject',
            'Renewal Report'
        );

        $pdfString = $dompdf->output();

        $meetingDate = new DateTime($meetingDate);

        $meetingDateDmy = $meetingDate->format('d-m-Y');

        $filePath = $reviewMeetingFolderPath . '/Renewal Report ' . $meetingDateDmy . '.pdf';

        $handle = fopen(
            $filePath,
            'w'
        );

        fwrite(
            $handle,
            $pdfString
        );

        $this->pdfEncrypt(
            $filePath,
            $filePath,
            'RenewalOwner2018',
            'CNCShoreham2018'
        );

        return $htmlPage;
    }

    public function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);

        $checkMonthYear = function ($value) {
            if (!$value) {
                return false;
            }

            return !!DateTime::createFromFormat(
                'm/Y',
                $value
            );
        };

        $dsData->addColumn(
            self::searchFormCustomerID,
            DA_STRING,
            DA_NOT_NULL
        );
        $dsData->addColumn(
            self::searchFormStartYearMonth,
            DA_STRING,
            DA_NOT_NULL,
            $checkMonthYear
        );
        $dsData->addColumn(
            self::searchFormEndYearMonth,
            DA_STRING,
            DA_NOT_NULL,
            $checkMonthYear
        );
        $dsData->addColumn(
            self::searchFormMeetingDate,
            DA_DATE,
            DA_NOT_NULL
        );
    }

    /**
     * @param $origFile
     * @param $destFile
     * @param null $owner_password
     * @param null $user_password
     * @param array $permissions
     * @return mixed
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    function pdfEncrypt($origFile,
                        $destFile,
                        $owner_password = null,
                        $user_password = null,
                        $permissions = ['print']
    )
    {
        $pdf = new FpdiProtection();
        $pagecount = $pdf->setSourceFile($origFile);
        // copy all pages from the old unprotected pdf in the new one
        for ($loop = 1; $loop <= $pagecount; $loop++) {
            $tplidx = $pdf->importPage($loop);
            $pdf->addPage();
            $pdf->useTemplate($tplidx);
        }
        // Allow for array('print', 'modify', 'copy', 'annot-forms');
        $pdf->SetProtection(
            $permissions,
            $user_password,
            $owner_password
        );
        $pdf->Output(
            $destFile,
            'F'
        ); // F write, D download
        return $destFile;
    }

    /**
     * @param $customerID
     * @param $meetingDate
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     * @throws Exception
     */
    public function generateMeetingNotes($customerID,
                                         $meetingDate
    )
    {
        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            $customerID,
            $dsCustomer
        );


        Settings::setOutputEscapingEnabled(true);
        $templateProcessor = new TemplateProcessor(
            PDF_RESOURCE_DIR . '/Client Meeting Notes Template.docx'
        );


        $buContact = new BUContact($this);
        $reviewContactsDS = new DataSet($this);
        $buContact->getReviewContacts(
            $customerID,
            $reviewContactsDS
        );

        $reviewContactsString = "";

        while ($reviewContactsDS->fetchNext()) {
            if ($reviewContactsString) {
                $reviewContactsString .= "/ ";
            }

            $reviewContactsString .= $reviewContactsDS->getValue(
                    DBEContact::firstName
                ) . " " . $reviewContactsDS->getValue(DBEContact::lastName);
        }

        $clientName = $dsCustomer->getValue(DBECustomer::name);

        if ($reviewContactsString) {
            $clientName .= ' - ' . $reviewContactsString;
        }

        $templateProcessor->setValue(
            self::templateClientName,
            $clientName
        );
        $meetingDate = new DateTime($meetingDate);
        $templateProcessor->setValue(
            self::templateReviewMeetingDate,
            $meetingDate->format('d/m/Y')
        );
        $uniqueId = uniqid(
            null,
            true
        );
        $docFile = $uniqueId . ".docx";
        $pdfFile = $uniqueId . '.pdf';
        $templateProcessor->saveAs($docFile);
        shell_exec(
            '"c:\Program Files\LibreOffice\program\soffice.exe" --headless --convert-to pdf ' . $docFile
        );

        if (!file_exists($pdfFile)) {
            throw new Exception('Failed to generate Clients Notes PDF');
        }

        unlink($docFile);
        $documentFolderPath = $buCustomer->getCustomerFolderPath($customerID);

        $reviewMeetingFolderPath = $documentFolderPath . '/Review Meetings';

        rename(
            $pdfFile,
            $reviewMeetingFolderPath . '/CIF ' . $meetingDate->format('d-m-Y') . '.pdf'
        );

    }
}

?>