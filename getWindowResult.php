<?php

/*
This function returns in json an array containing all the detection times between two dates
to test it :

dailySummary:
http://192.168.0.2/monitor/getWindowResult.php?from='2017-11-16'&to='2017-11-27'&filter=Agar.io+-+Google+Chrome&dbhost=192.168.0.2&nbrecs=100&order=date&myFunc=dailySummary

Should return something like this:
{"records":[
{"id":"","time":"2017-11-22","title":"Agar.io - Google Chrome","duration":"0"},
{"id":"","time":"2017-11-23","title":"Agar.io - Google Chrome","duration":"45"},
{"id":"","time":"2017-11-24","title":"Agar.io - Google Chrome","duration":"0"},
{"id":"","time":"2017-11-25","title":"Agar.io - Google Chrome","duration":"64"},
{"id":"","time":"2017-11-26","title":"Agar.io - Google Chrome","duration":"67"}],
"errMsg":"",
"from":"'2017-11-16'",
"to":"'2017-11-27'"}

dailySummaryTotal:
http://192.168.0.2/monitor/getWindowResult.php?from='2017-11-15'&to='2017-11-26'&filter="Agar.io - Google Chrome","slither.io - Google Chrome"&dbhost=192.168.0.2&nbrecs=100&order=date&myFunc=dailySummaryTotal

Should return something like this :

{"records":[
{"date":"2017-11-15","title":"","duration":"1"},
{"date":"2017-11-22","title":"","duration":"0"},
{"date":"2017-11-23","title":"","duration":"50"},
{"date":"2017-11-24","title":"","duration":"0"},
{"date":"2017-11-25","title":"","duration":"58"}],
"errMsg":"",
"from":"'2017-11-15'",
"to":"'2017-11-26'"}

NB : json validate : https://jsonlint.com/

History :
06/01/2017 : ED : fixing dailySummaryTotal so that it has an explicit point even if value is 0 (otherwise, the graph just links the existing non-zero values)
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
//$dbhost = "192.168.0.147";

//echo $_SERVER['REQUEST_URI']."\n";
//echo gethostname()."\n";

//sleep(5);

class Fgw
{
    public $id;
    public $time;
    public $host;
    public $title;
    public $duration;

    // Assigning the values
    public function __construct($id, $time, $host, $title, $duration)
    {
        $this->id = $id;
        $this->time = $time;
        $this->host = $host;
        $this->title = $title;
        $this->duration = $duration;
    }

    // Creating a method (function tied to an object)
    public function test()
    {
        return "Hello, this is tyis event : " . $this->id . " " . $this->time . " !";
    }
}

//========================================================================================
function getDetails($from, $to, $hostFilter, $titleFilter, $dbhost, $nbrecs)

{
    $fgwArray = array();
    $errMsg = "";
    $query = "
    SELECT fgw_time,fgw_host,fgw_title,fgw_duration,fgw_cpu
    FROM fgw
    WHERE
    fgw_host like '%" . $hostFilter . "%' and
    fgw_title like '%" . $titleFilter . "%' and
    fgw_time >=" . $from . " and
    fgw_time <=" . $to . "
    ORDER by fgw_time desc
    LIMIT " . $nbrecs . "
    ";
    //echo $query."<br>\n";

    include 'connect-db.php';
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $mydb);
    if ($conn->connect_error) {
        //echo 'Server error. Please try again sometime. CON';
        $errMsg = 'Server error. Please try again sometime.';
        $outp = "{}";
        return array('outp' => $outp, 'errMsg' => $errMsg);
    }
    $result = $conn->query($query);

    if (!$result) {
        die("problem : " . $conn->error);
    }

    if (!$conn->set_charset("utf8")) {
        printf("Error with charset utf8 : %s\n", $conn->error);
        exit();
    }

    if ($result->num_rows > 0) {
        // output data of each row

        $fgwArray = array();
        while ($rs = $result->fetch_assoc()) {

            $time = $rs["fgw_time"];
            $host = $rs["fgw_host"];
            $title = $rs["fgw_title"];
            if ($title == null) {$title = "";}
            $duration = $rs["fgw_duration"];
            $cpu = $rs["fgw_cpu"];

            $myfgw = new Fgw("", $time, $host, utf8_encode($title), number_format(strval($duration), 0));

            $fgwArray[] = $myfgw;
        }
    } else {
        $errMsg = "error !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"; // .mysql_error()
    }

    $conn->close();
    return array('errMsg' => $errMsg, 'fgwArray' => $fgwArray);
}

//========================================================================================
function getSummary($from, $to, $hostFilter, $titleFilter, $dbhost, $nbrecs)
{
    $fgwArray = array();
    $errMsg = "";
    $query = "
    SELECT fgw_host,fgw_title,SUM(fgw_duration) AS duration
    FROM fgw
    WHERE
    fgw_host like '%" . $hostFilter . "%' and
    fgw_title like '%" . $titleFilter . "%' and
    fgw_time >=" . $from . " and
    fgw_time <=" . $to . "
    GROUP BY fgw_host,fgw_title
    ORDER by duration desc
    LIMIT " . $nbrecs . "
    ";
    //echo $query."<br>\n";

    include 'connect-db.php';
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $mydb);
    if ($conn->connect_error) {
        //echo 'Server error. Please try again sometime. CON';
        $errMsg = 'Server error. Please try again sometime.';
        $outp = "{}";
        return array('outp' => $outp, 'errMsg' => $errMsg);
    }
    $result = $conn->query($query);

    if (!$result) {
        die("problem : " . $conn->error);
    }
    /*
    if (!$conn->set_charset("utf8")) {
    printf("Error with charset utf8 : %s\n", $conn->error);
    exit();
    }
     */
    if ($result->num_rows > 0) {
        // output data of each row

        $fgwArray = array();
        while ($row = $result->fetch_assoc()) {

            $host = $row["fgw_host"];
            $title = $row["fgw_title"];
            if ($title == null) {$title = "";}
            $duration = $row["duration"];
            $myfgw = new Fgw("", "", $host, utf8_encode($title), number_format(strval($duration / 60), 0));
            $fgwArray[] = $myfgw;
        }
    } else {
        $errMsg = "error !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"; // .mysql_error()
    }
    $conn->close();
    return array('errMsg' => $errMsg, 'fgwArray' => $fgwArray);
}

//========================================================================================
function getDailySummary($from, $to, $hostFilter, $titleFilter, $dbhost, $nbrecs, $order)
{

    $fgwArray = array();
    $errMsg = "";

    $query = "
    SELECT date(fgw_time) as date, fgw_host, fgw_title, SUM(fgw_duration) AS duration, SUM(fgw_duration)/60 AS duration_min
    FROM fgw
    WHERE
    fgw_host like '%". $hostFilter ."%' and
    fgw_title is not null and
    fgw_title <> '' and
    fgw_time >=" . $from . " and
    fgw_title like '%" . $titleFilter . "%' and
    fgw_time <=" . $to . "
    GROUP BY fgw_host,fgw_title,date
    ORDER by " . $order . "
    LIMIT " . $nbrecs . "
    ";

    //echo $query."<br>\n";

    include 'connect-db.php';

    $conn = new mysqli($dbhost, $dbuser, $dbpass, $mydb);
    if ($conn->connect_error) {
        //echo 'Server error. Please try again sometime. CON';
        $errMsg = 'Server error. Please try again sometime. dbhost :' . $dbhost . "  mydb:" . $mydb;
        $outp = "{}";
        return array('outp' => $outp, 'errMsg' => $errMsg);
    }
    $result = $conn->query($query);

    if (!$result) {
        die("problem : " . $conn->error);
    }

    if (!$conn->set_charset("utf8")) {
        printf("Error with charset utf8 : %s\n", $conn->error);
        exit();
    }

    if ($result->num_rows > 0) {
        // output data of each row
        //echo "start --------------------------\n";

        while ($row = $result->fetch_assoc()) {

            $host = $row["fgw_host"];
            $title = $row["fgw_title"];
            if ($title == null) {$title = "";}
            //echo "^$title\n";
            $date = $row["date"];
            $duration = $row["duration"];
            $myfgw = new Fgw("", $date, $host, utf8_encode($title), number_format(strval($duration / 60), 0));
            $fgwArray[] = $myfgw;
        }
    } else {
        $errMsg = "0 records !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"; // .mysql_error()
    }
    $conn->close();
    return array('errMsg' => $errMsg, 'fgwArray' => $fgwArray);
}

//========================================================================================
function getDailySummaryTotal($from, $to, $hostFilter, $inTitleList, $dbhost, $nbrecs, $order)
{

    $errMsg = "";
    $fgwArray = array();
    $query = "
    SELECT date(fgw_time) as date, fgw_host, fgw_title, SUM(fgw_duration) AS duration, SUM(fgw_duration)/60 AS duration_min
    FROM fgw
    WHERE
    " . //fgw_host in ('" . $hostFilter . "') and
        //fgw_title is not null and
    "fgw_title <> '' and
    fgw_host like ('%" . $hostFilter . "%') and
    fgw_title in (" . $inTitleList . ") and
    fgw_time >=" . $from . " and
    fgw_time <=" . $to . "
    GROUP BY date
    ORDER by " . $order . "
    LIMIT " . $nbrecs . "
    ";

    //echo $query."<br>\n";
    include 'connect-db.php';
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $mydb);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $result = $conn->query($query);

    if (!$result) {
        die("problem : " . $conn->error);
    }

    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $myfgw = new Fgw("", $row["date"], "", "", number_format(strval($row["duration"]/ 60), 0));
            $fgwArray[] = $myfgw;
        }
    } else {
        $errMsg = "!!!!!!! 0 results";
    }
    $conn->close();

     return array('errMsg' => $errMsg, 'fgwArray' => $fgwArray);
}

//========================================================================================
function getFgw($fgwFunction, $from, $to, $hostFilter, $titleParam, $dbhost, $nbrecs, $order)
{

    //echo "function : $fgwFunction\n";
    $fgwArray = array();
    $errMsg = "";

    switch ($fgwFunction) {
    case "details" : 
        $query = "
        SELECT fgw_time,fgw_host,fgw_title,fgw_duration,fgw_cpu
        FROM fgw
        WHERE
        fgw_host like '%" . $hostFilter . "%' and
        fgw_title like '%" . $titleParam . "%' and
        fgw_time >=" . $from . " and
        fgw_time <=" . $to . "
        ORDER by fgw_time desc
        LIMIT " . $nbrecs . "
        ";  
        break;
    case "summary" : 
        $query = "
        SELECT fgw_host,fgw_title,SUM(fgw_duration) AS duration
        FROM fgw
        WHERE
        fgw_host like '%" . $hostFilter . "%' and
        fgw_title like '%" . $titleParam . "%' and
        fgw_time >=" . $from . " and
        fgw_time <=" . $to . "
        GROUP BY fgw_host,fgw_title
        ORDER by duration desc
        LIMIT " . $nbrecs . "
        ";  
        break;
    case "dailySummary" : 
        $query = "
        SELECT date(fgw_time) as date, fgw_host, fgw_title, SUM(fgw_duration) AS duration, SUM(fgw_duration)/60 AS duration_min
        FROM fgw
        WHERE
        fgw_host like '%". $hostFilter ."%' and
        fgw_title is not null and
        fgw_title <> '' and
        fgw_title like '%" . $titleParam . "%' and
        fgw_time >=" . $from . " and
        fgw_time <=" . $to . "
        GROUP BY fgw_host,fgw_title,date
        ORDER by " . $order . "
        LIMIT " . $nbrecs . "
        ";
        break;
    case "dailySummaryTotal" : 
        $query = "
        SELECT date(fgw_time) as date, fgw_host, fgw_title, SUM(fgw_duration) AS duration, SUM(fgw_duration)/60 AS duration_min
        FROM fgw
        WHERE
        fgw_title in (" . $titleParam . ") and
        fgw_host like ('%" . $hostFilter . "%') and
        fgw_title is not null and
        fgw_title <> '' and
        fgw_time >=" . $from . " and
        fgw_time <=" . $to . "
        GROUP BY date
        ORDER by " . $order . "
        LIMIT " . $nbrecs . "
        ";
        break;
        
     case "others" : 
        break;
    }

    //echo $query."<br>\n";

    include 'connect-db.php';

    $conn = new mysqli($dbhost, $dbuser, $dbpass, $mydb);
    if ($conn->connect_error) {
        echo 'Server error. Please try again sometime. CON';
        $errMsg = 'Server error. Please try again sometime. dbhost :' . $dbhost . "  mydb:" . $mydb;
        return array('errMsg' => $errMsg, 'fgwArray' => $fgwArray);
    }
    $result = $conn->query($query);

    if (!$result) {
        $errMsg = "db error : $conn->error";
        die("problem : " . $conn->error);
    } else {
        //echo "connection OK !";
    }

    if (!$conn->set_charset("utf8")) {
        $errMsg = "db error : $conn->error";
        die("problem : " . $conn->error);
    }

    if ($result->num_rows > 0) {
        //echo "start --------------------------\n";
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            switch ($fgwFunction) {
                case "details":
                $time = $row["fgw_time"];
                $host = $row["fgw_host"];
                $title = $row["fgw_title"];
                if ($title == null) {$title = "";}
                $duration = $row["fgw_duration"];
                $cpu = $row["fgw_cpu"];
                $myfgw = new Fgw("", $time, $host, utf8_encode($title), number_format(strval($duration), 0));
                $fgwArray[] = $myfgw;
                break;
            case "summary":
                $host = $row["fgw_host"];
                $title = $row["fgw_title"];
                if ($title == null) {$title = "";}
                $duration = $row["duration"];
                $myfgw = new Fgw("", "", $host, utf8_encode($title), number_format(strval($duration / 60), 0));
                $fgwArray[] = $myfgw;
                break;
            case "dailySummary":
                $host = $row["fgw_host"];
                $title = $row["fgw_title"]; 
                if ($title == null) {
                    $title = "";
                }
                //echo "$title\n";
                $date = $row["date"];
                $duration = $row["duration"];
                $myfgw = new Fgw("", $date, $host, utf8_encode($title), number_format(strval($duration / 60), 0));
                $fgwArray[] = $myfgw;    
                break;
            case "dailySummaryTotal":
                $myfgw = new Fgw("", $row["date"], "", "", number_format(strval($row["duration"]/ 60), 0));
                $fgwArray[] = $myfgw;
                break;
            case "others":
                break;
            }
        }
    } else {
        $errMsg = "0 records !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"; // .mysql_error()
    }
    $conn->close();
    return array('errMsg' => $errMsg, 'fgwArray' => $fgwArray);
}

//========================================================================================
function checkCharSet($conn)
{
    echo "<!DOCTYPE html>";
    echo "<html>";
    echo "before\n";
    $re = mysql_query('SHOW VARIABLES LIKE "%character_set%";') or die(mysql_error());
    while ($r = mysql_fetch_assoc($re)) {
        var_dump($r);
        echo "<br />";
    }
    echo "---------------------------------------------------<br />\n";

    mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

    $re = mysql_query('SHOW VARIABLES LIKE "%character_set%";') or die(mysql_error());
    while ($r = mysql_fetch_assoc($re)) {
        var_dump($r);
        echo "<br />";
    }
    echo "after\n";
}

//========================================================================================
function get_string_between($string, $start, $end)
{
    $string = " " . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) {
        return "";
    }

    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

//========================================================================================
function complementFgwArray($fgwArray) 
{
    //add records with a zero duration for the days where there has been no records, in order to have a correct graph
    $complementedFgwArray = array();
    //echo "count : " .count($fgwArray); 
    if (count($fgwArray) >=1) {
        $complementedFgwArray[] = $fgwArray[0];
        $prevDate = $fgwArray[0]->time;
        //echo "prevDate : " . $prevDate . "<br>\n";
    }
    $i = 0;
    foreach ($fgwArray as $myfgw) {
        //echo "---------------- myfgw->time : " . $myfgw->time . "prev : " . $prevDate . "<br>\n";
        // the first record is already there
        if ($i == 0) {
            $i+=1;
        } else {
            //echo "------------------myfgw->time : ".$myfgw->time."<br>\n";
            $thisDate = date('Y-m-d',strtotime($myfgw->time));
            $j=0;
            while (($thisDate <> date('Y-m-d', strtotime($prevDate.' +1 day'))) and ($j<1000) ) {
                $complementedFgwArray[] = new Fgw("", date('Y-m-d', strtotime($prevDate.' +1 day')), "", "", 0);
                $prevDate=date('Y-m-d', strtotime($prevDate.' +1 day'));
                $j+=1;
            }
            $complementedFgwArray[] = new Fgw("", $myfgw->time, "", "", $myfgw->duration);
            $prevDate=date('Y-m-d', strtotime($prevDate.' +1 day'));
            $i+=1;
        }
    }
    return($complementedFgwArray);
}
//========================================================================================
// main

$from = "'2000-01-01'";
if (isset($_GET['from'])) {$from = $_GET['from'];}

$to = "'2099-01-01'";
if (isset($_GET['to'])) {$to = $_GET['to'];}

$hostFilter = "%";
if (isset($_GET['hostFilter'])) {
    $hostFilter = $_GET['hostFilter'];
}

$titleFilter = "%";
if (isset($_GET['titleFilter'])) {
    $titleFilter = $_GET['titleFilter'];
}

$inTitleList = '"xxx"';
if (isset($_GET['inTitleList'])) {
    $inTitleList = $_GET['inTitleList'];
}

$myFunc = "details";
if (isset($_GET['myFunc'])) {$myFunc = $_GET['myFunc'];}

$nbrecs = "5";
if (isset($_GET['nbrecs'])) {$nbrecs = $_GET['nbrecs'];}

$dbhost = "192.168.0.147";
if (isset($_GET['dbhost'])) {$dbhost = $_GET['dbhost'];}

$order = "duration desc";
if (isset($_GET['order'])) {$order = $_GET['order'];}

//checkCharSet($conn);
//echo "this is a test 2";

if ($myFunc == "details") {
    //$myArray = getDetails($from, $to, $hostFilter, $titleFilter, $dbhost, $nbrecs);
    $myArray = getFgw($myFunc, $from, $to, $hostFilter, $titleFilter, $dbhost, $nbrecs, $order);
} elseif ($myFunc == "summary") {
    //$myArray = getSummary($from, $to, $hostFilter, $titleFilter, $dbhost, $nbrecs);
    $myArray = getFgw($myFunc, $from, $to, $hostFilter, $titleFilter, $dbhost, $nbrecs, $order);
} elseif ($myFunc == "dailySummary") {
    //echo "test daily summary; dbhost : $dbhost\n";
    //$myArray = getDailySummary($from, $to, $hostFilter, $titleFilter, $dbhost, $nbrecs, $order);
    $myArray = getFgw($myFunc, $from, $to, $hostFilter, $titleFilter, $dbhost, $nbrecs, $order);

    if (0) { //debug
        if ($myArray['errMsg']<>"") {
            echo "errMsg : " . $myArray['errMsg'];
        } else {
            echo "first record : " . $myArray['fgwArray'][0]->title;
        }
    } //debug

} elseif ($myFunc == "dailySummaryTotal") {
    //$myArrayTemp = getDailySummaryTotal($from, $to, $hostFilter, $inTitleList, $dbhost, $nbrecs, $order);
    $myArrayTemp = getFgw($myFunc, $from, $to, $hostFilter, $inTitleList, $dbhost, $nbrecs, $order);
    //add records with a zero duration for the days where there has been no records, in order to have a correct graph
    $complementedFgwArray = complementFgwArray($myArrayTemp['fgwArray']);
    $myArray = array('errMsg' => $myArrayTemp['errMsg'], 'fgwArray' => $complementedFgwArray);
}

//echo "myArray : ".$myArray;


$errMsg = $myArray['errMsg'];
if ($errMsg == "") {
    $records = json_encode($myArray['fgwArray']);
} else {
    $records = "[]";
}

$outp = '{"records":' . $records;
$outp = $outp . ',"errMsg":"' . $errMsg . '"';
$outp = $outp . ',"from":"' . $from . '"';
$outp = $outp . ',"to":"' . $to . '"';
$outp = $outp . '}';
echo $outp;

