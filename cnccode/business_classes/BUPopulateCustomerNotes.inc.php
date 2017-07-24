<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUPopulateCustomerNotes extends Business
{

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function update()
    {
        $trim_list = array('-', ' ', '.');

        $db = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        //$dbUpdate = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $dbUpdate = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);
        mysqli_select_db($dbUpdate, DB_NAME);

        $sql =
            "SELECT cus_custno, 
        comments
        FROM customer
        WHERE comments > ''
        ";

        $result = $db->query($sql);

        while ($row = $result->fetch_object()) {

            $seconds = 1;

            $custno = $row->cus_custno;
            $comments = $row->comments;

            $date_pattern =
                '(((0?[1-9]|[12]\d|3[01])[.-/](0?[13578]|1[02])[.-/]((1[6-9]|[2-9]\d)?\d{2}))|((0?[1-9]|[12]\d|30)[.-/](0?[13456789]|1[012])[.-/]((1[6-9]|[2-9]\d)?\d{2}))|((0?[1-9]|1\d|2[0-8])[.-/]0?2[.-/]((1[6-9]|[2-9]\d)?\d{2}))|(29[.-/]0?2[.-/]((1[6-9]|[2-9]\d)?(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)|00)))';

            $comments_array = preg_split(
                $date_pattern,
                $comments,
                0,                                                  // no limit on number
                PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE      // no empty strings | get delimited
            );
            while ($element = next($comments_array)) {


                /*
                if this is a date process next 4 elements dd/mm/yy, dd, mm, yy
                */
                if (preg_match($date_pattern, $element)) {

                    $day = next($comments_array);
                    $month = next($comments_array);
                    $year = next($comments_array);

                    if (strlen($year) == 2) {
                        if ($year > 50) {
                            $year = 1900 + $year;
                        } else {
                            $year = 2000 + $year;
                        }
                    }

                    if (!$year) {
                        $year = '2010';
                    }

                    $details = trim(next($comments_array), '.');
                    $details = trim($details, ' - ');

                    $created = $year . "-" . $month . "-" . $day . ' 00:00:' . str_pad($seconds, 2, 0, STR_PAD_LEFT);

                    if ($created == $lastCreated) {

                        $seconds++;

                        $created = $year . "-" . $month . "-" . $day . ' 00:00:' . str_pad($seconds, 2, 0, STR_PAD_LEFT);

                    } else {
                        $seconds = 1;
                    }

                    $lastCreated = $created;

                    // update table
                    $sql =
                        "INSERT INTO
                  customernote
              SET
                  cno_custno = " . $custno . ",
                  cno_created = '" . $created . "',
                  cno_consno = 30,
                  cno_details = '" . mysql_escape_string($details) . "'";

                    echo $sql . '<BR/>';

                    mysqli_query($dbUpdate, $sql);


                }

            } // while $element = $comments_array[]

        } // while fetch row


    }

}

?>
