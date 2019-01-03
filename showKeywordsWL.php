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
        console.log("start script")
        var webserver = "<?php echo $webserver; ?>";
        var dbhost = "<?php echo $dbhost; ?>";

        var app = angular.module('myApp', []);
        app.controller('myCtrl', function($scope, $http,$location,$filter,$interval) {

            $scope.staticNow = new Date();
            $scope.staticNowTimeStr = (new Date()).toLocaleTimeString("fr-BE", {hour12: false});
            $scope.currentDateStr = (new Date()).toLocaleDateString("fr-BE", {hour12: false});


            $scope.myURL = $location.absUrl();

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
                  
            $scope.getKeywordsWL = function() {
                $scope.myPage1351 = "getKeywordsWL.php";
                //alert("myPage351 : "+$scope.myPage35);
                //console.log("myURL1351: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage1351);
                $http.get($scope.myPage1351)
                .then(
                function(response) {
                    $scope.keywordsWL = response.data.keywords;
                    $scope.errorMsg = response.data.errMsg;
                    //alert("error message 34 : " + response.data.errMsg)
                },
                function(failure) {
                    //Second function handles error
                    $errorMsg = "Error in getKeywords : " + failure;
                    console.log("error 1351 : "+$errorMsg);
                    alert("error message 1351 : " + $errorMsg);
                });
            }
            $scope.getKeywordsWL();
            
            $scope.getGameTimeExceptionallyAllowedToday = function() {
                $myURL = "getGameTimeExceptionallyAllowedToday.php";
                //console.log("myurl 1381 : " + $myURL);
                $http.get($myURL)
                .then(
                function(response) {
                    $scope.gameTimeExceptionallyAllowedToday = response.data.gameTimeExceptionallyAllowedToday;
                    //console.log(response.data);
                    $scope.gameTimeAllowedDaily = response.data.gameTimeAllowedDaily;
                },
                function(failure) {
                    $errorMsg = "Error in getGameTimeExceptionallyAllowedToday : " + failure;
                    console.log($errorMsg);
                    alert("error msg 138 : " + $errorMsg);
                });
            }
            $scope.getGameTimeExceptionallyAllowedToday();

            getTimePlayedToday = function() {
                $scope.i = parseInt($scope.i) + 1;
                $myURL = "getTimePlayedToday.php";
                //console.log("myurl 199 : " + $myURL);
                $http.get($myURL)
                .then(
                function(response) {
                    //console.log(response.data.timePlayedToday);
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
            

        });

    </script>
 
    <body ng-app="myApp" ng-controller="myCtrl">

        <h2>White list </h2>
        <table>
            <!-- "track by $index" in case of duplicate values -->
            <tr ng-repeat="x in keywordsWL track by $index">
                <td>{{x}}</td>
            <!--    <td>{{$index}}</td>
                <td><a href="" ng-click="delKeywordWL(x)">Del</a></td> -->
            </tr>
        </table>
        <p>allowed today : {{gameTimeExceptionallyAllowedToday}}
        <p>played time : {{playedTime }} 
        <p>Remaining to play : {{ remainingTimeToPlay() }}

    </body>
</html>
