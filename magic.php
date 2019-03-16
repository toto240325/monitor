
<?php

/*
magic.php

magic is enabled if the monitoring system doesn't need to check anything (we are in a grace period)

if parameter is :
    isMagicEnabled : return $true or $false depending on whether magic is enabled or not
    enableMagic    : enable the magic state
    disableMagic   : disable the magic state



examples : 
http://localhost/monitor/magic.php?isMagicEnabled
{"errMg":"", "isMagicEnabled":"$true"}

http://192.168.0.147/monitor/magic.php?enableMagic
{"errMg":"", "isMagicEnabled":"$true"}

http://localhost/monitor/magic.php?mock
{"errMsg":"unrecognised function !","isMagicEnabled":""}

NB : json validate : https://jsonlint.com/
 
History :
16/03/2019 ED : first version

 */

//echo 'Version PHP courante : ' . phpversion() . "<br>";
$thisServer = $_SERVER['SERVER_NAME'];
//echo "server : $thisServer<br>";

/* 
    params.php contains among others :
    $webserver = "localhost";
    $dbhost = "localhost";
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
function magic($myFunc, $dbhost)
// date must be in format "YYYY-MM-DD"
{

    //echo now()." function : $myFunc\n";
    $isMagicEnabled = "";
    $errMsg = "";

    switch ($myFunc) {
    case "isMagicEnabled" : 
        $query = "
        SELECT pvalue
        FROM params
        WHERE pname = 'isMagicEnabled'
        ";  
        break;
    case "enableMagic" : 
        $query = "
        UPDATE params
        SET pvalue = '1' 
        WHERE pname = 'isMagicEnabled'
        ";
        break;
    case "disableMagic" : 
        $query = "
        UPDATE params
        SET pvalue = '0' 
        WHERE pname = 'isMagicEnabled'
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
        case "isMagicEnabled" : 
            $isMagicEnabled = 0;
            $result = $conn->query($query);
            if (!$result) {
                $errMsg = 'Server error in query. Please try again sometime. dbhost :' . $dbhost . '  mydb:' . $mydb;
                echo $errMsg;
                return array('errMsg' => $errMsg, 'keywords' => array());
            }
            //echo now()." before result->num_rows > 0\n";
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                        $isMagicEnabled = $row["pvalue"];
                }
                $errMsg = "isMagicEnabled check successful";
            }
            break;
        case "enableMagic" : 
            $result = $conn->query($query);
            if ($result) {
                $errMsg = "magic now enabled";
                $isMagicEnabled = 1;
            } else {
                $errMsg = 'enableMagic failed; dbhost :' . $dbhost . ';  mydb:' . $mydb;
            }
            break;
        case "disableMagic" : 
            $result = $conn->query($query);
            if ($result) {
                $errMsg = "magic now disabled";
                $isMagicEnabled = 0;
            } else {
                $errMsg = 'disableMagic failed; dbhost :' . $dbhost . ';  mydb:' . $mydb;
            }
            break;
    }

    // don't close connection since we want to keep a persistent connection
    //$conn->close();
    //echo now()." after conn->close\n";

    return array('errMsg' => $errMsg,  'isMagicEnabled' => $isMagicEnabled);
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



/*
var_dump($_GET); 
print_r($_GET); 
print_r($_GET(1)); 


if(empty($_GET)) {
   $myFunc = "isMagicEnabled"; }
else {
    $param1 = $_GET[1]; 
    print_r($_GET)[1]; 
}

    foreach($params as $x => $x_value) {

       echo "Key=" . $x . ", Value=" . $x_value;
       echo "<br>";
   }
*/ 


$myFunc = "";
if (isset($_GET['isMagicEnabled'])) {$myFunc = "isMagicEnabled";}
if (isset($_GET['enableMagic'])) {$myFunc = "enableMagic";}
if (isset($_GET['disableMagic'])) {$myFunc = "disableMagic";}


$dbhost = "localhost";
if (isset($_GET['dbhost'])) {$dbhost = $_GET['dbhost'];}

//checkCharSet($conn);
//echo "this is a test 2 : " .$myFunc."\n";

switch ($myFunc) {
    case "isMagicEnabled" : 
        $myArray = magic($myFunc, $dbhost);
    break;
    case "enableMagic" : 
        $myArray = magic($myFunc, $dbhost);
    break;
    case "disableMagic" : 
        $myArray = magic($myFunc, $dbhost);
    break;
    case "mock" : 
    $myArray = array('errMsg' => "mock function !",  'isMagicEnabled' => '0');
    break;
    default : 
        $errMsg = "unrecognised function !";       
        $myArray = array('errMsg' => "unrecognised function !",  'isMagicEnabled' => "");
    break;
}

//echo "myArray : ".$myArray;
//echo "----------------------\n";
//echo "my array :";
//var_dump($myArray);


$errMsg = $myArray['errMsg'];
//echo "keywords : "; var_dump($myArray['keywords'])  ;
$isMagicEnabled = $myArray['isMagicEnabled'];

//if ($errMsg == "") {
//    $records = json_encode($myArray['keywordsArray']);
//} else {
//    $records = "[]";
//}

$outp = '{"errMsg":"' . $errMsg . '"';
$outp = $outp . ',"isMagicEnabled":"' . $isMagicEnabled . '"';
$outp = $outp . '}';
echo $outp;

?>


