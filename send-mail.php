
<?php

/*
send-mail.php : send mail with passed to, subject, message
getKeywordsWL : return the list of whitelist keywords to check in windows titles

http://localhost/monitor/send-mail.php?to=toto240325@gmail.com&subject="my subject"&message="my message"
http://192.168.0.147/monitor/send-mail.php




 
History :
12/03/2019 ED : first version

 */

//echo 'Version PHP courante : ' . phpversion() . "<br>";
$thisServer = $_SERVER['SERVER_NAME'];
//echo "server : $thisServer<br>";


header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

//echo $_SERVER['REQUEST_URI']."\n";
//echo gethostname()."\n";

//sleep(5);



///========================================================================================
// main


$to = 'toto240325@gmail.com';
if (isset($_GET['to'])) {$to = $_GET['to'];}

$subject = "<empty subject>";
if (isset($_GET['subject'])) {$subject = $_GET['subject'];}

$message = "<empty message>";
if (isset($_GET['message'])) {$message = $_GET['message'];}

$headers = "From: toto@nb250\r\n";

$output = "";
$output = $output . "sending mail with \n\r";
$output = $output . "  subject : ".$subject."\n\r";
$output = $output . "  message : ".$message."\n\r";
$output = $output . "  to      : ".$to."\n\r";
$output = $output . "  headers : ".$headers."\n\r";


if (mail($to, $subject, $message, $headers)) {
   $output = $output . "SUCCESS\n\r";
} else {
   $output = $output . "ERROR\n\r";
}

$data = "";
$outp = '{"errMsg":' . json_encode($output) ;
$outp = $outp . ',"additional data":"' . $data . '"' ;
$outp = $outp . '}';
echo $outp;

?>


