<?php session_start();
	date_default_timezone_set('Asia/Kuala_Lumpur');
	// check if user logged in
	if(!isset($_SESSION['userID'])){
		$ajaxReply['msg'] = "Please log in";
		echo json_encode($ajaxReply);
		die();
	}
	
	// get user data
	$userID = $_SESSION['userID'];
	$channel = $_SESSION['channel'];
	
	ob_start(); //to clean any output and encode json at the end
	// add guest into DB to get guestID
	$ajaxReply['status'] = 0;
	$ajaxReply['msg'] = "已更新";
	
	//get related variables
	$guestID = $_POST['guestID'];
	
	//connect to DB, create $con
	include "connection.php";
	mysqli_set_charset($con, "utf8");
	
	$sql = "UPDATE guest SET arrivalTime = NOW() + INTERVAL 8 HOUR, channel = ? WHERE guestID = ?";
	$statement = $con->prepare($sql);
	
	if(false===$statement){
		$ajaxReply['status'] = 1;
		$ajaxReply['msg'] = "Fail to update";
		$con->close();
		echo json_encode($ajaxReply);
		exit();
	}
	
	$statement->bind_param("ii", $channel, $guestID);
	
	//execute
	if (!$statement->execute()) {
		$ajaxReply['status'] = 2;
		$ajaxReply['msg'] = "Fail to update";
		$statement->close();
		$con->close();
		echo json_encode($ajaxReply);
		exit();
	} 
	
	//check if statement affect any rows
	if ($statement->affected_rows == 0) {
		$ajaxReply['status'] = 3;
		$ajaxReply['msg'] =  "Fail to update";
	}
	
	//close statement
	$statement->close();
	
	//update user last action time
	$sql = "UPDATE channel SET lastActionTime = NOW() WHERE userID = ?";
	$statement = $con->prepare($sql);
	$statement->bind_param("i", $userID);
	$statement->execute();
	$statement->close();
	
	//close connection
	$con->close();

	echo json_encode($ajaxReply);
?>