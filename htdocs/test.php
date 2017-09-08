<?php
/**
 * Created by PhpStorm.
 * User: fizda
 * Date: 25/07/2017
 * Time: 13:56
 */

include('DB.php');

$test = DB::connect($dsn, true);

var_dump($test);