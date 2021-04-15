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
        $headers = ["firstName", "lastName", "email", "status", "lastLogin"];
        $sheet->fromArray($headers);
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
        $this->applyAutoFilterAndAutoSize($sheet);
        $this->formatDateColumn($sheet, 5);
    }


    public function getReportData(array $getUsers, array $authenticationLogs): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $usersSheet = $spreadsheet->getActiveSheet();
        $this->fillUsersSheet($usersSheet, $getUsers);
        $this->fillAuthenticationLogsSheet($spreadsheet->createSheet(), $authenticationLogs);
        return $spreadsheet;
    }

    private function fillAuthenticationLogsSheet(Worksheet $sheet, array $authenticationLogs)
    {
        $sheet->setTitle('Authentication Logs');
        // we have to create an array
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
        $sheet->fromArray($headers);
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
        $this->applyAutoFilterAndAutoSize($sheet);
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
        $styles = $sheet->getStyle("{$columnLetter}:{$columnLetter}");
        $styles->getNumberFormat()->setFormatCode(
            NumberFormat::FORMAT_DATE_DDMMYYYY . " hh:mm:ss"
        );
    }
}