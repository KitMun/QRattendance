$(function() {
		
	$(document).on('click', '#btnArrive', function(e) {
		
		if (!confirm("Confirm to update?")){
			e.preventDefault();
			return;
		} 
		
		$.ajax({
			method: "POST",
			url: "../controller/guestArrivalHandler.php",
			data: { 
				guestID: $("#guestID").val()
			}
		}).done(function(json) {
			reply = jQuery.parseJSON(json);
			alert(reply.msg);
			if(reply.status == 0)
				sendWelcomeEmail();
				window.location.replace("index.php");
		});
	});
	
	$(document).on('click', '#editEmail', function(e) {
		$("#guestEmail").hide();
		$(this).hide();
		$("#saveEmail").show();
		$("#txtEmail").show().focus().select();
	});
	
	$(document).on('click', '#saveEmail', function(e) {
		if($("#guestEmail").html() == $("#txtEmail").val()){
			e.preventDefault();
			$("#txtEmail").hide();
			$(this).hide();
			$("#guestEmail").show();
			$("#editEmail").show();
		}
	});
	
	$(document).on('click', '#sendEmail', function(e) {
		if(confirm("Confirm to send?")){
			sendWelcomeEmail();
		}
	});
});

function sendWelcomeEmail(){
	$.ajax({
		method: "POST",
		url: "../controller/welcomeEmailHandler.php",
		data: { 
			guestID: $("#guestID").val()
		}
	}).done(function(json) {
		reply = jQuery.parseJSON(json);
		alert(reply.msg);
		location.reload();
	});
}