<?php

require_once($cfg['path_ct'] . '/CTCNC.inc.php');

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
                $DBEOSSupportDates->setLogSQLOn();
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

                $DBEOSSupportDates->setValue(
                    DBEOSSupportDates::domain,
                    $_REQUEST['domain']
                );
                $DBEOSSupportDates->setValue(
                    DBEOSSupportDates::customerID,
                    $_REQUEST['customerID']
                );

                $DBEOSSupportDates->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $DBEOSSupportDates = new DBEOSSupportDates($this);

                $DBEOSSupportDates->setValue(DBEOSSupportDates::name, $_REQUEST['name']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::version, $_REQUEST['version']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::build, $_REQUEST['build']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::subBuild, $_REQUEST['subBuild']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::availabilityDate, $_REQUEST['availabilityDate']);
                $DBEOSSupportDates->setValue(DBEOSSupportDates::endOfLifeDate, $_REQUEST['endOfLifeDate']);
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
                    $data[] = [
                        "id"               => $DBEOSSupportDates->getValue(DBEOSSupportDates::id),
                        "name"             => $DBEOSSupportDates->getValue($DBEOSSupportDates::name),
                        "version"          => $DBEOSSupportDates->getValue($DBEOSSupportDates::version),
                        "build"            => $DBEOSSupportDates->getValue($DBEOSSupportDates::build),
                        "subBuild"         => $DBEOSSupportDates->getValue($DBEOSSupportDates::subBuild),
                        "availabilityDate" => $DBEOSSupportDates->getValue($DBEOSSupportDates::availabilityDate),
                        "endOfLifeDate"    => $DBEOSSupportDates->getValue($DBEOSSupportDates::endOfLifeDate),
                    ];
                }
                echo json_encode(
                    $data,
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