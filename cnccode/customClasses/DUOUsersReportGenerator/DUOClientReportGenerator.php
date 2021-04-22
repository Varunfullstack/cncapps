<?php

namespace CNCLTD\DUOUsersReportGenerator;

use CNCLTD\DUOApi\AuthLog\AuthLog;
use CNCLTD\DUOApi\Users\User;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DUOClientReportGenerator
{

    /**
     * DUOUsersReportGenerator constructor.
     */
    public function __construct() { }

    /**
     * @param Worksheet $sheet
     * @param $users User[]
     */
    private function fillUsersSheet(Worksheet $sheet, array $users)
    {
        $sheet->setTitle('Users');
        // we have to create an array
        $headers = ["First Name", "Last Name", "Email", "Status", "Last Login"];
        $this->addHeaders($sheet, $headers);
        usort(
            $users,
            function (User $a, User $b) {
                return strcmp($b->lastLogin(), $a->lastLogin());
            }
        );
        $rawData = array_map(
            function (User $user) {
                return [
                    $user->firstName(),
                    $user->lastName(),
                    $user->email(),
                    $user->status(),
                    $this->getFormattedTimeFromTimeStamp($user->lastLogin())
                ];
            },
            $users
        );
        $sheet->fromArray($rawData, null, 'A2');
        $this->applyAutoFilterAndAlignment($sheet);
        $this->formatDateColumn($sheet, 5);
    }


    public function getReportData(array $getUsers, array $authenticationLogs): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $this->setFont($spreadsheet);
        $this->fillUsersSheet($spreadsheet->getActiveSheet(), $getUsers);
        $this->fillAuthenticationLogsSheet($spreadsheet->createSheet(), $authenticationLogs);
        return $spreadsheet;
    }

    private function fillAuthenticationLogsSheet(Worksheet $sheet, array $authenticationLogs)
    {
        $sheet->setTitle('Authentication Logs');
        $headers = [
            "Date & Time",
            "Username",
            "Email",
            "Application",
            "Result",
            "Access IP",
            "Access City",
            "Access State",
            "Access Country"
        ];
        $this->addHeaders($sheet, $headers);
        usort(
            $authenticationLogs,
            function (AuthLog $a, AuthLog $b) {
                return $b->timestamp() - $a->timestamp();
            }
        );
        $rawData = array_map(
            function (AuthLog $authLog) {
                return [
                    $this->getFormattedTimeFromTimeStamp($authLog->timestamp()),
                    $authLog->user()->name(),
                    $authLog->email(),
                    $authLog->application()->name(),
                    $authLog->result(),
                    $authLog->accessDevice()->ip(),
                    $authLog->accessDevice()->location()->city(),
                    $authLog->accessDevice()->location()->state(),
                    $authLog->accessDevice()->location()->country(),
                ];
            },
            $authenticationLogs
        );
        $sheet->fromArray($rawData, null, 'A2');
        $this->applyAutoFilterAndAlignment($sheet);
        $this->formatDateColumn($sheet, 1);
    }

    /**
     * @param Worksheet $sheet
     */
    private function applyAutoFilterAndAutoSize(Worksheet $sheet): void
    {
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function getFormattedTimeFromTimeStamp($timestamp): string
    {
        return Date::PHPToExcel($timestamp);
    }

    private function formatDateColumn(Worksheet $sheet, int $columnIndex)
    {
        $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
        $styles       = $sheet->getStyle("{$columnLetter}:{$columnLetter}");
        $styles->getNumberFormat()->setFormatCode(
            NumberFormat::FORMAT_DATE_DDMMYYYY . " hh:mm:ss"
        );
    }

    private function formatCenterAlign(Worksheet $sheet)
    {
        $sheet->getStyle($sheet->calculateWorksheetDataDimension())->getAlignment()->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        );
    }

    /**
     * @param Worksheet $sheet
     * @param $headers
     */
    private function addHeaders(Worksheet $sheet, $headers): void
    {
        $sheet->fromArray($headers);
        $sheet->getStyle("A1:{$sheet->getHighestColumn()}1")->getFont()->setBold(true);
    }

    /**
     * @param Worksheet $sheet
     */
    private function applyAutoFilterAndAlignment(Worksheet $sheet): void
    {
        $this->applyAutoFilterAndAutoSize($sheet);
        $this->formatCenterAlign($sheet);
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function setFont(Spreadsheet $spreadsheet): void
    {
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
    }
}