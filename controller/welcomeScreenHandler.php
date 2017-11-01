<?php
session_start();
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

if(isset($_GET["screen"]))
	$channel = $_GET["screen"];
else
	$channel = 1; // default

//connect to DB, create $con
include "connection.php";
mysqli_set_charset($con, "utf8");
//get latest arrival guest info for the channel
$sql = "SELECT name, arrivalTime
		FROM guest 
		WHERE channel = ?
		ORDER BY arrivalTime DESC LIMIT 0,1";
$statement = $con->prepare($sql);
$statement->bind_param("i", $channel);
$statement->execute();
$result = $statement->get_result();
$row = $result->fetch_array();

if(!is_null($row['arrivalTime']))
	$name = $row['name'];
$statement->close();
echo "data: {$name}\n\n";

flush();
?>