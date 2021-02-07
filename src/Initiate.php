
<?php
  
error_reporting(E_ALL);
ini_set('display_errors', '1');


//This is the server configuration and credentials, for the MySQL database on IP 10.100.56.180
$servername = "localhost";
$username = "root";
$password = "12344321";
$dbname = "staticdb"; 


//This is a dummy date, change it to system date.
$dateValue = "2018-05-03";


//This is current system time, will be stored as attendance starting time in appropriate place in the database
date_default_timezone_set("Asia/Kolkata");
$timeTillSecond = date("H:i:s");
#echo $timeTillSecond;

// Below is the CourseId, take as an input from URL. (Desktop application provides it while requesting the server to run this file)
$c_id = $_GET['cid'];



//Connect to dbms
$conn = new mysqli($servername, $username, $password, $dbname);
//Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
} 


// The code below ensures that the CourseId given by the desktop application is valid (check in the database).
$sqlCheckCourse = "SELECT CourseId FROM courseinfo WHERE CourseId = '$c_id'";
$checkCourse = $conn->query($sqlCheckCourse);	// Query is executed, checkCourse stores the answer.
if($checkCourse->num_rows === 0){		
	echo "Wrong CourseId";		// message to the desktop application, if the CourseId is invalid.
}


// Basically, the code below is to store the time when attendace is started. So that no student request will be entertained before that.
else{

//The startsaver table in the database is for the purpose of starting and stopping the attendace session, for a particular course and particular date.
	$sqlCheckCourse0 = "SELECT CourseId FROM startsaver WHERE CourseId = '$c_id' and TheDate = '$dateValue'";
	$checkCourse0 = $conn->query($sqlCheckCourse0);
	if($checkCourse0->num_rows === 0){	// If it is 0, that means this script is run for the first time.
		$sqlInsert = "INSERT INTO startsaver (CourseId, TheDate, StartTime, Stop) VALUES ('$c_id', '$dateValue', '$timeTillSecond', false)";	// (Stop will be made true when the instructor will press "stop" button in the desktop application.
		if ($conn->query($sqlInsert) === TRUE) {
			echo "Starting attendance session...";
		} else {
			echo "Error: " . $sqlInsert . "\n" . $conn->error;
		}
	}
	else{			// this means that this script is already run before.
		echo "Attendance Session is already started or finished.";
	}
}

//All the echo messages are fetched in the desktop application and are presented to the user(Professor).
$conn->close();
 ?>


