<?php

namespace CNCLTD\DUOUsersReportGenerator;

use CNCLTD\DUOApi\Users\Users\Users\Users\User;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DUOClientReportGenerator
{

    /**
     * DUOUsersReportGenerator constructor.
     */
    public function __construct() { }

    /**
     * @param $users User[]
     * @throws Exception
     */
    public function getReportSpreadsheet($users)
    {

        // we have to create an array
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ["firstName", "lastName", "email", "status", "lastLogin"];

        usort($users,function(User $a, User $b) {})

        $sheet->fromArray(array_merge(["Customer Name"], $this->dataHeaders));

    }
}