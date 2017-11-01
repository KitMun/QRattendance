<?php session_start(); 
// View Guest Page (DEMO)
include "../controller/connection.php";
mysqli_set_charset($con, "utf8");
if(isset($_SESSION['name'])){
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
}

// get guest list
if(isset($_SESSION['name'])) {
	// list with check in button for logged in users, order by guest ID
	$sql = "SELECT guestID, name, DATE_FORMAT(arrivalTime,'%l:%i %p') AS arrivalTime FROM guest ORDER BY guestID";
		
	$statement = $con->prepare($sql);
	$statement->execute();
	$result = $statement->get_result();
	$count = 1;
	$list = "";
	$arrivedCount = 0;
	$notArrivedCount = 0;
	while($row = $result->fetch_array()){
		(is_null($row['arrivalTime']))? $notArrivedCount++:$arrivedCount++;
		$list.= '	<tr>
					<td> '.$count++.' </td>
					<td> '.$row['name'].' </td>
					<td> '.$row['arrivalTime'].' </td>
					<td> <button><a href="guestArrival.php?guestID='.$row['guestID'].'">Check in</a></button></td>
				</tr>';
	}
}else{
	// list without check in button, order by name
	$sql = "SELECT name, DATE_FORMAT(arrivalTime,'%l:%i %p') AS arrivalTime FROM guest ORDER BY name";
		
	$statement = $con->prepare($sql);
	$statement->execute();
	$result = $statement->get_result();
	$count = 1;
	$list = "";
	$arrivedCount = 0;
	$notArrivedCount = 0;
	while($row = $result->fetch_array()){
		(is_null($row['arrivalTime']))? $notArrivedCount++:$arrivedCount++;
		$list.= '	<tr>
					<td> '.$count++.' </td>
					<td> '.$row['name'].' </td>
					<td> '.$row['arrivalTime'].' </td>
				</tr>';
	}
}
?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Guest list</title>
	<style>
		table{
			border:1px solid black;
			border-collapse: collapse;
			width:98%;
			margin:auto;
		}
		td,th{
			border:1px solid black;
		}
		a{
			text-decoration:none;
			color:blue;
		}
		#userDetail{
			float:right;
			margin-right: 10px;
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
				</form><br><br>
			'; 
		?>
		<h1>Guest list</h1>
		
		<?php if(isset($kickout)){
			echo '<h3>Ops, you\'ve been kicked out, please <a href="index.php">relogin</a></h3>';
			unset($kickout);
		}?>
		
		<h3>Arrived : <span id="arrivedCount"><?php echo $arrivedCount; ?></span>
		&nbsp;&nbsp;&nbsp;&nbsp;Not arrived : <span id="notArrivedCount"><?php echo $notArrivedCount; ?></span></h3>
		
		<table id="guestList">
			<tr>
				<th>No. </th>
				<th>Name </th>
				<th>Arrival time </th>
				<?php if(isset($_SESSION['name'])) echo '<th>Option </th>'; ?>
			</tr>
			<?php 
				echo $list;
			?>
		</table>
		<br>
		<br>
		<button id="btnBack" type="button"><a href="index.php" style="text-decoration:none;color:black;">Menu</a></button>
	</div>
	
  </body>
</html>
