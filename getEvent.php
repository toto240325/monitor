<?php

/*

This function returns in json an array containing the last event, with its ID, time, text, event_type.

Output example : 
{"id":"63","time":"2017-11-22 22:07:56","host":"mypc3","text":"backup p702 to googleDrive via mypc3","type":"backup p702"}

Examples : 
http://localhost/monitor/getEvent.php?eventFct=getLastEventByType&type=uploading+file
http://192.168.0.147/monitor/getEvent.php?eventFct=getLastEventByType&type=backup+P702
http://localhost/monitor/getEvent.php?eventFct=add&time="2018-01-16"&host=myHost&text=my+text&type=my+type

Mockup : (no database connection) 
http://192.168.0.147/monitor/getEvent.php?type=mockup

Note : mysql_* deprecated ! use MySQLi instead

*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$URI = urldecode($_SERVER['REQUEST_URI']);
$myPost = $_POST;
$myGet = $_GET;

//echo $URI;


$defaultTimeZone='UTC';
if(date_default_timezone_get()!=$defaultTimeZone) date_default_timezone_set($defaultTimeZone);

//==========================================================================================================================
function _date($format="r", $timestamp=false, $timezone=false)
{
    $userTimezone = new DateTimeZone(!empty($timezone) ? $timezone : 'GMT');
    $gmtTimezone = new DateTimeZone('GMT');
    $myDateTime = new DateTime(($timestamp!=false?date("r",(int)$timestamp):date("r")), $gmtTimezone);
    $offset = $userTimezone->getOffset($myDateTime);
    return date($format, ($timestamp!=false?(int)$timestamp:$myDateTime->format('U')) + $offset);
}

//==========================================================================================================================
class Event {
    public $id;
    public $time;
    public $host;
    public $text;
    public $type;
    
    // Assigning the values
    public function __construct($id, $time, $host, $text, $type) {
        $this->id = $id;
        $this->time = $time;
        $this->host = $host;
        $this->text = $text;
        $this->type = $type;
    }
    
    // Creating a method (function tied to an object)
    public function test() {
        return "Hello, this is this event : " . $this->id . " " . $this->time . " !";
    }
}

//==========================================================================================================================
function getEvent($eventFct, $type, $dbhost) {
 
    $recordsArray = array();
    $errMsg = "";
    switch ($eventFct) {
        case "add" : 
            $query = "
            INSERT INTO event ".
            "(event_time,event_host,event_text,event_type) ".
            "VALUES ".
            "('$myNow','$event_host','$event_text','$event_type')
            ";
        break;
        case "getLastEventByType" : 
            $query = "
            SELECT event_id, event_time, event_host, event_text, event_type
            FROM event
            WHERE event_type = '".$type."'
            ORDER BY event_id DESC
            LIMIT 1
            ";
            break;
        case "others" : 
            break;
    }
    
    #echo $query;

    include 'connect-db.php';
    $conn = new mysqli($dbhost,$dbuser,$dbpass,$mydb);
    if ($conn->connect_error) {
        $errMsg = 'Server error. Please try again sometime. dbhost :' . $dbhost . "  mydb:" . $mydb;
        //echo $errMsg;
        return array('errMsg' => $errMsg, 'recordsArray' => $recordsArray);
    } 
    $result = $conn->query($query);
    if (!$result) {
        $errMsg = 'Server error 2. Please try again sometime. dbhost :' . $dbhost . "  mydb:" . $mydb;
        //echo $errMsg;
        return array('errMsg' => $errMsg, 'recordsArray' => $recordsArray);
    }
    
    if (!$conn->set_charset("utf8")) {
        $errMsg = 'Server error in setting charset : '. $conn->error . 'Please try again sometime. dbhost :' . $dbhost . '  mydb:' . $mydb;
        //echo $errMsg;
        return array('errMsg' => $errMsg, 'recordsArray' => $recordsArray);
    }


    //$myEvent = new Event("0","1999-12-31 23:59:59","(none)","(none)","(none)");

    if ($result->num_rows > 0) {
        // output data of each row
            
        while ($row = $result->fetch_assoc()) {
            switch ($eventFct) {
            case "getLastEventByType":
                $id = $row["event_id"];
                $time = $row["event_time"];
                $host = $row["event_host"];
                $text = utf8_encode($row["event_text"]);
                $type = $row["event_type"];
                                
                //echo "id: " . $row["event_id"]. " - time: " . $row["event_time"]. "<br>";
                /*
                $myEvent->id = $id;
                $myEvent->time = $time;
                $myEvent->host = $host;
                $myEvent->text = utf8_encode($text);
                $myEvent->type = $type;
                */

                // __construct($id, $time, $host, $text, $type)
                $myRecord = new Event($id, $time, $host, $text, $type);
                $recordsArray[] = $myRecord;
                break;
            case "others":
                break;
            }
        }
    } else {
        $errMsg = "0 records !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"; // .mysql_error()
    }
    $conn->close();
    //return $myEvent;
    return array('errMsg' => $errMsg, 'recordsArray' => $recordsArray);
}

//==========================================================================================================================
function addEvent($time, $host, $text, $type, $dbhost) {
 
    $errMsg = "";
    $query = "
    INSERT INTO event ".
    "(event_time,event_host,event_text,event_type) ".
    "VALUES ".
    "('$time','$host','$text','$type')
    ";
    
    //echo $query;

    include 'connect-db.php';
    $conn = new mysqli($dbhost,$dbuser,$dbpass,$mydb);
    if ($conn->connect_error) {
        $errMsg = 'Server error. Please try again sometime. dbhost :' . $dbhost . "  mydb:" . $mydb;
        //echo $errMsg;
        return array('errMsg' => $errMsg, 'recordsArray' => $recordsArray);
    } 

    if (!$conn->set_charset("utf8")) {
        $errMsg = 'Server error in setting charset : '. $conn->error . 'Please try again sometime. dbhost :' . $dbhost . '  mydb:' . $mydb;
        //echo $errMsg;
        return array('errMsg' => $errMsg, 'recordsArray' => $recordsArray);
    }

    $result = $conn->query($query);
    if (!$result) {
        $errMsg = 'Server error during insert : ' . $conn->error . '. dbhost :' . $dbhost . "  mydb:" . $mydb;
        //echo $errMsg;
    } else {
        $errMsg = "Record inserted correctly";
    }
    $conn->close();
    return $errMsg;
}

//==========================================================================================================================
function getParam($paramName, $defaultValueStr) {
    $paramValue = "";

    if (isset($_GET[$paramName])) { $paramValue = $paramValue . $_GET[$paramName]; }
    if (isset($_POST[$paramName])) { $paramValue = $paramValue . $_POST[$paramName]; }

    if ($paramValue == "") {
        $paramValue = $defaultValueStr;
    }
    return $paramValue;    
}

//==========================================================================================================================
//==========================================================================================================================
//==========================================================================================================================

$currTime = _date("Y-m-d H:i:s", false, 'Europe/Paris');

include 'params.php';
$dbhost = getParam("dbhost", $dbhost);

$eventFct = getParam("eventFct", "(no Fct specified)");
$host = getParam("host", "(no host specified)");
$text = getParam("text", "(no text specified)");
$type = getParam("type", "(no type specified)");

//$eventFct = "(no Fct specified)";
//if (isset($_GET['eventFct']) OR isset($_POST['eventFct']) ) { $eventFct = $_GET['eventFct'] + $_POST['eventFct']; }
/*
$host = "(no host specified)";
if(isset($_GET['host'])) { $host = $_GET['host']; }

$text = "(no text)";
if(isset($_GET['text'])) { $text = $_GET['text']; }

$type = "backup p702";
if(isset($_GET['type'])) { $type = $_GET['type']; }

$dbhost = "192.168.0.147";
if(isset($_GET['dbhost'])) { $dbhost = $_GET['dbhost']; }
*/

//echo "eventFct : ".$eventFct."\n";


if ($eventFct == "mockup") {
    $myMockupRecord =  '{"id":"63","time":"2017-11-22 22:07:56","host":"mockup host","text":"this is the mockup event","type":"mockup"}';
    $myArray = array('errMsg' => "", 'recordsArray' => $myMockupRecord);
    //echo '{"id":"63","time":"2017-11-22 22:07:56","host":"mockup host","text":"this is the mockup event","type":"mockup"}';
} elseif ($eventFct == "add") {
    $errMsg = addEvent($currTime, $host, $text, $type, $dbhost);
    $myArray = array('errMsg' => $errMsg, 'recordsArray' => array());
} elseif ($eventFct == "getLastEventByType") {
    $myArray = getEvent($eventFct, $type, $dbhost);
    //echo json_encode(getEvent($eventFct,$type, $dbhost));
} else {
    $myArray = array('errMsg' => 
        '!!!! not a known function: ' . $eventFct . 
        ' URI : ' . $URI . 
        ' myGet : ' . json_encode($myGet) . 
        ' myPost : ' . json_encode($myPost), 
        'recordsArray' => array());
}

$errMsg = $myArray['errMsg'];
if ($errMsg == "") {
    $records = json_encode($myArray['recordsArray']);
} else {
    $records = "[]";
}

$outp = '{"records":' . $records;
$outp = $outp . ',"errMsg":"' . $errMsg . '"';
$outp = $outp . '}';
echo $outp;

?>

