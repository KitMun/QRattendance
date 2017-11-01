<?php 
	ob_start(); //to clean any output and encode json at the end
	
	// SETTINGS
	$stickerAbsolutePath = "../img/welcome.png";
	
	$ajaxReply['status'] = 0;
	$ajaxReply['msg'] = "Message for success";
	
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
		$mail->Host = 'smtp.server';  					// Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'email';                 // SMTP username
		$mail->Password = 'password';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;                                    // TCP port to connect to

		//Recipients
		$mail->setFrom('email', 'Demo');
		$mail->addAddress($email, $name);     // Add a recipient

		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = 'Subject';
		$mail->Body    = 'Content';
		$mail->CharSet="UTF-8";
		$mail->send();
		
		// update send email time follow timezone
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