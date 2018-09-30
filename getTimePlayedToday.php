<?php

/*
    This REST service returns the number of minutes played today on a given host

    http://localhost/monitor/getTimePlayedToday.php
    returns either

    or
    {"records":[{"duration":0}],"errMsg":"0 records !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!","from":"'2018-09-29'","to":"'2018-09-30'"}

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
        "Surviv.io | Play Surviv.io for free on Iogames.space! - Google Chrome	",
        "Agar.io - Google Chrome",
        "alis.io - Google Chrome",
        "slither.io - Google Chrome",
        "diep.io - Google Chrome",
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

function getGamesTimeToday()
{
    global $dbhost;
    global $thisServer;
    global $inTitleList;
    global $today;
    

//    $inTitleList = urlencode(' "Document1 - Microsoft Word" ');


    // prepare Agario graph -----------------------------------------------------------------------------
    //$fromDateAgar = new DateTime(date('Y-m-d'));
    //$fromDateAgar->modify('-10 day');
    //$fromAgar = $fromDateAgar->format('Y-m-d');
    $fromAgar = $today;

    //$toDateAgar = new DateTime(date('Y-m-d'));
    //$toDateAgar = $today;
    //$toDateAgar->modify('+1 day');
    //$toAgar = $toDateAgar->format('Y-m-d');
    $toAgar = date('Y-m-d', strtotime($today. '+1 days'));

    //echo "from:".$fromAgar."+++<br>\n";
    //echo "to:".$toAgar."+++<br>\n";
 
    //$toAgar = ((new Datetime(date('Y-m-d')))->modify('+1 day'))->format('Y-m-d');
    //$to = $to." 23:59:59";
    //echo "from - to : <br>";
    //echo $from." - ".$to."<br>";

    //$to = urlencode($to);
    //echo $inTitleList;

    //$myPageAgarioAndOtherGames = "http://" . $webserver . "/monitor/getWindowResult.php" .


    $myPageAgarioAndOtherGames = "http://" . $thisServer . "/monitor/getWindowResult.php" .
        "?from='" . $fromAgar . "'" .
        "&to='" . $toAgar . "'" .
        "&inTitleList=" . $inTitleList .
        "&dbhost=" . $dbhost .
        "&nbrecs=100" .
        "&order=date" .
        "&myFunc=dailySummaryTotal";

    //echo "<br><br>=========================================<br>".$myPageAgarioAndOtherGames."<br>\n\n\n";
    //echo "-----------------------------------------<br>\n\n";



    //echo "fromAgar : ".$fromAgar."  toAgar : ".$toAgar."<br>";
    //echo "mypage Agario2 : ".$myPageAgarioAndOtherGames."<br>";
    
    /*
    echo "<script>";
    echo 'console.log("mypage Agario2 : ' . $myPageAgarioAndOtherGames . '")';
    echo "</script>";
    */

    //('mypage Agario2 : '".$myPageAgarioAndOtherGames);
    $json = file_get_contents($myPageAgarioAndOtherGames);
    //$json = file_get_contents($a);
    //echo "json 123 : "."<br>";
    //print_r($json)."\nb<br>";
    //echo "test 34<br>\n"; var_dump($json);
    return $json;     
}


echo getGamesTimeToday();

?>


