<?php

/*
    This REST service returns YES if the server is up and running (and of course nothing otherwise)
    
    http://localhost/monitor/getTimePlayedToday.php
    returns YES if the server is up
    or
    {"YES"records":[{"duration":0}],"errMsg":"0 records !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!","from":"'2018-09-29'","to":"'2018-09-30'"}

*/


//echo 'Version PHP courante : ' . phpversion() . "<br>";
$thisServer = $_SERVER['SERVER_NAME'];
//echo "server : $thisServer<br>";

/* 
    params.php contains among others :
    $webserver = "localhost";
    $dbhost = "localhost";
    $inTitleList = urlencode('
        "Agar Private Server Agario Game Play Agario - Google Chrome",
        "ZombsRoyale.io | Play ZombsRoyale.io for free on Iogames.space! - Google Chrome",
        "space1.io - Google Chrome"
    ');
    */
include 'params.php';

$defaultTimeZone = 'UTC';
if (date_default_timezone_get() != $defaultTimeZone) {
    date_default_timezone_set($defaultTimeZone);
}

function _date($format = "r", $timestamp = false, $timezone = false)
{
    $userTimezone = new DateTimeZone(!empty($timezone) ? $timezone : 'GMT');
    $gmtTimezone = new DateTimeZone('GMT');
    $myDateTime = new DateTime(($timestamp != false ? date("r", (int) $timestamp) : date("r")), $gmtTimezone);
    $offset = $userTimezone->getOffset($myDateTime);
    return date($format, ($timestamp != false ? (int) $timestamp : $myDateTime->format('U')) + $offset);
}

$today = _date("Y-m-d", false, 'Europe/Paris');
$now = _date("Y-m-d H:i:s", false, 'Europe/Paris');

//========================================================================================

echo json_encode(array('result' => 'YES',  'datetime' => $now));

?>


