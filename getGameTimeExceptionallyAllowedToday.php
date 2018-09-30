
<?php

/*
getGameTimeExceptionallyAllowedToday : return the total number of minutes that can be played today


function get :

http://localhost/monitor/getGameTimeExceptionallyAllowedToday.php
{"errMsg":"no records found","date":"2018-09-29","gameTimeExceptionallyAllowedToday":"0","gameTimeAllowedDaily":"30"}

function add :

http://localhost/monitor/getGameTimeExceptionallyAllowedToday.php?add=15


 */

//echo 'Version PHP courante : ' . phpversion() . "<br>";
$thisServer = $_SERVER['SERVER_NAME'];
//echo "server : $thisServer<br>";

/* 
    params.php contains among others :
    $webserver = "localhost";
    $dbhost = "localhost";
    $gameTimeAllowedDaily = 30;
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

/*
This function returns in json an array containing the number of minutes exceptionally allocated to gaming
to test it :

http://192.168.0.147/monitor/getGameTimeExceptionallyAllowedToday.php
http://192.168.0.147/monitor/getGameTimeExceptionallyAllowedToday.php?date=2018-09-23
If the date is not specified, the current date is by default
should return something like this :
    {"errMsg":"","date":"2018-09-23","totalMin":"17"}
if no records found :
    {"errMsg":"no records found","date":"2018-09-22","totalMin":"0"}


NB : json validate : https://jsonlint.com/

History :
23/09/2018 ED : first version
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
//$dbhost = "192.168.0.147";

//echo $_SERVER['REQUEST_URI']."\n";
//echo gethostname()."\n";

//sleep(5);

function now() {
    return _date("Y-m-d H:i:s", false, 'Europe/Paris');
}

//========================================================================================
function getGameTimeExceptionallyAllowedToday($gameTimeFunction, $date, $nbMin, $dbhost)
// date must be in format "YYYY-MM-DD"
{

    //echo now()." function : $gameTimeFunction\n";
    $totalMin = 0;
    $errMsg = "";

    switch ($gameTimeFunction) {
        case "get" : 
        $query = "
        SELECT SUM(nbMin) as totalMin
        FROM gameTime
        WHERE
        date = '" . $date . "'
        ";  
        break;
    case "add" : 
        $query = "
        INSERT INTO gameTime(nbMin, date) VALUES (".$nbMin.",'".$date."')
        ";
        break;
    }

    //echo $query."<br>\n";

    include 'connect-db.php';
    //echo now()." before connect\n";
    
    // p: prefix to the host to indicate persistant connection
    $conn = new mysqli("p:".$dbhost, $dbuser, $dbpass, $mydb);
    if ($conn->connect_error) {
        $errMsg = 'Server error in connection. Please try again sometime. dbhost :' . $dbhost . '  mydb:' . $mydb;
        //echo $errMsg;
        return array('errMsg' => $errMsg, 'totalMin' => $totalMin);
    }

    switch ($gameTimeFunction) {
        case "get" : 
            $result = $conn->query($query);
            if (!$result) {
                $errMsg = 'Server error in query. Please try again sometime. dbhost :' . $dbhost . '  mydb:' . $mydb;
                echo $errMsg;
                return array('errMsg' => $errMsg, 'totalMin' => $totalMin);
            }
            //echo now()." before result->num_rows > 0\n";
            if ($result->num_rows > 0) {
                //echo now()." start --------------------------\n";
                // output data of each row
                while ($row = $result->fetch_assoc()) {
                    $totalMin = $row["totalMin"];
                    if ($totalMin == null) {
                            $totalMin = 0;
                            $errMsg = "no records found";
                        }
                }
            }
            break;
        case "add" : 
            $result = $conn->query($query);
            if ($result) {
                $errMsg = "Insert OK";
            } else {
                $errMsg = 'insert failed. dbhost :' . $dbhost . '  mydb:' . $mydb;
            }
            break;
    }

    // don't close connection since we want to keep a persistent connection
    //$conn->close();
    //echo now()." after conn->close\n";

    return array('errMsg' => $errMsg,  'totalMin' => $totalMin);
}

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



///========================================================================================
// main

$today = _date("Y-m-d", false, 'Europe/Paris');
//echo "currtime" . $currTime."\n";

$date = $today;
if (isset($_GET['date'])) {$date = $_GET['date'];}

$myFunc = "get";
if (isset($_GET['myFunc'])) {$myFunc = $_GET['myFunc'];}

$nbMin = 0;
if (isset($_GET['nbMin'])) {$nbMin = $_GET['nbMin'];}

$dbhost = "192.168.0.147";
if (isset($_GET['dbhost'])) {$dbhost = $_GET['dbhost'];}

//checkCharSet($conn);
//echo "this is a test 2";

if ($myFunc == "get") {
    $myArray = getGameTimeExceptionallyAllowedToday($myFunc, $date, 0, $dbhost);
}

if ($myFunc == "add") {
    $myArray = getGameTimeExceptionallyAllowedToday($myFunc, $date, $nbMin, $dbhost);
}

//echo "myArray : ".$myArray;

$errMsg = $myArray['errMsg'];
$gameTimeExceptionallyAllowedToday = $myArray['totalMin'];
//if ($errMsg == "") {
//    $records = json_encode($myArray['gameTimeArray']);
//} else {
//    $records = "[]";
//}



switch ($myFunc) {
    case "get" : 
        $outp = '{"errMsg":"' . $errMsg . '"';
        $outp = $outp . ',"date":"' . $date . '"';
        $outp = $outp . ',"gameTimeExceptionallyAllowedToday":"' . $gameTimeExceptionallyAllowedToday . '"';
        $outp = $outp . ',"gameTimeAllowedDaily":"' . $gameTimeAllowedDaily . '"';
        $outp = $outp . '}';
        echo $outp;
    break;
    case "add" : 
        $outp = '{"errMsg":"' . $errMsg . '"';
        $outp = $outp . '}';
        echo $outp;
    break;
}









?>


