<?php 
	ob_start(); //to clean any output and encode json at the end
	//SETTINGS
	// web domain specify in QR code generated and send to guest
	$domain = "192.168.0.2";

	// add guest into DB to get guestID
	$ajaxReply['status'] = 0;
	$ajaxReply['msg'] = "Guest added";
	
	//get related variables
	$name = $_POST['guestName'];
	$email = $_POST['guestEmail'];
	
	//connect to DB, create $con
	include "connection.php";
	mysqli_set_charset($con, "utf8");
	
	$sql = "INSERT INTO guest (name,email) VALUES (?, ?)";
	$statement = $con->prepare($sql);

	if(false===$statement){
		$ajaxReply['status'] = 1;
		$ajaxReply['msg'] = "Guest not added: ".$statement->error;
		$con->close();
		echo json_encode($ajaxReply);
		exit();
	}
	
	$statement->bind_param("ss",$name,$email);
	
	//execute
	if (!$statement->execute()) {
		$ajaxReply['status'] = 2;
		$ajaxReply['msg'] = "Guest not added: ".$statement->error;
		$statement->close();
		$con->close();
		echo json_encode($ajaxReply);
		exit();
	} 
	
	//check if statement affect any rows
	if ($statement->affected_rows == 0) {
		$ajaxReply['status'] = 3;
		$ajaxReply['msg'] =  "Guest not added: 3, ".$statement->error;
	}
	
	//close statement
	$statement->close();
	
	//get the new guest ID
	$statement = $con->prepare("SELECT MAX(guestID) as guestID FROM guest");
	$statement->execute();
	$result = $statement->get_result();
	$row = $result->fetch_array();
	$newGuestID = $row['guestID'];
	
	if(!isset($newGuestID)){
		//fail to get new guest ID
		$ajaxReply['status'] = 4;
		$ajaxReply['msg'] = "Guest not added： 4, ".$statement->error;
		echo json_encode($ajaxReply);
		exit();
	}
	
	//close statement
	$statement->close();
	
	//close connection
	$con->close();

    include('../phpqrcode/qrlib.php'); 

    // generate QR code using new guest ID
     
    $qrCodeDir = "../img/qrcode/"; 
	
	$url = $domain."/view/guestArrival.php?guestID=".$newGuestID;
     
    $fileName = 'guest'.$newGuestID.'.png'; 
     
    $pngAbsoluteFilePath = $qrCodeDir.$fileName; 
     
    // generating 
    if (!file_exists($pngAbsoluteFilePath)) { 
        QRcode::png($url, $pngAbsoluteFilePath); 
    } 
	
	//send email 
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
		$mail->Host = 'SMTP.SERVER';  					// Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'email@email.com';                 // SMTP username
		$mail->Password = 'password';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;                                    // TCP port to connect to

		//Recipients
		$mail->setFrom('email@email.com', 'Demo');
		$mail->addAddress($email, $name);     // Add a recipient

		//Attachments
		$mail->addAttachment($pngAbsoluteFilePath);         // Add attachments

		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = 'Email subject';
		$mail->Body    = 'Email content';
		$mail->CharSet="UTF-8";
		$mail->send();
	} catch (Exception $e) {
		//fail to send email
		$ajaxReply['status'] = 5;
		$ajaxReply['msg'] = "Guest not added: 5, ".$mail->ErrorInfo;
		ob_end_clean(); //clean any output before output json encode
		echo json_encode($ajaxReply);
		exit();
	}
	ob_end_clean(); //clean any output before output json encode
	echo json_encode($ajaxReply);
?>