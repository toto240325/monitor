<script>
//console.log ("test99");
</script>

<?php

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
//include 'getGamesTodayData.php';

//echo "webserver: ".$webserver."<br>\n";
//echo "dbhost : ".$dbhost."<br>\n";

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

$currTime = _date("Y-m-d H:i:s", false, 'Europe/Paris');

function displayRawTimes($timesArray)
{
    $i = 0;
    foreach ($timesArray as $time) {
        echo $i . " " . $time . "<br>";
        $i += 1;
    }
}

//----------------------------------------------------------------------------------
/*  makes an array of which the elements are a datetime and a value; the datetimes go from $from to $to
in increment of $period minutes; the value will contain the nb of times a detection has taken place
within the corresponding period
 */
function makeGraphDataFromDetections($from, $to, $period)
{
    $myGraphData = array();
    $currentDateTime = new DateTimeImmutable($from);
    $toDateTime = new DateTimeImmutable($to);
    while ($currentDateTime <= $toDateTime) {
        //echo $currentDateTime->format('Y-m-d H:i:s')."<br>";
        $currentDateTimeStr = $currentDateTime->format('Y-m-d H:i:s');
        $elem = array('datetime' => $currentDateTimeStr, 'nbDetections' => "0");
        $myGraphData[] = $elem;
        $currentDateTime = $currentDateTime->modify('+' . $period . ' minutes');
    }
    return $myGraphData;
}
//----------------------------------------------------------------------------------
/*  makes an array of which the elements are a datetime and a value; the datetimes go from $from to $to
in increment of $period minutes; the value will contain the nb of times a detection has taken place
within the corresponding period

input : [{"date":"2016-09-17","title":"Agar.io - Google Chrome","duration":"57"},{"date":"2016-09-16","title":"Agar.io - Google Chrome","duration":"54"}]
output : [{"x":"2016-09-14 00:00:00","y":1},{"x":"2016-09-15 00:30:00","y":7},{"x":"2016-09-16 01:00:00","y":10}]

 */
function makeGraphDataFromAgario($records)
{

    $myGraphData = array();
    foreach ($records as $rec) {
        //echo "<br>\n rec : ".var_dump($rec)."<br>\n";
        //echo "<br>\n rec_time : ".$rec->time."<br>\n";
        //echo "<br>\n rec_duration : ".$rec->duration."<br>\n";
        //echo "<br>\n rec_title : ".$rec->title."<br>\n";

        $elem = array('x' => $rec->time, 'y' => (int) $rec->duration);
        $myGraphData[] = $elem;
    }
    return $myGraphData;
}
//----------------------------------------------------------------------------------
function displayGraphData($graphData)
{
    $i = 0;
    foreach ($graphData as $elem) {
        echo $i . " " . $elem['datetime'] . "  " . $elem['nbDetections'] . "<br>";
        $i += 1;
    }
}
//----------------------------------------------------------------------------------
/*  convertGraphDataToGoogleGraph :
input : [{"datetime":"2016-09-14 00:00:00","nbDetections":1},{"datetime":"2016-09-14 00:30:00","nbDetections":"0"},{"datetime":"2016-09-14 01:00:00","nbDetections":"0"}]
output : [{"x":"2016-09-14 00:00:00","y":1},{"x":"2016-09-14 00:30:00","y":0},{"x":"2016-09-14 01:00:00","y":0},{"x":"2016-09-14 01:30:00","y":0},{"x":"2016-09-14 02:00:00","y":0},{"x":"2016-09-18 00:00:00","y":0}]
 */
function convertGraphDataToGoogleGraph($graphData)
{
    //echo "input to convertGraphDataToGoogleGraph: \n<br>".json_encode($graphData)."\n<br>";
    //        var_dump($graphData);
    //echo json_encode($graphData)."\n<br>";
    $rows = array();
    foreach ($graphData as $elem) {
        $x = $elem['datetime'];
        $y = $elem['nbDetections'];
        $myRow = array('x' => (string) ($x), 'y' => (int) ($y));
        $rows[] = $myRow;
    }
    //echo "output of convertGraphDataToGoogleGraph: \n<br>".json_encode($rows)."\n<br>";
    return $rows;
}
//----------------------------------------------------------------------------------
/* fills the empty graphData (containing only the timeslots) with the raw data in $timesArray
Note that timesArray must be sorted by datetime
input  : [{"datetime":"2016-09-14 00:00:00","nbDetections":"0"},{"datetime":"2016-09-14 00:30:00","nbDetections":"0"},{"datetime":"2016-09-14 01:00:00","nbDetections":"0"}]
output : [{"datetime":"2016-09-14 00:00:00","nbDetections":1},{"datetime":"2016-09-14 00:30:00","nbDetections":"0"},{"datetime":"2016-09-14 01:00:00","nbDetections":"0"}]
 */

function completeGraphData($graphData, $timesArray, $period)
{
    //echo "graphdata as input : \n<br>".json_encode($graphData)."\n<br>";
    //echo "timesarray : \n<br>"; var_dump ($timesArray);

    $i = 0;
    $nbTimesArray = count($timesArray);
    foreach ($graphData as &$elem) {
        //echo "test 1 --- ".$i." ".$elem['datetime']." ---".$nbTimesArray."----\n<br>";
        while ($i < $nbTimesArray) {
            //echo "test 2--- ".$timesArray[$i]."----\n<br>"; var_dump ($timesArray[$i]);
            $detectionTime = new DateTimeImmutable($timesArray[$i]);
            $graphSlotStart = new DateTimeImmutable($elem['datetime']);
            $graphSlotEnd = $graphSlotStart->modify('+' . $period . ' minutes');
            //var_dump($graphSlotStart, $detectionTime,$graphSlotEnd);
            $isComprisedInCurrentPeriod = (($detectionTime >= $graphSlotStart) and ($detectionTime <= $graphSlotEnd));
            //var_dump($isComprisedInCurrentPeriod);
            //var_dump($graphSlotEnd->format('Y-m-d H:i:s'));
            if ($isComprisedInCurrentPeriod) {
                $elem['nbDetections'] += 1;
                echo $i . " one detection added <br>";
                $i += 1;
            } else {
                break;
            }
        }
    }
    //echo "graphdata as output : \n<br>".json_encode($graphData)."\n<br>";
    return $graphData;
}
//----------------------------------------------------------------------------------
function getLastGetWindowTitleMypc3()
{
    global $dbhost;
    include 'connect-db.php';
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $mydb);

    if (!$conn) {
        die('Could not connect: ' . mysqli_error($conn));
    }
    $query = "SELECT * from fgw order by fgw.fgw_id desc limit 1";
    $result = mysqli_query($conn, $query) or die('Error, query failed');
    if (mysqli_num_rows($result) == 0) {
        echo "Database is empty <br>";
    } else {
        $time = "";
        $host = "";
        $title = "";
        echo "nb results getWindowTitle : " . mysqli_num_rows($result) . "<p>";
        while ($row = $result->fetch_assoc()) {
            $time = $row['fgw_time'];
            $host = $row['fgw_host'];
            $title = $row['fgw_title'];
        }
    }
    mysqli_close($conn);
    return $time;
}

//---------------------------------------------------------------------------------
/* this function return raw (x,y) data in this format (but not json encoded !):
[{"x":"2016-08-01 00:00:06","y":98},
{"x":"2016-08-01 00:00:06","y":97},
{"x":"2016-08-01 00:00:06","y":97},
{"x":"2016-06-21 22:45:44","y":43}]
 */
function getTemperatureData()
{
    global $dbhost;
    include 'connect-db.php';
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $mydb);

    if (!$conn) {
        die('Could not connect: ' . mysqli_error($conn));
    }
    $query = "SELECT * from temp order by temp_time desc limit 25";
    $result = mysqli_query($conn, $query) or die('Error, query failed');
    if (mysqli_num_rows($result) == 0) {
        echo "Database is empty <br>";
    } else {
        $rows = array();
        //echo "nb results2 : ".mysqli_num_rows($result)."<p>";
        while ($row = $result->fetch_assoc()) {
            $time = $row['temp_time'];
            $temp = $row['temp_temp'];
            $myRow = array('x' => (string) ($time), 'y' => (int) ($temp));
            //$myRow[] = array('x' => (string) ($time));
            //$myRow[] = array('y' => (int) $temp);
            $rows[] = $myRow;
        }
        //$dataArray = json_encode($rows);
    }
    mysqli_close($conn);
    return $rows;
}

//---------------------------------------------------------------------------------
/* this function takes data in this format (but not json encoded) :
[{"x":"2016-08-01 00:00:06","y":98},
{"x":"2016-08-01 00:00:06","y":97},
{"x":"2016-08-01 00:00:06","y":97},
{"x":"2016-06-21 22:45:44","y":43}]
and returns a json_encoded string in a format compatible with googleGraph, like this :

{"cols":[
{"label":"Date","type":"string"},
{"label":"temp","type":"number"}
],
"rows":[
{"c":[{"v":"2016-08-01 00:00:06"},{"v":98}]},
{"c":[{"v":"2016-08-01 00:00:06"},{"v":97}]},
{"c":[{"v":"2016-08-01 00:00:06"},{"v":97}]},
{"c":[{"v":"2016-06-21 22:45:44"},{"v":43}]}
]
}
 */
function formatGraphData($dataArray,$yAxisStr)
{
    //echo json_encode($dataArray);
    //var_dump($dataArray);
    $table = array();
    $table['cols'] = array(
        array('label' => 'Date', 'type' => 'date'),
        array('label' => $yAxisStr, 'type' => 'number'),
    );

    $rows = array();
    foreach ($dataArray as $row) {
        $x = $row['x'];
        $y = $row['y'];
        //echo "x,y : ".$x.",".$y."<br>";
        $myRow = array();
        $yyyy = substr($x, 0, 4);
        $MM = substr($x, 5, 2);
        $dd = substr($x, 8, 2);
        $hh = substr($x, 11, 2);
        $mm = substr($x, 14, 2);
        $ss = substr($x, 17, 2);
        $myRow[] = array('v' => 'Date(' . $yyyy . ', ' . (((int) $MM) - 1) . ', ' . $dd . ', ' . $hh . ', ' . $mm . ', ' . $ss . ')');
        $myRow[] = array('v' => (int) $y);
        $rows[] = array('c' => $myRow);
    }
    $table['rows'] = $rows;
    $graphData = $table;
    return $graphData;
}

function getLokiGraphData()
{
    global $webserver;

    // prepare Loki Eating habits graph ------------------------------------------------------------------------------

    $to = date('Y-m-d');
    $fromDate = new DateTime($to);
    $fromDate->modify('-4 day');
    $from = $fromDate->format('Y-m-d');
    //echo "40 from - to : <br>";
    //echo $from." - ".$to."<br>";

    $fromDate = new DateTime(date('Y-m-d'));
    $fromDate->modify('-5 day');
    $from = $fromDate->format('Y-m-d');

    $toDate = new DateTime(date('Y-m-d'));
    $toDate->modify('+1 day');
    $to = $toDate->format('Y-m-d');

    // prepare Loki graph ---------------------------------------------------------------------------

    $myFunc = "2";
    $period = 30;

    $mypage45 = "http://" . $webserver . "/loki/getDetectionTimes.php?myFunc=9&from='" . $from . "'&to='" . $to . "'&period=" . $period;
    //echo "my page 45 : ".$mypage45."<br>";
    $json = file_get_contents($mypage45);
    //echo "json7 :\n<br>"; var_dump($json);
    //echo "\n<br>-----------------------\n<br>";

    $obj = json_decode($json);
    //echo "count : ".count($obj->records)."<br>";
    $timesArray = $obj->records;
    $graphData = makeGraphDataFromDetections($from, $to, $period);
    //echo "\n timesArray 7 : \n"; var_dump($timesArray);
    $graphData = completeGraphData($graphData, $timesArray, $period);

    $GGdata = convertGraphDataToGoogleGraph($graphData);
    //echo "GGdata1 : \n<br>".json_encode($GGdata)."\n<br>";

    return json_encode(formatGraphData($GGdata, "temp"));
}


function getGamesGraphData()
{
    global $dbhost;
    global $thisServer;
    global $inTitleList;
    global $myPageAgarioAndOtherGames;
    
    // prepare Agario graph -----------------------------------------------------------------------------
    $fromDateAgar = new DateTime(date('Y-m-d'));
    $fromDateAgar->modify('-10 day');
    $fromAgar = $fromDateAgar->format('Y-m-d');

    $toDateAgar = new DateTime(date('Y-m-d'));
    $toDateAgar->modify('+1 day');
    $toAgar = $toDateAgar->format('Y-m-d');

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

    //echo "<br><br>=========================================<br>".$myPageAgarioAndOtherGames."<br>";
    //echo "fromAgar : ".$fromAgar."  toAgar : ".$toAgar."<br>";
    //echo "mypage Agario2 : ".$myPageAgarioAndOtherGames."<br>";
    
    
    //echo "<script>";
    //echo 'console.log("mypage Agario2 : ' . $myPageAgarioAndOtherGames . '")';
    //echo "</script>";

    //echo "<script>";
    //echo 'console.log("dbhost ' . $dbhost . '")';
    //echo "</script>";

    $json = file_get_contents($myPageAgarioAndOtherGames);

    //echo "json 34 : "."<br>";
    //print_r($json);
    //echo "test 34<br>\n"; var_dump($json);
    $obj = json_decode($json);

    if ($obj->errMsg != "") {

        // maybe it's normal and the result is just empty
        //echo "!!!!!!!!!!!!!!! Error getting data in ".$myPageAgarioAndOtherGames."!!! ";

    } else {
        //echo "count : ".count($obj->records)."<br>";
        //$timesArray = $obj->records;

        //echo json_encode($obj->records)."<br>";

        //echo "<br>records 2 : ".json_encode($obj->records)."<br>";
        $graphData = makeGraphDataFromAgario($obj->records);
        //echo "<br>graphData2 : ".json_encode($graphData)."<br>";

        //echo "graphData : ".json_encode($graphData)."<br>";
        //echo "<br>";
        //$graphData = json_decode('[{"datetime":"2016-09-14 00:00:00","nbDetections":1},{"datetime":"2016-09-15 00:30:00","nbDetections":"0"},{"datetime":"2016-09-16 01:00:00","nbDetections":"0"}]');
        //$GGdata = convertGraphDataToGoogleGraph($graphData);
        //echo "GGdata4 : \n<br>".json_encode($GGdata)."\n<br>";
        //    $GGdata = json_decode('[{"x":"2016-09-14 00:00:00","y":1},{"x":"2016-09-15 00:30:00","y":7},{"x":"2016-09-16 01:00:00","y":10}]',true);

        $GGdata = $graphData;

        //echo "GGdata2 : \n<br>".json_encode($GGdata)."\n<br>";
        //echo "formatGraphData  : \n<br>";
        //echo json_encode(formatGraphData($GGdata))."\n<br>";
    }
    return json_encode(formatGraphData($GGdata, "Duration (Mins)"));
 
}


$jsonTable3 = getLokiGraphData();

$jsonTableAgarioAndOtherGames = getGamesGraphData();
//echo "jsonTableAgarioAndOtherGames  : \n<br>";
//echo $jsonTableAgarioAndOtherGames."\n<br>";

//$jsonGamesTodayData = getGamesTodayData();
//echo "jsonGamesTodayData : \n<br>";
//echo $jsonGamesTodayData."\n<br>";
//$obj = json_decode($jsonGamesTodayData);
//echo "count : ".count($obj->records)."<br>";
//$durationGames = $obj->records[0]->duration;


$to = date('Y-m-d');
$fromDate = new DateTime($to);
$fromDate->modify('-4 day');
$from = $fromDate->format('Y-m-d');

?>

<!--<!DOCTYPE html>
-->
<html>
    <style>
        table, th, td {
        border: 1px solid grey;
        border-collapse: collapse;
        padding: 5px;
        }
        table tr:nth-child(odd) {
        background-color: #f1f1f1;
        }
        table tr:nth-child(even) {
        background-color: #ffffff;
        }
    </style>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>   
    <script>
        var webserver = "<?php echo $webserver; ?>";
        var dbhost = "<?php echo $dbhost; ?>";

        var app = angular.module('myApp', []);
        app.controller('myCtrl', function($scope, $http,$location,$filter,$interval) {


            $scope.staticNow = new Date();
            $scope.staticNowTimeStr = (new Date()).toLocaleTimeString("fr-BE", {hour12: false});
            $scope.currentDateStr = (new Date()).toLocaleDateString("fr-BE", {hour12: false});

            function displayCurrentDate() {
                $scope.now = new Date();
                $scope.currentTimeStr = (new Date()).toLocaleTimeString("fr-BE", {hour12: false});
                $scope.currentDateStr = (new Date()).toLocaleDateString("fr-BE", {hour12: false});
            };
            displayCurrentDate();

            $interval(displayCurrentDate, 10*1000);

            $scope.myStyleLastBackup = function(){
                $scope.backupDiffInMin = ($scope.staticNow - (new Date($scope.eventsArray["backup P702"]))) / (60*1000);
                $scope.backupDiffInMin = $scope.backupDiffInMin.toFixed(2);
                return parseInt($scope.backupDiffInMin) > 25 * 60 ? {'background-color': 'pink'} : {'background-color': 'lightgreen'}
            }

            $scope.myStyleLastTemp= function(){
                $scope.tempDiffInMin = ($scope.staticNow - (new Date($scope.lastTemp))) / (60*1000*60);  // hours
                $scope.tempDiffInMin = $scope.tempDiffInMin.toFixed(2);
                return parseInt($scope.tempDiffInMin) > 5 ? {'background-color': 'pink'} : {'background-color': 'lightgreen'}
            }

            $scope.myStyleLastDetection= function(){
                $scope.DetectionDiffInMin = ($scope.staticNow - (new Date($scope.lastDetectionTime))) / (60*1000); //min
                $scope.DetectionDiffInMin = $scope.DetectionDiffInMin.toFixed(0);
                return parseInt($scope.DetectionDiffInMin) > 3*60 ? {'background-color': 'pink'} : {'background-color': 'lightgreen'}
            }

            $scope.myStyleLastEvent = function($type,$time){
                $scope.DetectionDiffInMin = ($scope.staticNow - (new Date($time))) / (60*1000); //min
                $scope.DetectionDiffInMin = $scope.DetectionDiffInMin.toFixed(0);
                //console.log("type : ",$type," time: ",$time, "diffinmin : ", $scope.DetectionDiffInMin);
                if ($type == "backup P702") {
                    //return parseInt($scope.DetectionDiffInMin) > 2 ? {'background-color': 'pink'} : {'background-color': 'lightgreen'}
                    return parseInt($scope.DetectionDiffInMin) > 28*60 ? {'background-color': 'pink'} : {'background-color': 'lightgreen'}
                } else if ($type == "getWindowTitle mypc3") {
                    return parseInt($scope.DetectionDiffInMin) > 3*60 ? {'background-color': 'pink'} : {'background-color': 'lightgreen'}
                } else {
                    return parseInt($scope.DetectionDiffInMin) > 3*60 ? {'background-color': 'pink'} : {'background-color': 'lightgreen'}
                }

            }
             $scope.lokiEatingURL = "http://"+webserver+"/loki/eating_log.php";
            $scope.showLogURL = "http://"+webserver+"/loki/showlog.php";

            /*
                $scope.myCount = 0;
                $scope.myTest = function() {
                $scope.myCount +=3;
                }
                $interval(function () {
                $scope.myTest();
                }, 2*1000);
            */

            //console.log("test toto");
            //alert("test alert")
            $scope.myURL = $location.absUrl();

            $scope.count = 0;
            $scope.myFunction = function() {
                $scope.count++;
            }



//   $to = date('Y-m-d');
//    $fromDate = new DateTime($to);
//    $fromDate->modify('-4 day');
//    $from = $fromDate->format('Y-m-d');

            $scope.myTest = "initial test";

            $scope.example = {
                value: new Date(2013, 9, 22)
            };
            $scope.myFrom2 = (new Date());
            $scope.myFrom2.setHours(0,0,0,0);
            $scope.myFrom = (new Date());
            $scope.myFrom.setHours(0,0,0,0);
            $scope.myTo = (new Date());
            $scope.myTo.setHours(0,0,0,0);
            $scope.myTo.setDate($scope.myTo.getDate()+1);
            

            $scope.from = (new Date()).toLocaleDateString("fr-BE", {hour12: false});
            $scope.from = (new Date());
            //$scope.to = (new Date().toLocaleDateString("fr-BE", {hour12: false});
            $scope.to = "2099-12-31";
            $scope.to = new Date();
            $scope.to.setDate($scope.to.getDate()+1);



            $scope.hostFilter = "";
            $scope.titleFilter = "";
            $scope.myFunc = "dailySummary";
            $scope.dbhost = dbhost;
            $scope.nbrecs = "15";
            $scope.testdata = "test";
            $scope.testdata2 = new Array("toto", "tutu");
            $scope.testdata2["toto"] = "datatoto";

            $scope.testfct = function($myArray,$index, $val) {
                $myArray[$index] = $val;
            }
            $scope.testfct($scope.testdata2,"toto","toto2");
            $scope.testfct($scope.testdata2,"tutu","tutu2");


            $scope.httpError = "";
            $scope.convStrToDate = function($from) {
                $dd = $from.substring(0,2);
                $mm = $from.substring(3,5);
                $yyyy = $from.substring(6,10);
                $fromDate = new Date($yyyy + "-" + $mm + "-" + $dd);
                return $filter('date')($fromDate,'yyyy-MM-dd');
            }


            Date.prototype.yyyymmdd = function() {
            var mm = this.getMonth() + 1; // getMonth() is zero-based
            var dd = this.getDate();

            return [this.getFullYear(),
                    (mm>9 ? '' : '0') + mm,
                    (dd>9 ? '' : '0') + dd
                    ].join('/');
            };

            var date = new Date();
            //console.log("my date : " + date.yyyymmdd());

            $scope.prevDate = function() {
                milsInADay = 24*60*60*1000;
                $scope.myFrom = new Date($scope.myFrom.getTime()-milsInADay);
                $scope.myTo = new Date($scope.myTo.getTime()-milsInADay);
                $scope.getResults();
            }


            $scope.onInput = function() {
                $scope.getResults();
                }


            $scope.nextDate = function() {
                milsInADay = 24*60*60*1000;
                $scope.myFrom = new Date($scope.myFrom.getTime()+milsInADay);
                $scope.myTo = new Date($scope.myTo.getTime()+milsInADay);
                $scope.getResults();
            }
            $scope.getResults = function() {
                
                $myFromStr = $scope.myFrom
                
                //$scope.myPage35 = "http://"+webserver+"/monitor/getWindowResult.php" +
                $scope.myPage35 = "getWindowResult.php" +
                "?from='" + $scope.myFrom.yyyymmdd() + "'" +    // $scope.convStrToDate($scope.from) + "'" +
                //"?from='" + $titleFilter('date')($scope.fromDate,'yyyy-MM-dd') + "'" +
                "&to='"+ $scope.myTo.yyyymmdd() + "'" +
                "&hostFilter="+ $scope.hostFilter +
                "&titleFilter="+ $scope.titleFilter +
                "&dbhost="+ $scope.dbhost +
                "&nbrecs="+ $scope.nbrecs +
                "&order=duration+desc" +
                "&myFunc="+ $scope.myFunc;
                //alert("myPage35 : "+$scope.myPage35);
                console.log("myURL35: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage35);
                $http.get($scope.myPage35)
                .then(
                function(response) {
                    $scope.fgw = response.data.records;
                    $scope.errorMsg = response.data.errMsg;
                    //alert("error message 34 : " + response.data.errMsg)
                },
                function(failure) {
                    //Second function handles error
                    $errorMsg = "Error in getResults : " + failure;
                    console.log("error 35 : "+$errorMsg);
                    alert("error message 35 : " + $errorMsg);
                });
            }
            $scope.getResults();

            $scope.getLastTemp = function() {
                //$scope.myPage36 = "http://"+webserver+"/monitor/getEvent.php?dbhost="+ $scope.dbhost+"&type=temperature";
                //$scope.myPage36 = "getEvent.php?dbhost="+ $scope.dbhost+"&type=temperature";
                $scope.myPage36 = "getEvent.php?eventFct=getLastEventByType&dbhost=" + $scope.dbhost+"&type=temperature";              
                //console.log("myURL36: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage36);
                $http.get($scope.myPage36)
                .then(
                function(response) {
                    $scope.lastTemp = response.data.records[0].time;
                    $scope.lastDetectionTime = response.data.records[0].time;
                    $scope.lastDetectionTemp = response.data.records[0].text;
                },
                function(failure) {
                    $errorMsg = "Error in getEvent 36 : " + failure;
                    console.log($errorMsg);
                    alert("error msg 36 : " + $errorMsg);
                });
            }
            $scope.getLastEvent = function($myArray,$type) {
                //$myURL = "http://"+webserver+"/monitor/getEvent.php?type="+$type;
                $myURL = "getEvent.php?type="+$type+"&eventFct=getLastEventByType";
                //alert($myURL);
                //console.log("myURL37: http://<?php echo $thisServer ?>/monitor/" +$myURL);
                $http.get($myURL)
                .then(
                function(response) {
                    //$lastEventTime = response.data.time;
                    //$lastDetectionTxt = response.data.text;
                    //alert(response.data.text);
                    $myArray[$type] = response.data.records[0].time;
                },
                function(failure) {
                    $errorMsg = "Error in getLastEvent3 (for type " + $type + ") : " + failure;
                    console.log($errorMsg);
                    alert("error msg 37 : " + $errorMsg);
                });

            }
            $scope.getLastEventGetWindowTitleMypc3 = function($myArray,$type) {
                //$myURL = "http://"+webserver+"/monitor/getLastTimeWindowTitle.php";
                $myURL = "getLastTimeWindowTitle.php";
                //alert("myurl 38 : " + $myURL);
                console.log("myurl 38 : " + $myURL);
                $http.get($myURL)
                .then(
                function(response) {
                    $lastEventTime = response.data.time;
                    $lastDetectionTitle = response.data.title;
                    $myArray[$type] = $lastEventTime;
                },
                function(failure) {
                    $errorMsg = "Error in getLastEventGetWindowTitleMypc3 : " + failure;
                    console.log($errorMsg);
                    alert("error msg 38 : " + $errorMsg);
                });

            }
            $scope.getLastTemp();

            $scope.eventsArray = [];
            $scope.eventsArray["1"] = "init1";
            $scope.eventsArray["backup P702"] = "";
            $scope.eventsArray["getWindowTitle mypc3"] = "";

            $scope.getLastEvent($scope.eventsArray,"1");
            $scope.getLastEvent($scope.eventsArray,"backup P702");
            $scope.getLastEventGetWindowTitleMypc3($scope.eventsArray,"getWindowTitle mypc3");

        });

    </script>

    <!-- graph for Agar.io -->
    <script type="text/javascript">

        // Load the Visualization API and the piechart package.

        google.charts.load('current', {'packages': ['corechart','timeline'] });

        // Set a callback to run when the Google Visualization API is loaded.
        //var from = "<    ?php echo $from?>";
        //var to = "<     ?php echo $to?>";

        Date.prototype.addHours = function(hours) {
            var dat = new Date(this.valueOf())
            dat.setHours(dat.getHours() + hours);
            return dat;
        }

        Date.prototype.addDays = function(days) {
            var dat = new Date(this.valueOf())
            dat.setDate(dat.getDate() + days);
            return dat;
        }

        var to = new Date().toJSON().slice(0,10).replace(/-/g,'/');
        var from = new Date().addDays(-4);

        google.charts.setOnLoadCallback(function() { drawChartAgario(from,to); });
        function drawChartAgario(from,to) {

            function getDates(startDate, stopDate) {
                var dateArray = new Array();
                var currentDate = startDate;
                while (currentDate <= stopDate) {
                    dateArray.push(currentDate)
                    currentDate = currentDate.addHours(2);
                    //currentDate = currentDate.addDays(1);
                }
                return dateArray;
            }

            /*
                var d = new Date("2016/08/30");
                console.log("test33 "+ d);
                console.log("test33 "+ d.addDays(1) + "----" + (d.getDate() + 1) + "----" + d.setDate(d.getDate() + 1));
            */
            var dateArray = getDates(new Date("<?php echo $from?>"), (new Date("<?php echo $to?>")));
            //var dateArray = getDates(new Date(2016,07,24), (new Date(2016,07,30)));

            // Create our data table out of JSON data loaded from server.
            var data = new google.visualization.DataTable(<?php echo $jsonTableAgarioAndOtherGames?>);
            var options = {
                title: 'Agar.io graph',
                is3D: 'true',
                width: 1000,
                height: 400,

                hAxis: {
                    //ticks: dateArray,
                    gridlines: {count: 15},
                    format: 'd/M HH:mm'
                }
                //hAxis: { ticks: [new Date(2016,8,26), new Date(2016,8,27)] }
            };
            // Instantiate and draw our chart, passing in some options.
            // Do not forget to check your div ID
            var chart = new google.visualization.AreaChart(document.getElementById('chart_agario'));
            chart.draw(data, options);

        }
    </script>

  <!-- graph for Loki eating's habits -->
    <script type="text/javascript">

        // Load the Visualization API and the piechart package.
        google.charts.load('visualization', '1', {'packages':['corechart','timeline']});

        // Set a callback to run when the Google Visualization API is loaded.
        // var mypageAgario = "<  ?=$myPageAgarioAndOtherGames?>";
        /*
        var from = "<  ?=$from?>";
        var to = "<  ?=$to?>";
        */

        Date.prototype.addHours = function(hours) {
            var dat = new Date(this.valueOf())
            dat.setHours(dat.getHours() + hours);
            return dat;
        }

        Date.prototype.addDays = function(days) {
            var dat = new Date(this.valueOf())
            dat.setDate(dat.getDate() + days);
            return dat;
        }

        var to = new Date().toJSON().slice(0,10).replace(/-/g,'/');
        var from = new Date().addDays(-4);


        google.charts.setOnLoadCallback(function() { drawChart3(from,to); });
        function drawChart3(from,to) {

            function getDates(startDate, stopDate) {
                var dateArray = new Array();
                var currentDate = startDate;
                //while (currentDate <= stopDate.addDays(1)) {
                while (currentDate <= stopDate) {
                    dateArray.push(currentDate);
                    //console.log(currentDate);
                    currentDate = currentDate.addHours(24);
                    //currentDate = currentDate.addDays(1);
                }
                return dateArray;
            }

            /*
                var d = new Date("2016/08/30");
                console.log("test33 "+ d);
                console.log("test33 "+ d.addDays(1) + "----" + (d.getDate() + 1) + "----" + d.setDate(d.getDate() + 1));
            */

            //alert("from : " + "<?php echo $from?>" + "     " + new Date("<?php echo $from?> 00:00:00"));

            var dateArray = getDates(new Date("<?php echo $from?> 00:00:00"), (new Date("<?php echo $to?> 00:00:00")));

            //var dateArray = getDates(new Date(2016,07,24), (new Date(2016,07,30)));

            // Create our data table out of JSON data loaded from server.
            var data = new google.visualization.DataTable(<?php echo $jsonTable3?>);
            var options = {
                title: 'My test graph3',
                is3D: 'true',
                width: 1000,
                height: 400,
                hAxis: {
                    ticks: dateArray,
                    //gridlines: {count: 15},
                    format: 'd/M HH:mm'
                }
                //hAxis: { ticks: [new Date(2016,8,26), new Date(2016,8,27)] }
            };
            // Instantiate and draw our chart, passing in some options.
            // Do not forget to check your div ID
            var chart = new google.visualization.AreaChart(document.getElementById('chart_div3'));
            //chart.draw(data, options);
        }
    </script>


    <body ng-app="myApp" ng-controller="myCtrl">
        <a href="index.php">Index</a>

        <table>
            <td><h1>{{currentDateStr}} {{currentTimeStr}}</h1>

                <!-- 
                myURL : {{myURL}}<br>
                my interval count : {{myCount}}<br> 
                -->
            </td>
            <td>
                <!--
                <b>Last Temp recorded : </b>
                <span  ng-style="myStyleLastTemp(lastTemp)"> {{lastTemp}} </span><br>
                ({{tempDiffInMin}} hours)
                <hr> 
                <b>Last Detection recorded : </b>
                <span  ng-style="myStyleLastDetection(lastDetetionTime)"> {{lastDetectionTime}} </span><br>
                ({{DetectionDiffInMin}} min)
                <hr> 
                <b>event[1]: {{ eventsArray["1"] }} </b><br>
                -->
                <b>backup P702    : <span  ng-style="myStyleLastEvent('backup P702',eventsArray['backup P702'])"> {{ eventsArray["backup P702"] }} </span></b><br>
                <b>last getWindows: <span  ng-style="myStyleLastEvent('getWindowTitle mypc3',eventsArray['getWindowTitle mypc3'])"> {{ eventsArray["getWindowTitle mypc3"] }} </span></b><br>
            </td>
        </table>
        <hr> <!--------------------------------------------------->
        <!-- 
        Scope.myPage36 : {{myPage36}}<br>
        URL : <span id="demo1"></span><br>
        pathArray : <span id="demo2"></span><br>
        SeconLevelArray : <span id="demo3"></span><br>
        -->
        <script>
            var newURL = window.location.protocol + "//" + window.location.host + window.location.pathname;
            var pathArray = window.location.pathname.split( '/' );
            var secondLevelLocation = pathArray[0];
            /*
            document.getElementById("demo1").innerHTML = newURL;
            document.getElementById("demo2").innerHTML = pathArray;
            document.getElementById("demo3").innerHTML = secondLevelLocation;
            */
        </script>

        <hr> <!--------------------------------------------------->


        <form name="myForm">
            myFrom:
            <input type="date" ng-change="onInput()"  id="exampleInput" name="input" ng-model="myFrom"
                placeholder="yyyy-MM-dd" min="2018-01-01" max="2099-12-31" required />

            myTo:
            <input type="date" id="exampleInputTo" name="inputTo" ng-model="myTo"
                placeholder="yyyy-MM-dd" min="2018-01-01" max="2099-12-31" required />

            <button ng-click="prevDate()">Prev</button>
            <button ng-click="nextDate()">Next</button>


            <div role="alert">
                <span class="error" ng-show="myForm.input.$error.required">
                    Required!</span>
                <span class="error" ng-show="myForm.input.$error.date">
                    Not a valid date!</span>
            </div>
            <!--
            <tt>value myFrom = {{myFrom | date: "yyyy-MM-dd HH:mm:ss"}}</tt><br/>
            <tt>value myTo = {{myTo | date: "yyyy-MM-dd HH:mm:ss"}}</tt><br/>
            <tt>myForm.input.$valid = {{myForm.input.$valid}}</tt><br/>
            <tt>myForm.input.$error = {{myForm.input.$error}}</tt><br/>
            <tt>myForm.$valid = {{myForm.$valid}}</tt><br/>
            <tt>myForm.$error.required = {{!!myForm.$error.required}}</tt><br/>
            -->
            Function:
            <input type="radio" ng-model="myFunc" value="details">Details
            <input type="radio" ng-model="myFunc" value="summary">Summary
            <input type="radio" ng-model="myFunc" value="dailySummary">Daily Summary
            <button ng-click="prevDate()">Prev</button>
            <br>
            Database :
            <input type="radio" ng-model="dbhost" value="localhost">Localhost
            <input type="radio" ng-model="dbhost" value="192.168.0.2">192.168.0.2
            <input type="radio" ng-model="dbhost" value="192.168.0.147">192.168.0.147
            <input type="radio" ng-model="dbhost" value="p702">p702
            <br>
            Nb recs:
            <input type="text" ng-model="nbrecs"><br>
            Host Filter:
            <input type="text" ng-model="hostFilter"><br>
            Title Filter:
            <input type="text" ng-model="titleFilter"><br>
            <button ng-click="getResults()">Refresh</button>
        </form>
        <p>(<a href="{{myPage35}}" target="_blank">{{myPage35}}</a>)</p>

        <!--
        Scope.errMsg : {{errMsg}}<br>
        -->
        <table>
            <tr ng-repeat="x in fgw">
                <td>{{ x.date }}</td>
                <td style="width:100px" >{{ x.time }}</td>
                <td>{{ x.host }}</td>
                <td>{{ x.title }}</td>
                <td>{{ x.duration }}</td>
                <td>{{ x.dur_min }}</td>
                <td>{{ x.cpu }}</td>
            </tr>
        </table>
<!--
        <hr> 
        <b>Last Temp recorded : </b>
        (<a href="{{showLogURL}}" target="_blank">show log</a>)<br>
        (<a href="{{myPage36}}" target="_blank">{{myPage36}}</a>)<br>
        <span  ng-style="myStyleLastTemp(lastTemp)"> {{lastTemp}} </span><br>
        tempDiffInMin : {{tempDiffInMin}}
        <hr> 
        <b>Last Detection recorded : </b>
        (<a href="{{lokiEatingURL}}" target="_blank">Loki Eating</a>)<br>
        <span  ng-style="myStyleLastDetection(lastDetetionTime)"> {{lastDetectionTime}} </span><br>
        txt : {{lastDetectionTxt}}<br>
        temp : {{lastDetectionTemp}}<br>
        DetectionDiffInMin : {{DetectionDiffInMin}}        
        <hr>
-->
        <div id="chart_div3">
<!--
        placeholder for chart_div3
-->
        </div>
<!--
        <hr> 
        <p>My Agario & Co chart (<a href="<?=$myPageAgarioAndOtherGames?>" target="_blank"><?=$myPageAgarioAndOtherGames?></a>)</p>
-->
        <div id="chart_agario">
        this is a placeholder
        </div>

<!--
        <p>test : <?=$jsonGamesTodayData?>
        <p>duration games today : <?=$durationGames?>
        <br>
-->
    </body>
</html>

