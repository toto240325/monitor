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


//========================================================================================
function getGamesTimeToday($dbhost)
{
    //echo now()." function : $myFunc\n";

    $today = _date("Y-m-d", false, 'Europe/Paris');
    $timePlayedToday = -1;
    $from = $today;
    $to = date('Y-m-d', strtotime($today. '+1 days'));
    $errMsg = "";

    $query = "
    SELECT SUM(fgw_duration)/60 AS duration_min 
    FROM fgw 
    WHERE fgw_time>= '" . $from . "' 
    and fgw_time <'" . $to . "' 
    and fgw_isgame = 1 
    ";  

    //echo $query."<br>\n";

    include 'connect-db.php';
    
    // p: prefix to the host to indicate persistant connection
    $conn = new mysqli("p:".$dbhost, $dbuser, $dbpass, $mydb);
    if ($conn->connect_error) {
        $errMsg = 'Server error in connection. Please try again sometime. dbhost :' . $dbhost . '  mydb:' . $mydb;
        //echo $errMsg;
        return array('errMsg' => $errMsg, 'timePlayedToday' => -1);
    }

    $result = $conn->query($query);
    if (!$result) {
        $errMsg = 'Server error in query. Please try again sometime. dbhost :' . $dbhost . '  mydb:' . $mydb;
        echo $errMsg;
        return array('errMsg' => $errMsg, 'timePlayedToday' => -1);
    }
    
    //echo now()." before result->num_rows > 0\n";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $timePlayedToday = floatval($row["duration_min"]);
//            echo "timePlayedToday : ".$timePlayedToday;
            $timePlayedToday = floor($timePlayedToday);
//            echo "timePlayedToday : ".$timePlayedToday;
        }
        $errMsg = "";
    }

    // don't close connection since we want to keep a persistent connection
    //$conn->close();
    //echo now()." after conn->close\n";
    return json_encode(array('errMsg' => $errMsg,  'timePlayedToday' => $timePlayedToday));
}

function getGamesTimeToday_obs()
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

$dbhost = "localhost";
if (isset($_GET['dbhost'])) {$dbhost = $_GET['dbhost'];}

echo getGamesTimeToday($dbhost);

//echo getGamesTimeToday_obs();

?>


