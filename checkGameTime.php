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

$shortVersion = (isset($_GET['short']));
$mobileVersion = (isset($_GET['mobile']));

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

        div.bordered {
        border: 4px solid black;
        width: 400px;
        }

    <?php 
    #if mobileVersion, then use a bigger font
    if ($mobileVersion) {
        echo '
            p, form, input, button, div {
                font-family: verdana;
                font-size: 40px;
            }
        ';
    };
    ?>
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
        app.controller('myCtrl', function($scope, $http,$location,$filter,$interval,$timeout) {

            $scope.isMagicEnabled = 0;
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
            $scope.pwd = "";
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
                //console.log("myURL35: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage35);
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
            
            $scope.getKeywords = function() {
                $scope.myPage135 = "getKeywords.php";
                //alert("myPage35 : "+$scope.myPage35);
                //console.log("myURL135: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage135);
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
            
            // get from DB the number of min exceptionally allowed today
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

            $scope.doMagic = function($operation) {
                if (!$scope.isPwdOK("doMagic " + $operation,"")) return;
                console.log("operation : "+ $operation);
                $scope.myPage = "magic.php?"+$operation+"Magic";
                //alert("myPage : "+$scope.myPage);
                console.log("myURL58: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage);
                $http.get($scope.myPage)
                .then(
                function(response) {
                    $scope.errorMsg = response.data.errMsg;
                    $scope.sendMail("just did some magic : " + $operation,"some more details...");
                    $scope.getIsMagicEnabled();
                    //alert("error message 34 : " + response.data.errMsg)
                },
                function(failure) {
                    //Second function handles error
                    $errorMsg = "Error in 99 : " + failure;
                    console.log("error 99 : "+$errorMsg);
                    alert("error message 99 : " + $errorMsg);
                });
            }

            // get from DB wheter magic is enable of not, i.e. the value of the isMagicEnabled parameter
            $scope.getIsMagicEnabled = function() {
                $myURL = "magic.php?isMagicEnabled";
                console.log("myurl 1382 : " + $myURL);
                $http.get($myURL)
                .then(
                function(response) {
                    $scope.isMagicEnabled = response.data.isMagicEnabled;
                    //console.log(response.data);
                    console.log("just updated isMagicEnabled as "+$scope.isMagicEnabled);
                 },
                function(failure) {
                    $errorMsg = "Error in isMagicEnabled : " + failure;
                    console.log($errorMsg);
                    alert("error msg 138 : " + $errorMsg);
                });
            }
            $scope.getIsMagicEnabled();

            //console.log("test 5555");

            $scope.sendMail = function($subject,$msg) {
            $myURL = 'send-mail.php?to="toto240325@gmail.com"&subject='+$subject+'&message='+$msg;
            //$myURL = 'send-mail.php?subject="minutes added"&message="strange !? ;-)"';
            //$myURL = "send-mail.php";
            $http.get($myURL)
            .then(
                function(response) {
                    if (response.data.status="simulation !") {
                        console.log("simulation of email sent : " + response.data.additionalInfo)
                    } else {
                        console.log("email actually sent");
                    }
                },
                function(failure) {
                    $errorMsg = "Error in sendMail : " + failure;
                    console.log($errorMsg);
                    alert("error msg 1139 : " + $errorMsg);
                });
	        }

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
            
            //console.log("test 777");

            $scope.isPwdOK = function($txt1,$txt2) {
                if ($scope.pwd != "monkiki") {
                    alert("sorry ! password not correct :-(");
                    $scope.sendMail($txt1 + " - wrong password : " + $scope.pwd,"some more details : "+$txt2);
                    return false;
                }
                // if pwd was OK, blank it (in x seconds) !
                //$scope.pwd = "";
                $timeout($scope.blankPwd, 60*1000);
                return true;
            }

            $scope.blankPwd = function($operation) {
                $scope.pwd = "";
            }

            $scope.addGamingTime = function($operation) {
                if (!$scope.isPwdOK("addGamingTime " + $operation,$scope.nbMinToAdd)) return;
                console.log("operation : "+ $operation);
                $nbToAdd = $scope.nbMinToAdd;
                if ($operation == "Sub") { $nbToAdd = -($nbToAdd); }
                $scope.myPage = "getGameTimeExceptionallyAllowedToday.php?myFunc=add&nbMin="+$nbToAdd;
                //alert("myPage : "+$scope.myPage);
                console.log("myURL99: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage);
                $http.get($scope.myPage)
                .then(
                function(response) {
                    $scope.errorMsg = response.data.errMsg;
                    $scope.getGameTimeExceptionallyAllowedToday();
		            $scope.sendMail("just added some time to play : " + $nbToAdd,"pw used : " + $scope.pwd);
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
                if (!$scope.isPwdOK("addKeyword",$keyword)) return;
                $scope.myPage = "getKeywords.php?myFunc=add&keyword="+$keyword;
                console.log("myURL199: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage);
                $http.get($scope.myPage)
                .then(
                function(response) {
                    $scope.errorMsg = response.data.errMsg;
                    $scope.getKeywords();
                    $scope.newKeyword = "";
		            $scope.sendMail("just added a new keyword in the blacklist: "+$keyword,"pw used : " + $scope.pwd);
                    //alert("error message 34 : " + response.data.errMsg)
                },
                function(failure) {
                    //Second function handles error
                    $errorMsg = "Error in 199 : " + failure;
                    console.log("error 199 : "+$errorMsg);
                    alert("error message 199 : " + $errorMsg);
                });
            }

            $scope.addKeywordWL = function($keywordWL) {
                if (!$scope.isPwdOK("addKeywordWL",$keywordWL)) return;
                $scope.myPage = "getKeywordsWL.php?myFunc=add&keyword="+$keywordWL;
                console.log("myURL1991: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage);
                $http.get($scope.myPage)
                .then(
                function(response) {
                    $scope.errorMsg = response.data.errMsg;
                    $scope.getKeywordsWL();
                    $scope.newKeywordWL = "";
		            $scope.sendMail("just added a new keyword in the whitelist : "+$keywordWL,"some more details...");
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
                if (!$scope.isPwdOK("delKeyword",$keyword)) return;
                $scope.myPage = "getKeywords.php?myFunc=del&keyword="+$keyword;
                console.log("myURL189: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage);
                $http.get($scope.myPage)
                .then(
                function(response) {
                    $scope.errorMsg = response.data.errMsg;
                    $scope.getKeywords();
		            $scope.sendMail("just deleted a keyword in the blacklist : "+$keyword,"some more details...");
                    //alert("error message 34 : " + response.data.errMsg)
                },
                function(failure) {
                    //Second function handles error
                    $errorMsg = "Error in 189 : " + failure;
                    console.log("error 189 : "+$errorMsg);
                    alert("error message 189 : " + $errorMsg);
                });
            }

            $scope.delKeywordWL = function($keywordWL) {
                if (!$scope.isPwdOK("delKeywordWL",$keywordWL)) return;
                $scope.myPage = "getKeywordsWL.php?myFunc=del&keyword="+$keywordWL;
                console.log("myURL1891: http://<?php echo $thisServer ?>/monitor/" +$scope.myPage);
                $http.get($scope.myPage)
                .then(
                function(response) {
                    $scope.errorMsg = response.data.errMsg;
                    $scope.getKeywordsWL();
		            $scope.sendMail("just deleted a keyword in the whitelist : "+$keywordWL,"some more details...");
                    //alert("error message 341 : " + response.data.errMsg)
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
    
            $scope.sendMail("window.onload triggered on checkGame","...");
            console.log('just sent an email on window.onload'); 
        });

    // window.onload = function () {
    //   angular.element(document.querySelector('[ng-controller="myCtrl"]')).scope().sendMail("window.onload triggered on checkGame","...");
    // }
    </script>
 
 <body ng-app="myApp" ng-controller="myCtrl">

    <?php 

        # if not shortVersion, then display the 15 main window titles
        if (!$shortVersion) {
            echo '
                <!-- windows titles -->
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
                ';
        };
    ?>

    <table>
        <td valign=top>

            <!-- blacklist keywords -->
            <form novalidate>
                Add blacklist keyword:<br>
                <input type="text" ng-model="newKeyword"><br>
                <button ng-click="addKeyword(newKeyword)">Add BL keyword</button>
            </form>
            <p>

            <?php 
            # if not shortVersion, then display the keywords
            if (!$shortVersion) {
                echo '
                <table>
                <!-- "track by $index" in case of duplicate values -->
                <tr ng-repeat="x in keywords track by $index">
                    <td>{{x}}</td>
                    <td>{{$index}}</td>
                    <td><a href="" ng-click="delKeyword(x)">Del</a></td>
                </tr>
                </table>
                ';
            };
            ?>

        </td>
        <td valign=top>
            <!-- whitelist keywords -->
            <form novalidate>
                Add whitelist keyword:<br>
                <input type="text" ng-model="newKeywordWL"><br>
                <button ng-click="addKeywordWL(newKeywordWL)">Add WL keyword</button>
            </form>
            <p>
            <?php 
            # if not shortVersion, then display the WL keywords
            if (!$shortVersion) {
                echo '
                            <table>
                                <!-- "track by $index" in case of duplicate values -->
                                <tr ng-repeat="x in keywordsWL track by $index">
                                    <td>{{x}}</td>
                                    <td>{{$index}}</td>
                                    <td><a href="" ng-click="delKeywordWL(x)">Del</a></td>
                                </tr>
                            </table>
                ';
            };
            ?>
        </td>
    </table>
    <form novalidate>
    Add minutes:
        <input type="text" ng-model="nbMinToAdd"><br>
        <button ng-click="addGamingTime('Add')">Add</button>
        <button ng-click="addGamingTime('Sub')">Sub</button>
        <br>pwd : <input type="password" ng-model="pwd"><br>
        <button ng-click="doMagic('enable')">EnableMagic</button>
        <button ng-click="doMagic('disable')">DisableMagic</button>
    </form>
    <p>
    <p>
    <div class=bordered>magic enabled : {{isMagicEnabled == "1" ? "True" : "False"}}</div>
    

    <p>allowed daily : {{gameTimeAllowedDaily}}
    <p>exceptionally allowed today : {{gameTimeExceptionallyAllowedToday}}
    <p>played time : {{playedTime }} ( {{ i }} )
    <p>Remaining to play : {{ remainingTimeToPlay() }}

    <!--
    //<p>error msg : {{errMsg}}
            <br>
    -->
    <!--
    -->
</body>
</html>
