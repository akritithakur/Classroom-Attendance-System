
<?php
//This file will be executed when android application requests. The purpose of this file is to upload the photos of the student in appropriate folder and add some rows in the database to tell the system that this student has attempted for attendance.

//Before doing the functions mentioned above, this file also checks that student is providing right ID, Classroom position, day, time and course are correct.


//Below is the ID of the student that is using the Android application that requests the server to execute this file.
//The ID is extracted from the name of the file(Photo) uploaded by the android application.
$id_string = $_FILES['uploaded_file']['name'];
$idfinal = substr($id_string, 0, 9); // returns "d"


//This is a dummy date, change it to system date.
$dateValue = "2018-05-03";


//This is the server configuration and credentials, for the MySQL database on IP 10.100.56.180
$servername = "localhost";
$username = "root";
$password = "12344321";
$dbname = "staticdb"; 
    


//Take routeraddress of the classroom router (It should be static, as it will be matched against the router address mentioned in the timetable table for given time in the database
$router = "128000111001";		//this is a dummy address for testing purposes, change it as mentioned above



//Take timevalue as in systemtime   
$timeValue = 8 ;


date_default_timezone_set("Asia/Kolkata");
$timeInSeconds = date("H:i:s");


//The code below is used to find the day of given date. 
$unixTimestamp = strtotime($dateValue);
$dayOfWeek = date("l", $unixTimestamp);
$dayUpper = strtoupper($dayOfWeek);
$day = substr($dayUpper,0,3);		// final output: For example, 26-March-18 will return "MON" (monday)



//Connect to dbms
$conn = new mysqli($servername, $username, $password, $dbname);
//Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
} 


//We will find out which course the student is attending by router address as well as day and time matching.
$sql = "SELECT CourseId FROM timetable WHERE RouterAddress = '$router' and TimeStart = '$timeValue' and Day = '$day' ";
$course = $conn->query($sql);

$row = $course->fetch_assoc();
$c_id = $row['CourseId'];
//$c_id is the course the student is attending.



$timeCheckSql = "SELECT StartTime, Stop FROM startsaver WHERE CourseId = '$c_id' and TheDate = '$dateValue'";
$timeCheckAns = $conn->query($timeCheckSql);
if($timeCheckAns->num_rows === 1){	//This condition means that attendance session is started by the instructor
	$timeRow = $timeCheckAns->fetch_assoc();
	if($timeRow['StartTime']<$timeInSeconds and $timeRow['Stop'] == 0){	//The session is started, but not stopped (Right condition)

		$sql1 = "SELECT StudentId FROM courseregisinfo WHERE StudentId = '$idfinal' and CourseId = '$c_id'";
		$result = $conn->query($sql1);

		if ($result->num_rows === 1) {	// This means that student has enrolled for this course.
			echo "The student is correctly positioned at right time and day, his attendance will be added for face matching.<br>";

		//In the code below, we are adding the student's entry to pending attendance table for verification of face in future (when "stop" button is presses and verification will happen)
			$sqlInsert = "INSERT INTO pendingattendance (CourseId, TheDate, StudentId) VALUES ('$c_id', '$dateValue', '$idfinal')";
			if ($conn->query($sqlInsert) === TRUE) {
				echo "New record created successfully in PendingAttendance";
			} else {
				echo "Error: " . $sql1 . "<br>" . $conn->error;
			}
		} else {
			if ($result->num_rows === 0) {
				echo "Classroom position, day, time or course is incorrect.";
			} else {
				echo "Inconsistency in the database";
			}
		}
	} else{
		//When student is late, insert his ID into no match table, which will be helpful to show unsuccessful attempts.
		$sqlFail = "INSERT INTO nomatch(StudentId,CourseId,TheDate) VALUES ('$idfinal', '$c_id', '$dateValue')";
		$conn->query($sqlFail);
		echo "Sorry, you are late for your class.";
	}

} else {
	//When student is early, insert his ID into no match table, which will be helpful to show unsuccessful attempts.
	$sqlFail = "INSERT INTO nomatch(StudentId,CourseId,TheDate) VALUES ('$idfinal', '$c_id', '$dateValue')";
	$conn->query($sqlFail);
	echo "Sorry, you are either early for your class.";
}

$conn->close();


//Uploaded file information and saving the photos to appropriate place in storage
			$file_path = "student_images/".$idfinal."/".$dateValue."_".$c_id."/";
//echo $file_path;
			mkdir($file_path, 0777, true);
			$file_path = $file_path . basename( $_FILES['uploaded_file']['name']);
			if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $file_path)) {
				echo "success";
			} 
			else {
				echo "fail";
			}


 ?>
