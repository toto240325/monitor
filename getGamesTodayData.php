
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


$today = _date("Y-m-d", false, 'Europe/Paris');

function getGamesTodayData()
{
    global $dbhost;
    global $thisServer;
    global $inTitleList;
    global $today;
    
    // prepare Agario graph -----------------------------------------------------------------------------
    //$fromDateAgar = new DateTime(date('Y-m-d'));
    //$fromDateAgar->modify('-10 day');
    //$fromAgar = $fromDateAgar->format('Y-m-d');
    $fromAgar = $today;

    //$toDateAgar = new DateTime(date('Y-m-d'));
    //$toDateAgar = $today;
    //$toDateAgar->modify('+1 day');
    //$toAgar = $toDateAgar->format('Y-m-d');
    $toAgar = $today." 23:59:59";

    //echo "from:".$fromAgar."\n";
    //echo "to:".$toAgar."\n";
    

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

    echo "<br><br>=========================================<br>".$myPageAgarioAndOtherGames."<br>";
    //echo "fromAgar : ".$fromAgar."  toAgar : ".$toAgar."<br>";
    //echo "mypage Agario2 : ".$myPageAgarioAndOtherGames."<br>";
    
    
    //echo "<script>";
    //echo 'console.log("mypage Agario2 : ' . $myPageAgarioAndOtherGames . '")';
    //echo "</script>";


echo "test 2\n";

$url="http://192.168.0.147/monitor/getWindowResult.php?from=2018-09-24&to=2018-09-24+23:59:59&inTitleList=%0A++++%22Agar+Private+Server+Agario+Game+Play+Agario+-+Google+Chrome%22%2C%0A++++%22ZombsRoyale.io+%7C+Play+ZombsRoyale.io+for+free+on+Iogames.space%21+-+Google+Chrome%22%2C%0A++++%22Surviv.io+%7C+Play+Surviv.io+for+free+on+Iogames.space%21+-+Google+Chrome%09%22%2C%0A++++%22Agar.io+-+Google+Chrome%22%2C%0A++++%22alis.io+-+Google+Chrome%22%2C%0A++++%22slither.io+-+Google+Chrome%22%2C%0A++++%22diep.io+-+Google+Chrome%22%2C%0A++++%22space1.io+-+Google+Chrome%22%0A&dbhost=localhost&nbrecs=100&order=date&myFunc=dailySummaryTotal";
echo "url:".$url."\n";


    $service_url = $url;
    echo "test 2.5\n";
    $curl = curl_init($service_url);
    echo "test 3\n";
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $curl_response = curl_exec($curl);

    echo "test 3\n";

    if ($curl_response === false) {
        $info = curl_getinfo($curl);
        curl_close($curl);
        die('error occured during curl exec. Additioanl info: ' . var_export($info));
    }
    curl_close($curl);
    $decoded = json_decode($curl_response);
    if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
        die('error occured: ' . $decoded->response->errormessage);
    }
    echo 'response ok!';
    var_export($decoded->response);
















    //('mypage Agario2 : '".$myPageAgarioAndOtherGames);
    $json = file_get_contents($myPageAgarioAndOtherGames);
    echo "json 123 : "."<br>";
    print_r($json)."\nb<br>";
    //echo "test 34<br>\n"; var_dump($json);
    return $json;     
}


$jsonGamesTodayData = getGamesTodayData();
echo "jsonGamesTodayData :".$jsonGamesTodayData."\n";

?>


