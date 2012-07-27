$(function() {
	$("#signupForm").validate({
		errorClass: "form_error",
		validClass: "form_valid",
		success: function(label) {
			label.html("&nbsp;").addClass("form_valid");
		},
		rules: {
			username: {
				required: true,
				minlength: 3,
				remote: "ajax/username.php"
			},
			password: {
				required: true,
				minlength: 5
			},
			email: {
				required: true,
				email: true
			},
			confirm_email: {
				required: true,
				email: true,
				equalTo: "#email"
			},
			confirm_password: {
				required: true,
				minlength: 5,
				equalTo: "#password"
			}
		},
		messages: {
			username: {
				required: "{L_USERNAME_REQUIRED}",
				minlength: "{L_USERNAME_MIN}",
				remote: "{L_USERNAME_REMOTE}"
			},
			password: {
				required: "{L_PASS_REQUIRED}",
				minlength: "{L_PASS_MIN}"
			},
			confirm_password: {
				required: "{L_PASS_REQUIRED}",
				minlength: "{L_PASS_MIN}",
				equalTo: "{L_PASS_EQUAL}"
			},
			email: {
				required: "{L_EMAIL_REQUIRED}",
				email: "{L_EMAIL_TYPE}"
			},
			confirm_email: {
				required: "{L_EMAIL_REQUIRED}",
				email: "{L_EMAIL_TYPE}",
				equalTo: "{L_EMAIL_EQUAL}"
			}
		},
	});
});