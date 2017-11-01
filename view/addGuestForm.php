<?php session_start();
// add guest page 

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
		echo '<h3>抱歉，你已经被踢出，请<a href="index.php">重新登录</a></h3>';
		die();
	}
}else{
	echo '<h3>请先<a href="index.php">登录</a></h3>';
	die();
}

 ?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>添加嘉宾</title>
	<script src="../js/jquery.min.js"></script>
	<script src="../js/addGuestForm.js"></script>
	<style>
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
						'.$_SESSION['name'].', 屏幕号 : '.$_SESSION['channel'].'&nbsp;&nbsp;
						<button type="submit" name="logout" value="1">退出</button>
					</span>
				</form>
			'; 
		?>
		<br><br><br>
		<h1> 添加嘉宾 </h1>
		<br>
		<h3>
			姓名 : 
			<input id="guestName" name="guestName" type="text" placeholder="Name" />
		</h3>
		<h3>
			电邮 : 
			<input id="guestEmail" name="guestEmail" type="email" placeholder="Email" />
		</h3>	
		<br>
		<button id="btnSave" type="button">添加</button>
		<a id="btnBack" href="index.php" style="text-decoration:none;color:black;"><button type="button">主页</button></a>
  </body>
</html>
