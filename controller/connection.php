<?php

// Connect to database 
$con = mysqli_connect("localhost","root","","qrattendance");

// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  $con = null;
  }

?>
