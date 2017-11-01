<?php session_start();
// guest arrival page

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

//connect to DB, create $con
include "../controller/connection.php";
mysqli_set_charset($con, "utf8");
$guestID = $_GET['guestID'];

// catch change email request
if(isset($_POST["txtEmail"])){
	$email = $_POST["txtEmail"];
	$sql = "UPDATE guest SET email = ? WHERE guestID = ?";
	$statement = $con->prepare($sql);
	$statement->bind_param("si", $email, $guestID);
	$statement->execute();
	$statement->close();
}

// get guest name
$sql = "SELECT name, email, DATE_FORMAT(emailTime,'%l:%i %p') AS emailTime FROM guest WHERE guestID = ?";
	
$statement = $con->prepare($sql);
$statement->bind_param("i",$guestID);
$statement->execute();
$result = $statement->get_result();
$row = $result->fetch_array();
$name = $row['name'];
$email = $row['email'];
$emailTime = $row['emailTime'];

//close statement
$statement->close();

//close connection
$con->close();
 ?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>嘉宾抵达</title>
	<script src="../js/jquery.min.js"></script>
	<script src="../js/guestArrival.js"></script>
	<style>
		#userDetail{
			float:right;
			margin-right: 10px;
		}
		#txtEmail, #saveEmail{
			display:none;
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
		<h1> 嘉宾抵达 </h1>
		<br>
		<form action="guestArrival.php?guestID=<?php echo $guestID;?>" method="POST">
			<h3>
				姓名 : 
				<span id="guestName" name="guestName"><?php echo $name;?></span>
				<br><br>
				电邮 : 
				<span id="guestEmail" name="guestEmail"><?php echo $email;?></span>
				<input id="txtEmail" name="txtEmail" type="email" placeholder="<?php echo $name;?>的电邮" value="<?php echo $email;?>" required/>
				<button type="button" id="editEmail" >更改</button>
				<button type="submit" id="saveEmail" >储存</button>
				<br><br>
				欢迎邮件 : <span id="guestEmailTime" name="guestEmailTime"><?php if(is_null($emailTime)) echo "未发送"; else echo $emailTime;?></span>
				<button type="button" id="sendEmail" >发送</button>
			</h3>	
		</form>
		<br>
		<input id="guestID" type="hidden" value="<?php echo $guestID;?>"/>
		<button id="btnArrive" type="button">确认抵达</button>
		<button id="btnBack" type="button"><a href="index.php" style="text-decoration:none;color:black;">主页</a></button>
  </body>
</html>
