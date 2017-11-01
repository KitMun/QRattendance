<?php
session_start();

//confirm usertype
if(!isset($_SESSION['user'])) $_SESSION['user'] = 'guest';

//send not admin back to index page
if($_SESSION['permission'] != 'superuser'){
	Header("Location:index.php");
	exit();
}

//handle received data 
$request = $_POST['request'];

if($request == "userList"){
	
	$ajaxReply['status'] = 0;
	$ajaxReply['html'] = "";
	
	//get related variables
	$searchbox = $_POST['searchbox'];
	$showInactive = $_POST['showInactive'];
	
	//connect to DB, create $con
	include "connection.php";
	
	//prepare sql
	$sql = "SELECT userID, user, permission, contactNo, email, accStatus FROM userlogin WHERE 1 ";
	$param_type = "";
	$needBindArray = 0;	
	
	if($searchbox != ""){
		$sql .= "AND CONCAT(' ', user,' ',contactNo,' ',email,' ') LIKE ? ";
		$param_type .= "s";
		$searchString = "%". $searchbox ."%";
		$a_params[] = & $searchString;
		$needBindArray = 1;
	}
	
	if($showInactive == "false"){
		$sql .= "AND accStatus != 'inactive' ";
	}
	
	//add order to sql
	$sql .= "ORDER BY permission, user";
	
	$statement = $con->prepare($sql);
	if($needBindArray){
		array_unshift($a_params, $param_type);
		call_user_func_array(array($statement, 'bind_param'), $a_params);
	}
	
	$statement->execute();
	$result = $statement->get_result();
	$rowCount = 1;
	
	if($result->num_rows == 0){
		$ajaxReply['status'] = 1;
		$ajaxReply['html'] .= "<tr><th>No record found";
		if($searchbox != "") $ajaxReply['html'] .= " for keyword '". $searchbox ."'";
		$ajaxReply['html'] .= "</th></tr>";
		echo json_encode($ajaxReply);
		exit();
	}
	
	$ajaxReply['html'] .='
		<tr>
			<th>No</th>
			<th>Name</th>
			<th>Permission</th>
			<th>Contact no</th>
			<th>Email</th>
			<th>Status</th>
			<th>Option</th>
		</tr>
		';
		
	while ($row = $result->fetch_array()) {
		$ajaxReply['html'] .='
			<tr id="U'. $row['userID'] .'">
				<td>'. $rowCount++ .'</td>
				<td>'. $row['user'] .'</td>
				<td>'. $row['permission'] .'</td>
				<td>'. $row['contactNo'] .'</td>
				<td>'. $row['email'] .'</td>
				<td>'. $row['accStatus'] .'</td>
				<td><button name="edit" class="btn btn-info btn-xs">Edit</button></td>
			</tr>
		';
	}
		
	//close statement
	$statement->close();
	
	//close connection
	$con->close();

	echo json_encode($ajaxReply);
	
}else if($request == "addUser"){
	
	$ajaxReply['status'] = 0;
	$ajaxReply['msg'] = "User added successfully";
	
	//get related variables
	$username = $_POST['username'];
	$permission = $_POST['permission'];
	$contactNo = $_POST['contactNo'];
	$email = $_POST['email'];
	$password = "123456";
	$status = "active";
	
	//connect to DB, create $con
	include "connection.php";
	
	$lowerUserName = strtolower($username);
	$sql = "SELECT userID FROM userlogin WHERE LOWER(user) = ?";
	
	//validate user name
	$statement = $con->prepare($sql);
	$statement->bind_param("s",$lowerUserName);
	$statement->execute();
	$statement->store_result();
	
	//check if user found
	if($statement->num_rows() != 0){
		$ajaxReply['status'] = 1;
		$ajaxReply['msg'] = "User name already registered";
		$statement->close();
		$con->close();
		echo json_encode($ajaxReply);
		exit();
	}
	
	$sql = "INSERT INTO userlogin (user,password,permission,contactNo,email,accStatus) VALUES (?, ?, ?, ?, ?, ?)";
	$statement = $con->prepare($sql);

	if(false===$statement){
		$ajaxReply['status'] = 2;
		$ajaxReply['msg'] = "Fail to add new user";
		$con->close();
		echo json_encode($ajaxReply);
		exit();
	}
	
	$statement->bind_param("ssssss",$username,$password,$permission,$contactNo,$email,$status);
	
	//execute
	if (!$statement->execute()) {
		$ajaxReply['status'] = 2;
		$ajaxReply['msg'] = "Fail to add new user";
		$statement->close();
		$con->close();
		echo json_encode($ajaxReply);
		exit();
	} 
	
	//check if statement affect any rows
	if ($statement->affected_rows == 0) {
		$ajaxReply['msg'] =  "Fail to add new user";
	}
	
	//close statement
	$statement->close();
	
	//close connection
	$con->close();
	
	echo json_encode($ajaxReply);
}else if($request == "editUser"){
	
	$ajaxReply['status'] = 0;
	$ajaxReply['msg'] = "Saved";
	
	//get related variables
	$userID = $_POST['userID'];
	$username = $_POST['username'];
	$permission = $_POST['permission'];
	$contactNo = $_POST['contactNo'];
	$email = $_POST['email'];
	$status = $_POST['status'];
	
	//connect to DB, create $con
	include "connection.php";
	
	$lowerUserName = strtolower($username);
	$sql = "SELECT userID FROM userlogin WHERE LOWER(user) = ? AND userID != ?";
	
	//validate user name
	$statement = $con->prepare($sql);
	$statement->bind_param("si",$lowerUserName,$userID);
	$statement->execute();
	$statement->store_result();
	
	//check if product found
	if($statement->num_rows() != 0){
		$ajaxReply['status'] = 1;
		$ajaxReply['msg'] = "User name already registered";
		$statement->close();
		$con->close();
		echo json_encode($ajaxReply);
		exit();
	}
	
	$sql = "UPDATE userlogin SET user = ? , permission = ? , contactNo = ? , email = ? , accStatus = ? WHERE userID = ?";
	$statement = $con->prepare($sql);

	if(false===$statement){
		$ajaxReply['status'] = 2;
		$ajaxReply['msg'] = "Fail to update";
		$con->close();
		echo json_encode($ajaxReply);
		exit();
	}
	
	$statement->bind_param("sssssi",$username,$permission,$contactNo,$email,$status,$userID);
	
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
		$ajaxReply['msg'] =  "No change occurred";
	}
	
	//close statement
	$statement->close();
	
	//close connection
	$con->close();
	
	echo json_encode($ajaxReply);
}

?>