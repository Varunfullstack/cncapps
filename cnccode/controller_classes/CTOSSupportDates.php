<?php

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEOSSupportDates.php');

class CTOSSupportDates extends CTCNC
{
    var $buActivity;

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
            "maintenance",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
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
                if ($availabilityDateString) {
                    $availabilityDate = DateTime::createFromFormat('d/m/Y', $availabilityDateString);
                    if (!$availabilityDate) {
                        throw new Exception('Date format is wrong');
                    }
                    $availabilityDateString = $availabilityDate->format('Y-m-d');
                }

                $endOfLifeDateString = $_REQUEST['endOfLifeDate'];
                if ($endOfLifeDateString) {
                    $endOfLifeDate = DateTime::createFromFormat('d/m/Y', $endOfLifeDateString);
                    if (!$endOfLifeDate) {
                        throw new Exception('Date format is wrong');
                    }
                    $endOfLifeDateString = $endOfLifeDate->format('Y-m-d');
                }


                $DBEOSSupportDates->setValue(DBEOSSupportDates::name, $_REQUEST['name']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::version, $_REQUEST['version']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::build, $_REQUEST['build']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::subBuild, $_REQUEST['subBuild']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::availabilityDate, $availabilityDateString);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::endOfLifeDate, $endOfLifeDateString);
                $DBEOSSupportDates->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $DBEOSSupportDates = new DBEOSSupportDates($this);

                $availabilityDateString = $_REQUEST['availabilityDate'];
                if ($availabilityDateString) {
                    $availabilityDate = DateTime::createFromFormat('d/m/Y', $availabilityDateString);
                    if (!$availabilityDate) {
                        throw new Exception('Date format is wrong');
                    }
                    $availabilityDateString = $availabilityDate->format('Y-m-d');
                }

                $endOfLifeDateString = $_REQUEST['endOfLifeDate'];
                if ($endOfLifeDateString) {
                    $endOfLifeDate = DateTime::createFromFormat('d/m/Y', $endOfLifeDateString);
                    if (!$endOfLifeDate) {
                        throw new Exception('Date format is wrong');
                    }
                    $endOfLifeDateString = $endOfLifeDate->format('Y-m-d');;
                }


                $DBEOSSupportDates->setValue(DBEOSSupportDates::name, $_REQUEST['name']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::version, $_REQUEST['version']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::build, $_REQUEST['build']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::subBuild, $_REQUEST['subBuild']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::availabilityDate, $availabilityDateString);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::endOfLifeDate, $endOfLifeDateString);
                $DBEOSSupportDates->insertRow();

                echo json_encode(
                    [
                        "id"               => $DBEOSSupportDates->getValue(DBEOSSupportDates::id),
                        "name"             => $DBEOSSupportDates->getValue($DBEOSSupportDates::name),
                        "version"          => $DBEOSSupportDates->getValue($DBEOSSupportDates::version),
                        "build"            => $DBEOSSupportDates->getValue($DBEOSSupportDates::build),
                        "subBuild"         => $DBEOSSupportDates->getValue($DBEOSSupportDates::subBuild),
                        "availabilityDate" => $DBEOSSupportDates->getValue($DBEOSSupportDates::availabilityDate),
                        "endOfLifeDate"    => $DBEOSSupportDates->getValue($DBEOSSupportDates::endOfLifeDate),
                    ],
                    JSON_NUMERIC_CHECK
                );

                break;
            case 'getData':
                $DBEOSSupportDates = new DBEOSSupportDates($this);

                $DBEOSSupportDates->getRows();


                $data = [];
                while ($DBEOSSupportDates->fetchNext()) {
                    $availabilityDateString = $DBEOSSupportDates->getValue(DBEOSSupportDates::availabilityDate);
                    if ($availabilityDateString) {
                        $availabilityDate = DateTime::createFromFormat('Y-m-d', $availabilityDateString);
                        if (!$availabilityDate) {
                            throw new Exception('Date format is wrong');
                        }
                        $availabilityDateString = $availabilityDate->format('d/m/Y');
                    }

                    $endOfLifeDateString = $DBEOSSupportDates->getValue(DBEOSSupportDates::endOfLifeDate);
                    if ($endOfLifeDateString) {
                        $endOfLifeDate = DateTime::createFromFormat('Y-m-d', $endOfLifeDateString);
                        if (!$endOfLifeDate) {
                            throw new Exception('Date format is wrong');
                        }

                        $endOfLifeDateString = $endOfLifeDate->format('d/m/Y');
                    }

                    $data[] = [
                        "id"               => $DBEOSSupportDates->getValue(DBEOSSupportDates::id),
                        "name"             => $DBEOSSupportDates->getValue(DBEOSSupportDates::name),
                        "version"          => $DBEOSSupportDates->getValue(DBEOSSupportDates::version),
                        "build"            => $DBEOSSupportDates->getValue(DBEOSSupportDates::build),
                        "subBuild"         => $DBEOSSupportDates->getValue(DBEOSSupportDates::subBuild),
                        "availabilityDate" => $availabilityDateString,
                        "endOfLifeDate"    => $endOfLifeDateString,
                    ];
                }
                echo json_encode(
                    [
                        "draw"            => 1,
                        "recordsTotal"    => 57,
                        "recordsFiltered" => 57,
                        "data"            => $data
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

        $URLDeleteItem = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'delete'
            ]
        );

        $URLUpdateItem = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'update'
            ]
        );

        $URLCreateItem = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'create'
            ]
        );

        $URLGetData = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'getData'
            ]
        );
        $this->template->setVar(
            [
                "URLDeleteItem" => $URLDeleteItem,
                "URLUpdateItem" => $URLUpdateItem,
                "URLAddItem"    => $URLCreateItem,
                "URLGetData"    => $URLGetData
            ]
        );

        $this->parsePage();
    }
}