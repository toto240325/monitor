<?php

	/*

	This function returns in json an array containing the last event, with its ID, time, text, event_type.
	
	Output example : 
	{"id":"63","time":"2017-11-22 22:07:56","text":"backup p702 to googleDrive via mypc3","type":"backup p702"}

	Example : 
	http://192.168.0.2/loki/getLastEvent.php?type="backup p702"

	Mockup : (no database connection) 
	http://192.168.0.2/loki/getLastEvent.php?type="mockup"

	Note : mysql_* deprecated ! use MySQLi instead

	*/

	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");

	//	echo $_SERVER['REQUEST_URI'];
	$defaultTimeZone='UTC';
	if(date_default_timezone_get()!=$defaultTimeZone) date_default_timezone_set($defaultTimeZone);
	
	function _date($format="r", $timestamp=false, $timezone=false)
	{
		$userTimezone = new DateTimeZone(!empty($timezone) ? $timezone : 'GMT');
		$gmtTimezone = new DateTimeZone('GMT');
		$myDateTime = new DateTime(($timestamp!=false?date("r",(int)$timestamp):date("r")), $gmtTimezone);
		$offset = $userTimezone->getOffset($myDateTime);
		return date($format, ($timestamp!=false?(int)$timestamp:$myDateTime->format('U')) + $offset);
	}
	
	class Event {
		public $id;
		public $time;
		public $text;
		public $type;
		
		// Assigning the values
		public function __construct($id, $time, $text, $type) {
		  $this->id = $id;
		  $this->time = $time;
		  $this->text = $text;
		  $this->type = $type;
		}
		
		// Creating a method (function tied to an object)
		public function test() {
		  return "Hello, this is this event : " . $this->id . " " . $this->time . " !";
		}
	}

	function getLastEvent($type) {
		$query = "
		SELECT event_id, event_time, event_text, event_type
		FROM event
		WHERE event_type = '".$type."'
		ORDER BY event_id DESC
		LIMIT 1
		";
		
		//echo $query;

		include 'connect-db.php';
		$conn = new mysqli($dbhost,$dbuser,$dbpass,$mydb);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} 
		$result = $conn->query($query);

		if (!$result) {
			die ("problem : ".$conn->error);
		}
		
		$myEvent = new Event("0","1999-12-31 23:59:59","(none)","(none)");
		if ($result->num_rows > 0) {
			// output data of each row
				
			while($row = $result->fetch_assoc()) {
				
				$id = $row["event_id"];
				$time = $row["event_time"];
				$text = $row["event_text"];
				$type = $row["event_type"];
								
				//echo "id: " . $row["event_id"]. " - time: " . $row["event_time"]. "<br>";
				
				$myEvent->id = $id;
				$myEvent->time = $time;
				$myEvent->text = utf8_encode($text);
				$myEvent->type = $type;
				
			}
		} else {
			//echo "0 results";
		}
		$conn->close();
		return $myEvent;				
	}
	
	//==========================================================================================================================
	//==========================================================================================================================
	//==========================================================================================================================
	
	$currTime = _date("Y-m-d H:i:s", false, 'Europe/Paris');
	
	//$myhost = "localhost";
	$myhost = "192.168.0.147"; 			
	$type = "backup p702";
	
	if(isset($_GET['myhost'])) { $myhost = $_GET['myhost']; }
	if(isset($_GET['type'])) { $type = $_GET['type']; }
	
	
	//echo "type : ".$type."\n";
	
	if ($type == "mockup") {
		echo '{"id":"63","time":"2017-11-22 22:07:56","text":"backup p702 to googleDrive via mypc3","type":"mockup"}';
	} else {
		echo json_encode(getLastEvent($type));
	}
	?>

