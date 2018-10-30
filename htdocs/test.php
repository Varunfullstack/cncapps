<?php


require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");
require_once($cfg['path_bu'] . '/BUActivity.inc.php');


$holidays = common_getUKBankHolidays(2018);


echo 'select ';
foreach ($holidays as $holidayDate){
    echo " isBankHoliday('$holidayDate') = 1,";
}

