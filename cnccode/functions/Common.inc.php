<?php
/**
 * determine whether location is an internal stock location
 */
define('COMMON_MYSQL_DATETIME', 'Y-m-d H:i:s');
define('COMMON_MYSQL_DATE', 'Y-m-d');

function common_isAnInternalStockLocation($customerID)
{
    if (
        ($customerID == CONFIG_MAINT_STOCK_CUSTOMERID) |
        ($customerID == CONFIG_SALES_STOCK_CUSTOMERID) |
        ($customerID == CONFIG_ASSET_STOCK_CUSTOMERID) |
        ($customerID == CONFIG_OPERATING_STOCK_CUSTOMERID)
    ) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function common_isAnInternalStockSupplier($supplierID)
{
    if (
        ($supplierID == CONFIG_MAINT_STOCK_SUPPLIERID) |
        ($supplierID == CONFIG_SALES_STOCK_SUPPLIERID)
    ) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function common_convertDateDMYToYMD($dateDMY)
{
    if ($dateDMY != '') {
        $dateArray = explode('/', $dateDMY);
        return ($dateArray[2] . '-' . str_pad($dateArray[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dateArray[0], 2, '0', STR_PAD_LEFT));
    } else {
        return '';
    }
}

function common_dateFormat($timestamp, $format = 1)
{
    $timestamp = trim($timestamp);
    // only do something if a date is passed in
    if ($timestamp > 0):
        // check if date contains '/' char i.e. from user entered data dd/mm/yyyy
        if (strstr($timestamp, "/")):
            $parts = explode("/", $timestamp);
            // $parts[0] = day
            // $parts[1] = month
            // $parts[2] = year
            $timestamp = date("Y-m-d H:i:s", strtotime("$parts[2]-$parts[1]-$parts[0]"));
        endif;
        // convert to unix time stamp for easier formatting
        $timestamp = strtotime($timestamp);

        switch ($format):
            case 1:
                // 25/12/2003
                $rDate = date("d/m/Y", $timestamp);
                break;
            case 2:
                // 25/12/2003 12:00:00
                $rDate = date("d/m/Y H:i:s", $timestamp);
                break;
        endswitch;
        return $rDate;
    endif;
}

/*
Convert time in HH:MM to decimal
*/
function common_convertHHMMToDecimal($hhMM)
{
    $hours = substr($hhMM, 0, 2);
    $minutes = substr($hhMM, 3, 2);

    $minutesAsFraction = $minutes / 60;

    return common_numberFormat($hours + $minutesAsFraction);
}

function convertHHMMToMinutes($hhMM)
{
    $time = explode(':', $hhMM);
    return ($time[0] * 60.0 + $time[1] * 1.0);
}

if (!function_exists('str_split')) {
    function str_split($string, $split_length = 1)
    {
        $strlen = strlen($string);

        for ($i = 0; $i < $strlen; $i += $split_length) {
            $array[] = substr($string, $i, $split_length);
        }

        return $array;
    }
}

function common_convertDecimalToHHMM($decimalTime)
{

    $hoursInteger = floor($decimalTime);
    $hoursString = str_pad($hoursInteger, 2, '0', STR_PAD_LEFT);
    $minutesString = str_pad(floor(60 * ($decimalTime - $hoursInteger)), 2, '0', STR_PAD_LEFT);
    return $hoursString . ':' . $minutesString;

}

function common_inRange($value, $startRange, $endRange)
{
    if (($value > $endRange) OR ($value < $startRange)) {
        return FALSE;
    } else {
        return TRUE;
    }
}

function common_numberFormat($number, $fuzz = 0.00000000001)
{
    return sprintf("%.2f", (($number >= 0) ? ($number + $fuzz) : ($number - $fuzz)));
}

//////////////////////////////////////////////////////////////////////////////////////////
// convert a string to hex of ascii code - blocks email spiders
//////////////////////////////////////////////////////////////////////////////////////////
function strtohex($string, $prefix = "%")
{
    for ($i = 0; $i < strlen($string); $i++) {
        $return .= $prefix . dechex(ord($string[$i]));
    }
    return $return;
}

function common_getHTMLEmailFooter($senderName, $senderEmail)
{

    // do not apply a footer - CNC mail server does this now
    return false;
}

function common_roundUpToQuarter($value)
{
    $diff = $value - floor($value);

    if ($diff > 0) {
        if ($diff <= 0.25) {
            $value = floor($value) + 0.25;
        };
        if ($diff > 0.25 && $diff <= 0.5) {
            $value = floor($value) + 0.5;
        };
        if ($diff > 0.5 && $diff <= 0.75) {
            $value = floor($value) + 0.75;
        };
        if ($diff > 0.75) {
            $value = floor($value) + 1;
        };
    };

    return $value;
}

function common_dateDiffMins($startDate, $startTime)
{
    $startDate = explode('-', $startDate);
    $startTime = explode(':', $startTime);
    $mins = abs(bcdiv(time() - mktime($startTime[0], $startTime[1], 0, $startDate[1], $startDate[2], $startDate[0]), 60));
    return $mins;
}

function common_decodeQueryArray(&$queryArray)
{

    foreach ($queryArray as $key => $value) {
        $queryArray[$key] = urldecode($value);
    }

}

/**
 * return next working day excluding week days but not bank holidays
 *
 */
function common_getNextWorkingDay()
{
    $nextdays = array(strtotime('+1 day'), strtotime('+2 days'), strtotime('+3 days'));
    for ($i = 0; $i < count($nextdays); $i++) {
        $daynum = (int)date('w', $nextdays[$i]);
        if (($daynum > 0) && ($daynum < 6)) {
            $nextDate = $nextdays[$i];
            break;
        }
    }
    return date('d/m/Y', $nextDate);
}

/**
 * strip html tages
 *
 * @param mixed $description
 * @return string
 */
function common_stripEverything($description)
{
    $description = str_replace("\r\n", '', trim($description));
    $description = str_replace("\r", '', trim($description));
    $description = str_replace("\n", '', $description);
    $description = str_replace("\t", '', $description);
    $description = str_replace('<br />', "", $description);
    $description = str_replace('<br/>', "", $description);
    $description = str_replace('<BR/>', "", $description);
    $description = str_replace('<BR>', "", $description);
    $description = str_replace('<p>', "", $description);
    $description = str_replace('</p>', "", $description);
    $description = str_replace('<P>', "", $description);
    $description = str_replace('</P>', "", $description);
    $description = str_replace('&nbsp;', " ", $description);
    $description = str_replace('&quot;', "'", $description);
    $description = strip_tags($description);
    $description = trim($description);
    return $description;

}

function common_getUKBankHolidays($year)
{
    $utFirstJan = strtotime('1st jan ' . $year);

    $firstJanDay = date('N', $utFirstJan);

    if ($firstJanDay > 5) { // sat or sun
        $holidays[] = date('Y-m-d', strtotime('first monday of january ' . $year));
    } else {
        $holidays[] = date('Y-m-d', $utFirstJan);
    }

    $utEasterSunday = easter_date($year);
    $holidays[] = date('Y-m-d', strtotime('last friday', $utEasterSunday));
    $holidays[] = date('Y-m-d', strtotime('next monday', $utEasterSunday));
    $holidays[] = date('Y-m-d', strtotime('first monday of may ' . $year));

    /*
    Example of adhock holiday adding
    */
    /*
    if ( $year == '2012' ){       // jubilee 2012
      $holidays[] = '2012-06-04';
      $holidays[] = '2012-06-05';
    }
    */
    $holidays[] = date('Y-m-d', strtotime('last monday of may ' . $year));

    $holidays[] = date('Y-m-d', strtotime('last monday of august ' . $year));  // August bh

    $xmasDay = date(strtotime('25th december ' . $year));
    if (date('N', $xmasDay) == 5) { // falls on friday
        $holidays[] = date('Y-m-d', $xmasDay);
        $holidays[] = date('Y-m-d', strtotime('next monday', $xmasDay));
    }
    if (date('N', $xmasDay) > 5) { // falls on sat or sun
        $holidays[] = date('Y-m-d', strtotime('next monday', $xmasDay));
        $holidays[] = date('Y-m-d', strtotime('next tuesday', $xmasDay));
    }
    if (date('N', $xmasDay) < 5) { // falls on mon to thurs
        $holidays[] = date('Y-m-d', $xmasDay);
        $holidays[] = date('Y-m-d', strtotime('next day', $xmasDay));
    }

    return $holidays;

}

?>