<?php
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEOSSupportDates.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');

class CTOSSupportDates extends CTCNC
{
    var $buActivity;
    /** @var DBEHeader */
    private $dsSystemHeader;

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
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(220);
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($this->dsSystemHeader);

    }


    function delete()
    {
        $this->defaultAction();
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch (@$_REQUEST['action']) {
            case 'delete':
                if (!isset($_REQUEST['id'])) {
                    http_response_code(400);
                    throw new Exception('ID is missing');
                }
                $DBEOSSupportDates = new DBEOSSupportDates($this);
                $DBEOSSupportDates->getRow($_REQUEST['id']);
                if (!$DBEOSSupportDates->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $DBEOSSupportDates->deleteRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'update':
                if (!isset($_REQUEST['id'])) {
                    throw new Exception('ID is missing');
                }
                $DBEOSSupportDates = new DBEOSSupportDates($this);
                $DBEOSSupportDates->getRow($_REQUEST['id']);
                if (!$DBEOSSupportDates->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $availabilityDateString = $_REQUEST['availabilityDate'];
                $endOfLifeDateString    = $_REQUEST['endOfLifeDate'];
                $DBEOSSupportDates->setValue(DBEOSSupportDates::name, $_REQUEST['name']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::version, $_REQUEST['version']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::availabilityDate, $availabilityDateString);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::endOfLifeDate, $endOfLifeDateString);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::isServer, $this->getParam('isServer') === 'on');
                $DBEOSSupportDates->setValue(DBEOSSupportDates::friendlyName, $_REQUEST['friendlyName']);
                $DBEOSSupportDates->setValue(
                    DBEOSSupportDates::thirdPartyPatchingCapable,
                    $this->getParam('thirdPartyPatchingCapable') === 'on'
                );
                $DBEOSSupportDates->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $DBEOSSupportDates      = new DBEOSSupportDates($this);
                $availabilityDateString = $_REQUEST['availabilityDate'];
                if ($availabilityDateString) {
                    $availabilityDate = DateTime::createFromFormat(DATE_MYSQL_DATE, $availabilityDateString);
                    if (!$availabilityDate) {
                        throw new Exception('Date format is wrong');
                    }
                    $availabilityDateString = $availabilityDate->format(DATE_MYSQL_DATE);
                }
                $endOfLifeDateString = $_REQUEST['endOfLifeDate'];
                if ($endOfLifeDateString) {
                    $endOfLifeDate = DateTime::createFromFormat(DATE_MYSQL_DATE, $endOfLifeDateString);
                    if (!$endOfLifeDate) {
                        throw new Exception('Date format is wrong');
                    }
                    $endOfLifeDateString = $endOfLifeDate->format(DATE_MYSQL_DATE);
                }
                $DBEOSSupportDates->setValue(DBEOSSupportDates::name, $_REQUEST['name']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::version, $_REQUEST['version']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::availabilityDate, $availabilityDateString);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::endOfLifeDate, $endOfLifeDateString);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::isServer, $this->getParam('isServer') === 'on');
                $DBEOSSupportDates->setValue(
                    DBEOSSupportDates::thirdPartyPatchingCapable,
                    $this->getParam('thirdPartyPatchingCapable') === 'on'
                );
                $DBEOSSupportDates->setValue(DBEOSSupportDates::friendlyName, $_REQUEST['friendlyName']);
                $DBEOSSupportDates->insertRow();
                echo json_encode(
                    [
                        "id"                             => $DBEOSSupportDates->getValue(DBEOSSupportDates::id),
                        "name"                           => $DBEOSSupportDates->getValue(DBEOSSupportDates::name),
                        "version"                        => $DBEOSSupportDates->getValue(DBEOSSupportDates::version),
                        "availabilityDate"               => $DBEOSSupportDates->getValue(
                            DBEOSSupportDates::availabilityDate
                        ),
                        "endOfLifeDate"                  => $DBEOSSupportDates->getValue(
                            DBEOSSupportDates::endOfLifeDate
                        ),
                        "threshold"                      => $this->dsSystemHeader->getValue(
                            DBEHeader::OSSupportDatesThresholdDays
                        ),
                        "isServer"                       => $DBEOSSupportDates->getValue(DBEOSSupportDates::isServer),
                        "friendlyName"                   => $DBEOSSupportDates->getValue(
                            DBEOSSupportDates::friendlyName
                        ),
                        "thirdPartyPatchingCapable" => $DBEOSSupportDates->getValue(
                            DBEOSSupportDates::thirdPartyPatchingCapable
                        ),
                    ],
                    JSON_NUMERIC_CHECK
                );
                break;
            case 'getData':
                $DBEOSSupportDates = new DBEOSSupportDates($this);
                $DBEOSSupportDates->getRows();
                $data = [];
                while ($DBEOSSupportDates->fetchNext()) {


                    $data[] = [
                        "id"                        => $DBEOSSupportDates->getValue(DBEOSSupportDates::id),
                        "name"                      => $DBEOSSupportDates->getValue(DBEOSSupportDates::name),
                        "version"                   => $DBEOSSupportDates->getValue(DBEOSSupportDates::version),
                        "availabilityDate"          => $DBEOSSupportDates->getValue(
                            DBEOSSupportDates::availabilityDate
                        ),
                        "endOfLifeDate"             => $DBEOSSupportDates->getValue(DBEOSSupportDates::endOfLifeDate),
                        "threshold"                 => $this->dsSystemHeader->getValue(
                            DBEHeader::OSSupportDatesThresholdDays
                        ),
                        "isServer"                  => $DBEOSSupportDates->getValue(DBEOSSupportDates::isServer),
                        "friendlyName"              => $DBEOSSupportDates->getValue(DBEOSSupportDates::friendlyName),
                        "thirdPartyPatchingCapable" => $DBEOSSupportDates->getValue(
                            DBEOSSupportDates::thirdPartyPatchingCapable
                        ),
                    ];
                }
                echo json_encode(
                    [
                        "draw"            => 1,
                        "recordsTotal"    => count($data),
                        "recordsFiltered" => count($data),
                        "data"            => $data
                    ],
                    JSON_NUMERIC_CHECK
                );
                break;
            case 'getMissingRowsData':
                $DBEOSSupportDates = new DBEOSSupportDates($this);
                $DBEOSSupportDates->getRows();
                $data = [];
                while ($DBEOSSupportDates->fetchNext()) {
                    $data[] = [
                        "name"    => $DBEOSSupportDates->getValue(DBEOSSupportDates::name),
                        "version" => $DBEOSSupportDates->getValue(DBEOSSupportDates::version)
                    ];
                }
                $dsn          = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
                $options      = [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                ];
                $labtechDB    = new PDO(
                    $dsn, LABTECH_DB_USERNAME, LABTECH_DB_PASSWORD, $options
                );
                $labtechQuery = $labtechDB->query(
                    'SELECT computers.`OS` as name, computers.`Version` as version  FROM computers GROUP BY OS, VERSION '
                );
                $labtechData  = $labtechQuery->fetchAll(PDO::FETCH_ASSOC);
                $missingRows  = [];
                foreach ($labtechData as $labtechDatum) {
                    // ignore those rows from labtech that don't have a correct version number, also extract only the version number
                    if (!preg_match('/\d+\.\d+\.\d+/', $labtechDatum['version'], $matches)) {
                        continue;
                    }
                    $possibleMissingRow = [
                        "name"    => $labtechDatum['name'],
                        "version" => $matches[0]
                    ];
                    if ($this->arrayFind($data, $possibleMissingRow)) {
                        // we have found the guy....nothing to look at here move along
                        continue;
                    }
                    $missingRows[] = $possibleMissingRow;
                }
                echo json_encode(
                    [
                        "draw"            => 1,
                        "recordsTotal"    => count($missingRows),
                        "recordsFiltered" => count($missingRows),
                        "data"            => $missingRows
                    ],
                    JSON_NUMERIC_CHECK
                );
                break;
            case 'displayForm':
            default:
                $this->displayForm();
                break;
        }
    }

    private function arrayFind($cncData, $osVersionToSearchFor)
    {
        foreach ($cncData as $datum) {
            if ($datum['name'] == $osVersionToSearchFor['name'] && $datum['version'] == $osVersionToSearchFor['version']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Export expenses that have not previously been exported
     * @access private
     */
    function displayForm()
    {
        $this->setPageTitle('OS Support Dates');
        $this->setTemplateFiles(
            'OSSupportDates',
            'OSSupportDates'
        );
        $this->template->parse(
            'CONTENTS',
            'OSSupportDates',
            true
        );
        $URLDeleteItem        = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            ['action' => 'delete']
        );
        $URLUpdateItem        = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            ['action' => 'update']
        );
        $URLCreateItem        = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            ['action' => 'create']
        );
        $URLGetData           = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            ['action' => 'getData']
        );
        $URLGetMissingRowData = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            ['action' => 'getMissingRowsData']
        );
        $this->template->setVar(
            [
                "URLDeleteItem"         => $URLDeleteItem,
                "URLUpdateItem"         => $URLUpdateItem,
                "URLAddItem"            => $URLCreateItem,
                "URLGetData"            => $URLGetData,
                "URLGetMissingRowsData" => $URLGetMissingRowData
            ]
        );
        $this->parsePage();
    }

    function update()
    {
        $this->defaultAction();
    }
}