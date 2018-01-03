<?php
	/*
		This function returns in json an array containing all the detection times between two dates
		to test it :
		
		http://192.168.0.2/test/getLastTimeWindowTitle.php
						
		Should return something like this : 
		
		{"id":"1355062","time":"2017-11-25 21:08:18","text":"","type":"10"}
				
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
	
	function getLastEvent() {
		
		global $dbhost;
		include 'connect-db.php';
		
		$query = '
		SELECT * FROM fgw
		ORDER BY fgw_id DESC
		LIMIT 1
		';

		$conn = new mysqli($dbhost,$dbuser,$dbpass,$mydb);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} 
		$result = $conn->query($query);

		if (!$result) {
			die ("problem : ".$conn->error);
		}
		
		$outp = "";
			
		if ($result->num_rows > 0) {
			// output data of each row
				
			while($row = $result->fetch_assoc()) {
				if ($outp != "") {$outp .= ",";}	
				$time = $row["fgw_time"];
				$title = $row["fgw_title"];
				$outp .= '{';
				$outp .= '"time":"'.$time.'",';
				$outp .= '"title":'. json_encode($title); 
				$outp .= '}';
			}
		} else {
			echo "0 results";
		}
		$conn->close();
		return $outp;				
	}
		
		
		
	class Fgw {
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
		  return "Hello, this is tyis event : " . $this->id . " " . $this->time . " !";
		}
	}
	  
	// Creating a new person called "boring 12345", who is 12345 years old ;-)
	
	function getLastFgw() {
		
		$myfgw = null;
		$query = '
		SELECT * FROM fgw
		ORDER BY fgw_id DESC
		LIMIT 1
		';

		//echo "test ".$dbhost."\n";
		
		global $dbhost;
		include 'connect-db.php';
		
		$conn = new mysqli($dbhost,$dbuser,$dbpass,$mydb);
		//echo "conn : ";
		//var_dump($conn);
		
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} 
		$result = $conn->query($query);
		//var_dump ($result);

		if (!$result) {
			die ("problem : ".$conn->error);
		}
		
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			/*
			$title=$row["fgw_title"];
			echo "title     : ".$title."<br>\n";
			echo "title ut  : ".utf8_encode($title)."<br>\n";
			echo "title jsut: ".json_encode(utf8_encode($title))."<br>\n";
			*/
			$myfgw = new Fgw($row["fgw_id"],$row["fgw_time"],utf8_encode($row["fgw_title"]),$row["fgw_duration"]);

		} 
		$conn->close();
		//var_dump($myfgw);
		return $myfgw;				
	}
		
	
	//==========================================================================================================================
	//==========================================================================================================================
	//==========================================================================================================================
	
	$dbhost = "192.168.0.147";
	if(isset($_GET['dbhost'])) { $dbhost = $_GET['dbhost']; }
	//echo "dbhost : ".$dbhost;		
	
	$currTime = _date("Y-m-d H:i:s", false, 'Europe/Paris');	
	
	//echo "test2".getLastFgw()->text."\n";
	//var_dump(getLastFgw());
	echo json_encode(getLastFgw());
	
	
	//list($time,$title) = getLastEvent();
	
	/*
	$tempOutp = getLastEvent();
	
	$outp = "{";
	
	//$outp = $outp.'"lastEvent":{"time":"'.$time.'","title":'.json_encode($title).'}';
	$outp = $outp.'"lastEvent":'.$tempOutp.',';
	$outp = $outp.'"currTime":"'.$currTime.'"';
	$outp = $outp.'}';
	
	echo($outp);
	*/
	
?>

