
<?php

/*
getKeywords : return the list of keywords to check in windows titles

function get :
http://localhost/monitor/getKeywords.php
{["keyword1", "keyword expression 2"]}

function add :

http://localhost/monitor/getKeywords.php?add=15


NB : json validate : https://jsonlint.com/

History :
01/10/2018 ED : first version

 */

//echo 'Version PHP courante : ' . phpversion() . "<br>";
$thisServer = $_SERVER['SERVER_NAME'];
//echo "server : $thisServer<br>";

/* 
    params.php contains among others :
    $webserver = "localhost";
    $dbhost = "localhost";
    $keywordsAllowedDaily = 30;
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
function getKeywords($myFunc, $keyword, $dbhost)
// date must be in format "YYYY-MM-DD"
{

    //echo now()." function : $myFunc\n";
    $keywords = [];
    $errMsg = "";

    switch ($myFunc) {
        case "get" : 
        $query = "
        SELECT keyword
        FROM keywords
        ";  
        break;
        case "add" : 
        $query = "
        INSERT INTO keywords(keyword) VALUES ('".$keyword."')
        ";
        break;
    case "del" : 
        $query = "
        DELETE FROM keywords 
        WHERE keyword='".$keyword."'
        ";
        break;
    }

    //echo $query."<br>\n";

    include 'connect-db.php';
    //echo now()." before connect : $dbhost \n";
    
    // p: prefix to the host to indicate persistant connection
    $conn = new mysqli("p:".$dbhost, $dbuser, $dbpass, $mydb);
    if ($conn->connect_error) {
        $errMsg = 'Server error in connection. Please try again sometime. dbhost :' . $dbhost . '  mydb:' . $mydb;
        //echo $errMsg;
        return array('errMsg' => $errMsg, 'records' => array());
    }

    switch ($myFunc) {
        case "get" : 
            $result = $conn->query($query);
            if (!$result) {
                $errMsg = 'Server error in query. Please try again sometime. dbhost :' . $dbhost . '  mydb:' . $mydb;
                echo $errMsg;
                return array('errMsg' => $errMsg, 'keywords' => array());
            }
            //echo now()." before result->num_rows > 0\n";
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                        $keyword = $row["keyword"];
                        $keywords[] = $keyword;
                }
                $errMsg = "test get";
            }
            break;
        case "add" : 
            $result = $conn->query($query);
            if ($result) {
                $errMsg = "Insert OK";
            } else {
                $errMsg = 'insert failed. keyword : ' . $keyword . '; dbhost :' . $dbhost . ';  mydb:' . $mydb;
            }
            break;
        case "del" : 
            $result = $conn->query($query);
            if ($result) {
                $errMsg = "Delete OK";
                //var_dump($result);
            } else {
                //var_dump($result);
                $errMsg = 'delete failed. keyword : ' . $keyword . '; dbhost :' . $dbhost . ';  mydb:' . $mydb;
            }
            break;
    }

    // don't close connection since we want to keep a persistent connection
    //$conn->close();
    //echo now()." after conn->close\n";


    return array('errMsg' => $errMsg,  'keywords' => $keywords);
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

$keyword = 0;
if (isset($_GET['keyword'])) {$keyword = $_GET['keyword'];}

$dbhost = "localhost";
if (isset($_GET['dbhost'])) {$dbhost = $_GET['dbhost'];}

//checkCharSet($conn);
//echo "this is a test 2 : " .$myFunc."\n";

switch ($myFunc) {
    case "get" : 
        $myArray = getKeywords($myFunc, "", $dbhost);
    break;
    case "add" : 
        $myArray = getKeywords($myFunc, $keyword, $dbhost);
    break;
    case "del" : 
        $myArray = getKeywords($myFunc, $keyword, $dbhost);
    break;
    case "mock" : 
    $myArray = array('errMsg' => "mock function !",  'keywords' => array("keyword1","keyword2"));
    //$myArray = array('errMsg' => "mock function !", 'keywords' => array());
    break;
    default : 
        $errMsg = "unrecognised function !";       
        $myArray = array('errMsg' => "unrecognised function !",  'keywords' => array());
    break;
}

//echo "myArray : ".$myArray;
//echo "----------------------\n";
//echo "my array :";
//var_dump($myArray);


$errMsg = $myArray['errMsg'];
//echo "keywords : "; var_dump($myArray['keywords'])  ;
$records = json_encode($myArray['keywords']);

//if ($errMsg == "") {
//    $records = json_encode($myArray['keywordsArray']);
//} else {
//    $records = "[]";
//}

$outp = '{"errMsg":"' . $errMsg . '"';
$outp = $outp . ',"keywords":' . $records ;
$outp = $outp . '}';
echo $outp;

?>


