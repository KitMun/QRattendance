$(function() {
		
	
	$(document).on('click', '#btnSave', function(e) {
		//trim input
		$("#guestName").val($("#guestName").val().trim());
		$("#guestEmail").val($("#guestEmail").val().trim());
		
		//validation
		if(!$("#guestName").val()){
			alert("Guest name cannot be empty");
			return;
		}
		if(!$("#guestEmail").val()){
			alert("Guest email cannot be empty");
			return;
		}
		
		if (!confirm("Confirm to add new guest?")){
			e.preventDefault();
			return;
		} 
		
		$.ajax({
			method: "POST",
			url: "../controller/addGuestHandler.php",
			data: { 
				guestName: $("#guestName").val(),
				guestEmail: $("#guestEmail").val()
			}
		}).done(function(json) {
			//alert(json);
			reply = jQuery.parseJSON(json);
			alert(reply.msg);
		});
	});
});