<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

//change dateValue to sysdate


//This is the server configuration and credentials, for the MySQL database on IP 10.100.56.180
$servername = "localhost";
$username = "root";
$password = "12344321";
$dbname = "staticdb"; 




// Below is the CourseId, take as an input from URL. (Desktop application provides it while requesting the server to run this file)    
$c_id = $_GET['cid'];



//This is a dummy date, change it to system date.
$dateValue = "2018-05-03";



//Connect to dbms
$conn = new mysqli($servername, $username, $password, $dbname);
//Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
} 



//$sql0 = "SELECT StudentId FROM pendingattendance WHERE CourseId = '$c_id' and TheDate = '$dateValue'";


// The code below ensures that the CourseId given by the desktop application is valid (check in the database).
$sqlCheckCourse = "SELECT CourseId FROM courseinfo WHERE CourseId = '$c_id'";
$checkCourse = $conn->query($sqlCheckCourse);
if($checkCourse->num_rows === 0){
	echo "Wrong CourseId";
}
else{

	$sqltest = "SELECT CourseId FROM startsaver WHERE CourseId = '$c_id' and TheDate = '$dateValue' and Stop = '1' ";
	$students0 = $conn->query($sqltest);
	
	//The code below means that attendance session as well as verification is over, now is the time to show the list of such students who attempted to mark their attendance but failed.. due to various reasons. (Photo not matched, he/she was early or late)
	if($students0->num_rows > 0){
		$sql = "SELECT StudentId FROM nomatch WHERE CourseId = '$c_id' and TheDate = '$dateValue'";
		$students = $conn->query($sql);
		$row = $students->fetch_assoc();
		echo "\n";
		while($row != null) {
			$s_id = $row['StudentId'];
			echo $s_id ;
			echo "\n";
			$row = $students->fetch_assoc();
		}
	}
	else{
		$sqltest1 = "SELECT CourseId FROM startsaver WHERE CourseId = '$c_id' and TheDate = '$dateValue' ";
		$student2 = $conn->query($sqltest1);
		if($student2->num_rows === 0){
			echo "Please start the session first.";		// this means that the start button is not  pressed by the instructor
		}
		else{
			echo "Please press the stop button to finish the session.";		// this means that either instructor has not pressed stop button or verification proccess is still running.
		}

	}
}
$conn->close();
 ?>

