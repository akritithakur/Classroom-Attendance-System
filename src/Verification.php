<?php
ini_set('display_errors', 1);


//This is the server configuration and credentials, for the MySQL database on IP 10.100.56.180
$servername = "localhost";
$username = "root";
$password = "12344321";
$dbname = "staticdb"; 


//This is a dummy date, change it to system date.
$dateValue = "2018-05-03";


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
$checkCourse = $conn->query($sqlCheckCourse);
if($checkCourse->num_rows === 0){
	echo "Wrong CourseId";
}
else{
	//The code below makes sure that the request is being made for such course for which attendace has been started first.
	$sqlCheckCourse0 = "SELECT CourseId FROM startsaver WHERE CourseId = '$c_id' and TheDate = '$dateValue' ";
	$checkCourse0 = $conn->query($sqlCheckCourse0);
	if($checkCourse0->num_rows === 0){
		echo "Please start the session first.";  // If it is not started, the destop application is notified.

	}else{


	$sqlCheckCourse1 = "SELECT CourseId FROM startsaver WHERE CourseId = '$c_id' and TheDate = '$dateValue' and Stop = '1' ";
	$checkCourse1 = $conn->query($sqlCheckCourse1);

	//The code below ensures that if the session is started, but not stopped (Right condition) stop it by changing variable "stop" to 1
	//Actually, the function mentioned above is also done in veri_test1.py file. So I have commented it.
	
		if($checkCourse1->num_rows ===0){
		echo "Finishing the attendance session...";
//		$tempsql = "UPDATE startsaver SET Stop= '1' WHERE CourseId = '$c_id' and TheDate = '$dateValue'";
//		$conn->query($tempsql);		// the code is commented because of the reason mentioned in the comment above
	

		//This starts verification process by calling veri_test1.py, which will verify pendingattendance and add those students to 			permanent attendace table whose photos match.

		$output = exec("/usr/bin/python veri_test1.py $c_id");
	//	echo $output;

		}
	//Else it means that this script is run before, and attendance session is already stopped before.
		else if($checkCourse1->num_rows >0){
		$row = $checkCourse1->fetch_assoc();
//			echo $row['CourseId'];
			echo "Attendance session is already verified.";
		}
	}

}
$conn->close();
 ?>


