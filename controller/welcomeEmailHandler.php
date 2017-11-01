<?php 
	ob_start(); //to clean any output and encode json at the end
	
	// SETTINGS
	$stickerAbsolutePath = "../img/welcome.png";
	
	$ajaxReply['status'] = 0;
	$ajaxReply['msg'] = "邮件已发送";
	
	//get related variables
	$guestID = $_POST['guestID'];
	
	//connect to DB, create $con
	include "connection.php";
	mysqli_set_charset($con, "utf8");
	
	// get guest name and email through guest ID
	$sql = "SELECT name, email FROM guest WHERE guestID = ?";
	
	$statement = $con->prepare($sql);
	$statement->bind_param("i",$guestID);
	$statement->execute();
	$result = $statement->get_result();
	$row = $result->fetch_array();
	$name = $row['name'];
	$email = $row['email'];
	
	//close statement
	$statement->close();
	
	//send welcoming email 
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	require '../PHPMailer/src/Exception.php';
	require '../PHPMailer/src/PHPMailer.php';
	require '../PHPMailer/src/SMTP.php';
	
	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
	try {
		//Server settings
		$mail->SMTPDebug = 2;                                 // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.live.com';  					// Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'impianaadev@hotmail.com';                 // SMTP username
		$mail->Password = 'ImpianAA';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;                                    // TCP port to connect to

		//Recipients
		$mail->setFrom('impianaadev@hotmail.com', 'Demo');
		$mail->addAddress($email, $name);     // Add a recipient

		//Attachments
		$mail->AddEmbeddedImage('../img/Little Sami-0'.mt_rand(1,6).'.png', 'cute');

		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = '欢迎你莅临方耀祥与薛仙明的佛化婚礼';
		$mail->Body    = '吉祥，<br><br>欢迎您大驾光临! <br> 谨送上一个可爱图案表示感谢<br> 请好好享受食物<br><img src="cid:cute" style="max-width:100%" />';
		$mail->CharSet="UTF-8";
		$mail->send();
		
		// update send email time
		$sql = "UPDATE guest SET emailTime = NOW() + INTERVAL 8 HOUR WHERE guestID = ?";
		$statement = $con->prepare($sql);
		$statement->bind_param("i", $guestID);
		$statement->execute();
		$statement->close();
	} catch (Exception $e) {
		//fail to send email
		$ajaxReply['status'] = 1;
		$ajaxReply['msg'] = "Email not sent: 5, ".$mail->ErrorInfo;
		//ob_end_clean(); //clean any output before output json encode
		echo json_encode($ajaxReply);
		exit();
	}
	ob_end_clean(); //clean any output before output json encode
	$con->close();
	echo json_encode($ajaxReply);
?>