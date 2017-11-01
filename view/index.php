<?php session_start();
// add guest page (DEMO)
include "../controller/connection.php";
mysqli_set_charset($con, "utf8");

// handle log in if POST password is not empty
if(isset($_POST["pw"])){
	if($_POST["pw"] == "123456"){
		$name = $_POST["name"];
		$channel = mt_rand(1,3); // a random channel is given
		
		// update the user table
		$sql = "INSERT INTO channel (name,channel,lastActionTime) VALUES (?, ?, NOW())";
		$statement = $con->prepare($sql);
		$statement->bind_param("si",$name,$channel);
		if (!$statement->execute()) {
			$msg = "Fail to log in, please contact Koo";
		}else{
			//get the new user ID
			$statement = $con->prepare("SELECT MAX(userID) as userID FROM channel");
			$statement->execute();
			$result = $statement->get_result();
			$row = $result->fetch_array();
			$userID = $row['userID'];
			$statement->close();
			// update SESSION
			$_SESSION['userID'] = $userID;
			$_SESSION['name'] = $name;
			$_SESSION['channel'] = $channel;
		}
	}
}

// handler for logged in user
if(isset($_SESSION['name'])){
	
	$userID = $_SESSION['userID'];

	//catch log out request
	if(isset($_POST['logout'])){
		// update the user table
		$sql = "DELETE FROM channel WHERE userID = ?";
		$statement = $con->prepare($sql);
		$statement->bind_param("i",$userID);
		if (!$statement->execute()) {
			$msg = "Fail to log out";
		}else{
			// destroy SESSION
			unset($_SESSION['userID']);
			unset($_SESSION['name']);
			unset($_SESSION['channel']);
		}
		
	}else{
	
		//catch switch channel request
		if(isset($_POST['channel'])){
			$channel = $_POST['channel'];
			// update the user table
			$sql = "UPDATE channel SET channel = ?, lastActionTime = NOW() WHERE userID = ?";
			$statement = $con->prepare($sql);
			$statement->bind_param("ii",$channel,$userID);
			if (!$statement->execute()) {
				$msg = "Fail to change channel";
			}else{
				// update SESSION
				$_SESSION['channel'] = $channel;
			}
			$statement->close();
		}
		
		//catch kick other user out
		if(isset($_POST['kickOut'])){
			$userID = $_POST['kickOut'];
			// update the user table
			$sql = "DELETE from channel WHERE userID = ?";
			$statement = $con->prepare($sql);
			$statement->bind_param("i",$userID);
			if (!$statement->execute()) {
				$msg = "Fail to kick user";
			}
			$statement->close();
		}
		
		$userID = $_SESSION['userID'];
		
		// check if user get kicked out by other user
		$statement = $con->prepare("SELECT * FROM channel WHERE userID = ?");
		$statement->bind_param("i",$userID);
		$statement->execute();
		$result = $statement->get_result();
		$statement->close();
		if ($result->num_rows == 0) {
			// destroy SESSION
			unset($_SESSION['userID']);
			unset($_SESSION['name']);
			unset($_SESSION['channel']);
			$kickout = true;
		}
		
		//get the user list
		$statement = $con->prepare("SELECT * FROM channel ORDER BY channel");
		$statement->execute();
		$result = $statement->get_result();
		$count = $ch1 = $ch2 = $ch3 = 0;
		$userList = "";
		while($row = $result->fetch_array()){
			
			// count channel users no.
			switch($row['channel']){
				case 1: $ch1++;break;
				case 2: $ch2++;break;
				case 3: $ch3++;break;
			}
			$count++;
			
			$userList.= '<tr>
							<td>'.$count.'</td>
							<td>'.$row['name'].'</td>
							<td>'.$row['channel'].'</td>
							<td> <button type="submit" name="kickOut" value="'.$row['userID'].'">Kick</button></td>
						</tr>';
		}
		$statement->close();
	}
}
 ?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>QR Attendance System</title>
	<script src="../js/jquery.min.js"></script>
	<style>
		#userDetail{
			float:right;
			margin-right: 10px;
		}
		#tblUserList{
			border-collapse:collapse;
			border:1px solid black;
			margin:auto;
			width:80%;
		}
		th,td{
			border:1px solid black;
		}
	</style>
  </head>
  <body>
	<div style="text-align:center;">
		<?php if(isset($_SESSION['name'])) 
				echo '
					<form action="index.php" method="post">
						<span id="userDetail">
							'.$_SESSION['name'].', Screen no. : '.$_SESSION['channel'].'&nbsp;&nbsp;
							<button type="submit" name="logout" value="1">Log out</button>
						</span>
					</form>
				'; 
				
		?>
		<br><br><br>
		<h1> QR Attendance System </h1><br>
		<?php
		if(isset($kickout)) {
			echo '<h3>Ops, you\'ve been kicked out, please relogin</h3>'; 
			unset($kickout);
		}
		if(!isset($_SESSION['name'])){
			?>
			<form action="index.php" method="post">
				<input id="name" name="name" type="text" length="5" placeholder="Name" required/>
				<input id="pw" name="pw" type="password" placeholder="Password" required/>
				<br><br><button type="submit">Log in</button>
			</form><br><br>
			<button id="btnViewGuest" type="button"><a href="viewGuest.php" style="text-decoration:none;color:black;">Guest list</a></button>
			<br><br>
			<h3>Welcoming screen</h3>
			<button><a href="welcomeMessage.php?screen=1" style="text-decoration:none;color:black;">Screen 1</a></button>
			<button><a href="welcomeMessage.php?screen=2" style="text-decoration:none;color:black;">Screen 2</a></button>
			<button><a href="welcomeMessage.php?screen=3" style="text-decoration:none;color:black;">Screen 3</a></button>
			<?php
		}else{
			?>
			<button id="btnViewGuest" type="button"><a href="viewGuest.php" style="text-decoration:none;color:black;">Guest list</a></button>
			<h3>Change my screen no.</h3>
			<form action="index.php" method="post">
				<button type="submit" name="channel" value="1">Screen 1 (<?php echo $ch1; ?>)</button>
				<button type="submit" name="channel" value="2">Screen 2 (<?php echo $ch2; ?>)</button>
				<button type="submit" name="channel" value="3">Screen 3 (<?php echo $ch3; ?>)</button>
			</form><br><br>
			<button id="btnAddGuest" type="button"><a href="addGuestForm.php" style="text-decoration:none;color:black;">Add guest</a></button>
			<h3>User list</h3>
			<form action="index.php" method="post">
				<table id="tblUserList">
					<tr>
						<th> No. </th> 
						<th> Name </th> 
						<th> Screen </th> 
						<th> Option </th> 
					</tr>
					<?php echo $userList; ?>
				</table>
			</form>
		<?php
		}
		?>
  </body>
</html>
