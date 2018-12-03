    <?php
/*
    This script check the status of games played on mypc3 today
 */

//echo 'Version PHP courante : ' . phpversion() . "<br>";
$thisServer = $_SERVER['SERVER_NAME'];
//echo "server : $thisServer<br>";

include 'params.php';
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
<!--
    <script src="http://localhost/monitor/libs/angular.min.js"></script>
    <script type="text/javascript" src="http://localhost/monitor/libs/jsapi"></script>
    <script type="text/javascript" src="http://localhost/monitor/libs/jquery.min.js"></script>
-->

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


            $scope.myStyleLastBackup = function(){
                $scope.backupDiffInMin = ($scope.staticNow - (new Date($scope.eventsArray["backup P702"]))) / (60*1000);
                $scope.backupDiffInMin = $scope.backupDiffInMin.toFixed(2);
                return parseInt($scope.backupDiffInMin) > 25 * 60 ? {'background-color': 'pink'} : {'background-color': 'lightgreen'}
            }

            $scope.myURL = $location.absUrl();
/*
            $scope.count = 0;
            $scope.myFunction = function() {
                $scope.count++;
            }
*/
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
            
            console.log("test 4444");

            $scope.getKeywords = function() {
                $scope.myPage135 = "getKeywords.php";
                //alert("myPage35 : "+$scope.myPage35);
                console.log("myURL135: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage135);
                $http.get($scope.myPage135)
                .then(
                function(response) {
                    $scope.keywords = response.data.keywords;
                    $scope.errorMsg = response.data.errMsg;
                    //alert("error message 34 : " + response.data.errMsg)
                },
                function(failure) {
                    //Second function handles error
                    $errorMsg = "Error in getKeywords : " + failure;
                    console.log("error 135 : "+$errorMsg);
                    alert("error message 135 : " + $errorMsg);
                });
            }
            $scope.getKeywords();
            
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

            //console.log("test 5555");

            getTimePlayedToday = function() {
                $scope.i = parseInt($scope.i) + 1;
                $myURL = "getTimePlayedToday.php";
                //console.log("myurl 199 : " + $myURL);
                $http.get($myURL)
                .then(
                function(response) {
                    console.log(response.data.timePlayedToday);
                    //$scope.playedTime = response.data.records[0].duration;
                    $scope.playedTime = response.data.timePlayedToday;
                },
                function(failure) {
                    $errorMsg = "Error in getTimePlayedToday : " + failure;
                    console.log($errorMsg);
                    alert("error msg 139 : " + $errorMsg);
                });
            }
            getTimePlayedToday();

            //console.log("test 6666");
            
            $scope.remainingTimeToPlay = function() {
                 return parseInt($scope.gameTimeExceptionallyAllowedToday) + parseInt($scope.gameTimeAllowedDaily) - parseInt($scope.playedTime);
            }
            
            //console.log("test 777");

            $scope.addGamingTime = function() {
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

            $scope.addKeyword = function($keyword) {
                $scope.myPage = "getKeywords.php?myFunc=add&keyword="+$keyword;
                console.log("myURL199: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage);
                $http.get($scope.myPage)
                .then(
                function(response) {
                    $scope.errorMsg = response.data.errMsg;
                    $scope.getKeywords();
                    $scope.newKeyword = "";
                    //alert("error message 34 : " + response.data.errMsg)
                },
                function(failure) {
                    //Second function handles error
                    $errorMsg = "Error in 199 : " + failure;
                    console.log("error 199 : "+$errorMsg);
                    alert("error message 199 : " + $errorMsg);
                });
            }

            $scope.delKeyword = function($keyword) {
                $scope.myPage = "getKeywords.php?myFunc=del&keyword="+$keyword;
                console.log("myURL189: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage);
                $http.get($scope.myPage)
                .then(
                function(response) {
                    $scope.errorMsg = response.data.errMsg;
                    $scope.getKeywords();
                    //alert("error message 34 : " + response.data.errMsg)
                },
                function(failure) {
                    //Second function handles error
                    $errorMsg = "Error in 189 : " + failure;
                    console.log("error 189 : "+$errorMsg);
                    alert("error message 189 : " + $errorMsg);
                });
            }

            $interval(displayCurrentDate, 10*1000);
            $interval(getTimePlayedToday, 10*1000);
        });

    </script>
 
    <body ng-app="myApp" ng-controller="myCtrl">
           
        <p>
        <form novalidate>
            Add minutes:
            <input type="text" size="2" ng-model="nbMinToAdd"><br>
            <button ng-click="addGamingTime()">Add minutes</button>
        </form>
        <p>
        <form novalidate>
            Add keyword:
            <input type="text" ng-model="newKeyword"><br>
            <button ng-click="addKeyword(newKeyword)">Add keyword</button>
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

