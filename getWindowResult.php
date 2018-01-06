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
$dbhost = "192.168.0.147";

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

function getDetails($from, $to, $filter, $dbhost, $nbrecs)
{
    $outp = "";
    $errMsg = "";

    $query = "
    SELECT fgw_time,fgw_host,fgw_title,fgw_duration,fgw_cpu
    FROM fgw
    WHERE
    fgw_title like '%" . $filter . "%' and
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

            if ($outp != "") {$outp .= ",";}
            $title = $rs["fgw_title"];
            if ($title == null) {$title = "";}
            $time = $rs["fgw_time"];
            $host = $rs["fgw_host"];
            $duration = $rs["fgw_duration"];
            $cpu = $rs["fgw_cpu"];
            $outp .= '{';
            $outp .= '"time":"' . $time . '",';
            $outp .= '"host":"' . $host . '",';
            $outp .= '"title":"' . utf8_encode($title) . '",';
            $outp .= '"duration":"' . $duration . '",';
            $outp .= '"cpu":"' . $cpu . '",';
            $outp .= '"dur_min":"' . number_format(strval($duration / 60), 0) . '"';
            $outp .= '}';

            $myfgw = new Fgw("", $time, $host, utf8_encode($title), number_format(strval($duration), 0));

            $fgwArray[] = $myfgw;
        }
    } else {
        $errMsg = "error !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"; // .mysql_error()
        return array('outp' => $outp, 'errMsg' => $errMsg);
    }

    $conn->close();
    //echo $outp;

    return array('outp' => $outp, 'errMsg' => $errMsg, 'fgwArray' => $fgwArray);
}

function getSummary($from, $to, $filter, $dbhost, $nbrecs)
{
    $outp = "";
    $errMsg = "";

    $query = "
    SELECT fgw_host,fgw_title,SUM(fgw_duration) AS duration
    FROM fgw
    WHERE
    fgw_title like '%" . $filter . "%' and
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

            if ($outp != "") {$outp .= ",";}
            $host = $row["fgw_host"];
            $title = $row["fgw_title"];
            if ($title == null) {$title = "";}

            $duration = $row["duration"];

            $outp .= '{';

            $outp .= '"host":"' . $host . '",';
            $outp .= '"title":"' . utf8_encode($title) . '",';
            $outp .= '"duration":"' . $duration . '",';
            $outp .= '"dur_min":"' . number_format(strval($duration / 60), 0) . '"';
            $outp .= '}';

            $myfgw = new Fgw("", "", $host, utf8_encode($title), number_format(strval($duration / 60), 0));
            $fgwArray[] = $myfgw;
        }
    } else {
        $errMsg = "error !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"; // .mysql_error()
        return array('outp' => $outp, 'errMsg' => $errMsg);
    }

    $conn->close();
    //echo $outp;

    return array('outp' => $outp, 'errMsg' => $errMsg, 'fgwArray' => $fgwArray);
}

function getDailySummary($from, $to, $filter, $dbhost, $nbrecs, $order)
{

    //$order = "duration desc"
    $outp = "";
    $errMsg = "";

    $query = "
    SELECT date(fgw_time) as date, fgw_host, fgw_title,SUM(fgw_duration) AS duration, SUM(fgw_duration)/60 AS duration_min
    FROM fgw
    WHERE
    fgw_title is not null and
    fgw_title <> '' and
    " . //fgw_title like '%".$filter."%' and
    "fgw_time >=" . $from . " and
    fgw_title like '%" . $filter . "%' and
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

        $fgwArray = array();
        while ($row = $result->fetch_assoc()) {

            if ($outp != "") {$outp .= ",";}

            $host = $row["fgw_host"];
            $title = $row["fgw_title"];if ($title == null) {$title = "";}
            $date = $row["date"];
            $duration = $row["duration"];

            //echo "duration : ".$duration."  title : -----".$title."++++++\n";

            $outp .= '{';

            $outp .= '"date":' . json_encode($date) . ',';
            $outp .= '"host":"' . $host . '",';
            $outp .= '"title":"' . utf8_encode($title) . '",';
            $outp .= '"duration":"' . number_format(strval($duration / 60), 0) . '"';
            $outp .= '}';

            //echo $outp."\n\n";

            $myfgw = new Fgw("", $date, $host, utf8_encode($title), number_format(strval($duration / 60), 0));
            $fgwArray[] = $myfgw;
        }
    } else {
        $errMsg = "error !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"; // .mysql_error()
        return array('outp' => $outp, 'errMsg' => $errMsg);
    }

    $conn->close();
    //echo "-------------->".$outp;

    //var_dump($fgwArray);

    return array('outp' => $outp, 'errMsg' => $errMsg, 'fgwArray' => $fgwArray);
}

/**
 * install tables
 *
 * @param ModuleContextInterface $context
 * @return 
 * @getDailySummaryTotal()
 */
    
function getDailySummaryTotal($from, $to, $filter, $dbhost, $nbrecs, $order)
{

    //$order = "duration desc"
    $outp = "";
    $errMsg = "";

    $query = "
    SELECT date(fgw_time) as date, fgw_title, SUM(fgw_duration) AS duration, SUM(fgw_duration)/60 AS duration_min
    FROM fgw
    WHERE
    fgw_title is not null and
    fgw_title <> '' and
    fgw_title in (" . $filter . ") and
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

    //$outp = "";

    if ($result->num_rows > 0) {
        // output data of each row
        $fgwArray = array();

        while ($row = $result->fetch_assoc()) {
            
            /*
            if ($outp != "") {$outp .= ",";}
            $id = "";
            $title = "";
            $date = $row["date"];
            $duration = $row["duration"];
            $outp .= '{';
            $outp .= '"id"		:' . json_encode($id) . ',';
            $outp .= '"time"	:' . json_encode($date) . ',';
            $outp .= '"title"	:' . json_encode(utf8_encode($title)) . ',';
            $outp .= '"duration":"' . number_format(strval($duration / 60), 0) . '"';
            $outp .= '}';
            */

            //$myfgw = new Fgw("", $row["date"], "", $row["fgw_title"], number_format(strval($row["duration"]/ 60), 0));
            $myfgw = new Fgw("", $row["date"], "", "", number_format(strval($row["duration"]/ 60), 0));
            $fgwArray[] = $myfgw;
        }
    } else {
        echo "!!!!!!! 0 results";
    }
    $conn->close();
    //echo "test :". $outp."<br>errMsg: " . $errMsg."\n\n\n";
    //var_dump($fgwArray);  
    //return array('outp' => $outp, 'errMsg' => $errMsg, 'fgwArray' => json_encode($fgwArray));
    //return array('outp' => $outp, 'errMsg' => $errMsg, 'fgwArray' => $fgwArray);

    //add records with a zero duration for the days where there has been no records, in order to have a correct graph
    $fgwArray2 = array();
    if (count($fgwArray >=1)) {
        $fgwArray2[] = $fgwArray[0];
        $prevDate = $fgwArray2[0]->time;
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
            //echo "thisDate : " . $thisDate . "<br>\n";
            //echo "prev   : " . $prevDate . "<br>\n";
            //echo "prev+1 : " . date('Y-m-d', strtotime($prevDate.' +1 day')) . "<br>\n";
            $j=0;
            while (($thisDate <> date('Y-m-d', strtotime($prevDate.' +1 day'))) and ($j<1000) ) {
                //echo "while " . $j . "   thidate : ".$thisDate . "    prev+1 : " . date('Y-m-d', strtotime($prevDate.' +1 day')) . "<br>\n";
                //echo "adding " . date('Y-m-d', strtotime($prevDate.' +1 day'))."\n";
                $fgwArray2[] = new Fgw("", date('Y-m-d', strtotime($prevDate.' +1 day')), "", "", 0);
                $prevDate=date('Y-m-d', strtotime($prevDate.' +1 day'));
                //echo "prevDate : " . $prevDate . "<br>\n\n";
                $j+=1;
            }
            $fgwArray2[] = new Fgw("", $myfgw->time, "", "", $myfgw->duration);
            $prevDate=date('Y-m-d', strtotime($prevDate.' +1 day'));
            //echo "adding 2 " . $myfgw->time . "\n";
            $i+=1;
        }
    }

    return array('errMsg' => $errMsg, 'fgwArray' => $fgwArray2);
}

function checkCharSet($conn)
{
    echo "<!DOCTYPE html>";
    echo "<html>";
    echo "before\n";
    $re = mysql_query('SHOW VARIABLES LIKE "%character_set%";') or die(mysql_error());
    while ($r = mysql_fetch_assoc($re)) {var_dump($r);
        echo "<br />";}
    echo "---------------------------------------------------<br />\n";

    mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

    $re = mysql_query('SHOW VARIABLES LIKE "%character_set%";') or die(mysql_error());
    while ($r = mysql_fetch_assoc($re)) {var_dump($r);
        echo "<br />";}
    echo "after\n";
}

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

//----------------------------------------------------
// main

$from = "'2000-01-01'";
if (isset($_GET['from'])) {$from = $_GET['from'];}

$to = "'2099-01-01'";
if (isset($_GET['to'])) {$to = $_GET['to'];}

$filter = "%";
if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
    //remove starting and eding quoted_printable_decode
    //echo "####### filter passed in http request ####################\n";
    //print_r($filter);
    //echo "\n";
    //$filter = get_string_between($filter,"'","'");
    //echo "####### filter trimmed ####################\n";
    //print_r($filter);
    //echo "\n";
}

$myFunc = "details";
if (isset($_GET['myFunc'])) {$myFunc = $_GET['myFunc'];}

$nbrecs = "5";
if (isset($_GET['nbrecs'])) {$nbrecs = $_GET['nbrecs'];}

if (isset($_GET['dbhost'])) {$dbhost = $_GET['dbhost'];}

$order = "duration desc";
if (isset($_GET['order'])) {$order = $_GET['order'];}

//checkCharSet($conn);
//echo "this is a test 2";

if ($myFunc == "details") {
    $myArray = getDetails($from, $to, $filter, $dbhost, $nbrecs);
} elseif ($myFunc == "summary") {
    $myArray = getSummary($from, $to, $filter, $dbhost, $nbrecs);
    $outp = json_encode($myArray['fgwArray']);
} elseif ($myFunc == "dailySummary") {
    $myArray = getDailySummary($from, $to, $filter, $dbhost, $nbrecs, $order);
    //$outp = $myArray['outp'];
    $errMsg = $myArray['errMsg'];
    //$fgwArray = $myArray['fgwArray'];
    $outp = json_encode($myArray['fgwArray']);
    //echo "fgwArray_js : ".$fgwArray."<br>\n";
} elseif ($myFunc == "dailySummaryTotal") {
    $myArray = getDailySummaryTotal($from, $to, $filter, $dbhost, $nbrecs, $order);
}

//echo "myArray : ".$myArray;

//if (($myFunc == "dailySummary") or ($myFunc == "summary") or ($myFunc == "details") or ($myFunc == "dailySummaryTotal")) {
    $outp = json_encode($myArray['fgwArray']);
/*
} else {
    $outp = '[' . $myArray['outp'] . ']';
    echo "output1 OK :" . $outp . "\n\n";
    $outp = json_encode($myArray['fgwArray']);
    echo "output2 NOK :" . $outp . "\n\n";
}
*/

$errMsg = $myArray['errMsg'];

$outp = '{"records":' . $outp;
$outp = $outp . ',"errMsg":"' . $errMsg . '"';
$outp = $outp . ',"from":"' . $from . '"';
$outp = $outp . ',"to":"' . $to . '"';
$outp = $outp . '}';

if ($myFunc == "xxxxxdailySummary") {
    //echo "fgwArray_js : ".json_encode($fgwArray)."<br>\n";
    echo json_encode($fgwArray);
} else {
    echo ($outp);
}
