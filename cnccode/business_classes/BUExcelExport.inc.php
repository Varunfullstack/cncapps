<?php /**
 * Invoice business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");

class BUExcelExport extends Business
{
    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    /**
     * Generate Sage export files
     * @parameter Integer $month month for sage data
     * @parameter Integer $year year for sage data
     * @access public
     */
    function generateFile($year, $month)
    {
        GLOBAL $db;
        $this->setMethodName('generateFile');

        $sql =
            "SELECT  
        DAYOFMONTH(inh_date_printed) AS 'printedDay',
        inh_invno,
        cus_name,
        SUM(inl_qty * inl_cost_price) AS cost,
        SUM(inl_qty * inl_unit_price) AS sale
      FROM invline
      INNER JOIN invhead ON invhead.inh_invno = invline.inl_invno
      INNER JOIN customer ON customer.cus_custno = invhead.inh_custno
      WHERE
        inh_date_printed_yearmonth = '$year$month'
        AND inl_line_type = 'I'
      GROUP BY inl_invno
      ORDER BY inh_date_printed, inh_invno";

        $db->query($sql);

        $monthArray =
            array(
                1 => 'jan',
                2 => 'feb',
                3 => 'mar',
                4 => 'apr',
                5 => 'may',
                6 => 'jun',
                7 => 'jul',
                8 => 'aug',
                9 => 'sep',
                10 => 'oct',
                11 => 'nov',
                12 => 'dec'
            );
        if ($db->next_record()) {
            $fileName = SAGE_EXPORT_DIR . '/' . $monthArray[(int)$month] . $year . '.csv';
            $fileURL = 'export/' . $monthArray[(int)$month] . $year . '.csv';
            $fileHandle = fopen($fileName, 'wb');
            if (!$fileHandle) {
                $this->raiseError("Unable to open file " . $fileName);
            }
            do {
                fwrite(
                    $fileHandle,
                    "\"" . $db->Record['printedDay'] . "\"," .
                    "\"" . $db->Record['inh_invno'] . "\"," .
                    "\"" . addslashes($db->Record['cus_name']) . "\"," .
                    "\"" . $db->Record['cost'] . "\"," .
                    "\"" . $db->Record['sale'] . "\"" .
                    "\r\n"
                );
            } while ($db->next_record());
            fclose($fileHandle);
            return $fileURL;
        } else {
            return FALSE;
        }
    }
}// End of class
?>