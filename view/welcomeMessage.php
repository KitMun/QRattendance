<?php?>
<!DOCTYPE html>
<html>
<head>
	<title>Welcome!</title>
	<style>
		body{
			background: url(../img/welcome.jpg) no-repeat center center fixed; 
			  -webkit-background-size: cover;
			  -moz-background-size: cover;
			  -o-background-size: cover;
			  background-size: cover;
		}
		h1{
			text-align: center;
			width:40%;
			margin-top:100px;
			margin-left:30%;
			color:#f8a4a7;
			font-size:70pt;
			Background-color:White;
			font-family:  '蘋果儷中黑', Helvetica, Arial, sans-serif;
		}
		audio{
			display:none;
		}
	</style>
</head>
<body>

	<h1 id="guestName"></h1>
	<audio id="myAudio" controls>
		  <source src="../audio/horse.ogg" type="audio/ogg">
		  Your browser does not support the audio element.
	</audio>
<script>
var guest = "";
if(typeof(EventSource) !== "undefined") {
    var source = new EventSource("../controller/welcomeScreenHandler.php<?php if(isset($_GET["screen"])) echo "?screen=".$_GET["screen"]; ?>");
    source.onmessage = function(event) {
        document.getElementById("guestName").innerHTML = event.data;
		if(guest != event.data){
			if(guest != "") document.getElementById("myAudio").play();
			guest = event.data;
		}
    };
} else {
    document.getElementById("result").innerHTML = "Sorry, your browser does not support server-sent events...";
}
</script>

</body>
</html>

