<?php

/*
test
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

    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
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

                   $scope.myURL = $location.absUrl();

            $scope.count = 0;
            $scope.myFunction = function() {
                $scope.count++;
            }

            $scope.playedTime = 0;
            $scope.i = 0;
            $scope.remainingTimeToPlay = "";
            $scope.nbMinToAdd = "15";
            $scope.from = (new Date()).toLocaleDateString("fr-BE", {hour12: false});
            $scope.to = "2099-12-31";
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
            $scope.getResults = function() {
                //$scope.myPage35 = "http://"+webserver+"/monitor/getWindowResult.php" +
                $scope.myPage35 = "getWindowResult.php" +
                "?from='" + $scope.convStrToDate($scope.from) + "'" +
                //"?from='" + $titleFilter('date')($scope.fromDate,'yyyy-MM-dd') + "'" +
                "&to='"+ $scope.to + "'" +
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
                console.log("myURL36: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage36);
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
                console.log("myURL37: http://<?php echo $thisServer ?>/monitor/" +$myURL);
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

            $scope.getGameTimeExceptionallyAllowedToday = function() {
                $myURL = "getGameTimeExceptionallyAllowedToday.php";
                //console.log("myurl 1381 : " + $myURL);
                $http.get($myURL)
                .then(
                function(response) {
                    $scope.gameTimeExceptionallyAllowedToday = response.data.gameTimeExceptionallyAllowedToday;
                    console.log(response.data);
                    $scope.gameTimeAllowedDaily = response.data.gameTimeAllowedDaily;
                },
                function(failure) {
                    $errorMsg = "Error in getGameTimeExceptionallyAllowedToday : " + failure;
                    console.log($errorMsg);
                    alert("error msg 138 : " + $errorMsg);
                });
            }
            $scope.getGameTimeExceptionallyAllowedToday();

            getPlayedTime = function() {
                $scope.i = parseInt($scope.i) + 1;
                $myURL = "getGamesTodayData.php";
                //console.log("myurl 199 : " + $myURL);
                $http.get($myURL)
                .then(
                function(response) {
                    //console.log(response.data.records[0]);
                    $scope.playedTime = response.data.records[0].duration;
                },
                function(failure) {
                    $errorMsg = "Error in getGamesTodayData : " + failure;
                    console.log($errorMsg);
                    alert("error msg 139 : " + $errorMsg);
                });
            }
            getPlayedTime();
            
            $scope.remainingTimeToPlay = function() {
                 return parseInt($scope.gameTimeExceptionallyAllowedToday) + parseInt($scope.gameTimeAllowedDaily) - parseInt($scope.playedTime);
            }
            
            $scope.addGamingTime = function($nbMinToAdd) {
                $scope.myPage = "getGameTimeExceptionallyAllowedToday.php?myFunc=add&nbMin="+$scope.nbMinToAdd;
                //alert("myPage : "+$scope.myPage);
                console.log("myURL99: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage);
                $http.get($scope.myPage)
                .then(
                function(response) {
                    $scope.errorMsg = response.data.errMsg;
                    $scope.getGameTimeExceptionallyAllowedToday();
                    //alert("error message 34 : " + response.data.errMsg)
                },
                function(failure) {
                    //Second function handles error
                    $errorMsg = "Error in 99 : " + failure;
                    console.log("error 99 : "+$errorMsg);
                    alert("error message 99 : " + $errorMsg);
                });
            }

           $interval(getPlayedTime, 10*1000);
        });

    </script>
 
    <body ng-app="myApp" ng-controller="myCtrl">
           
        <table>
            <tr ng-repeat="x in fgw">
                <td>{{ x.date }}</td>
                <td>{{ x.time }}</td>
                <td>{{ x.host }}</td>
                <td>{{ x.title }}</td>
                <td>{{ x.duration }}</td>
                <td>{{ x.dur_min }}</td>
                <td>{{ x.cpu }}</td>
                <td>{{ x.isgame }}</td>
            </tr>
        </table>
        <p>
        <p>
        <form novalidate>
            Add minutes:
            <input type="text" ng-model="nbMinToAdd"><br>
            <button ng-click="addGamingTime()">Add minutes</button>
        </form>
        <p>
        <p>allowed daily : {{gameTimeAllowedDaily}}
        <p>exceptionally allowed today : {{gameTimeExceptionallyAllowedToday}}
        <p>played time : {{playedTime }} ( {{ i }} )
        <p>remaining to play : {{ remainingTimeToPlay() }}

<!--
//<p>error msg : {{errMsg}}
        <br>
-->
<!--
-->
    </body>
</html>

