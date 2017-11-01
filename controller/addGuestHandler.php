<?php 
	ob_start(); //to clean any output and encode json at the end
	//SETTINGS
	// $domain = "http://impianaadev.ddns.net";
	$domain = "impian-aa-dev.000webhostapp.com";

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
	
    //$domain = "impianaadev.ddns.net";
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
		$mail->addAttachment($pngAbsoluteFilePath);         // Add attachments

		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = '佛化婚礼嘉宾邀请函 - 方耀祥与薛仙明';
		$mail->Body    = '吉祥，<br><br> 
						感谢您抽空共襄盛举。<br><br>
						佛化婚礼详情:-<br>
						日期：10月21日(六)<br>
						时间：上午10时<br>
						地点：马六甲诺富特酒店十九楼<br>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Novotel Melaka, 19th Floor<br>
						地址：No 1A, Jalan Melaka Raya 2, Taman Melaka Raya, 75000, Melaka<br><br>
						请在抵达会场的时候亮出您的QR code 报到。<br>
						报到完毕后将会有小小惊喜送给您。 <br><br>
						耀祥与仙明 合十<br>';
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