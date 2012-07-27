(function($) {
	$.extend($.fn, {
		jcrud: function(mode, func, is_validate, custom_selector) {
				if (is_validate == undefined) {
					is_validate = true;
				}
				this.each(function() {
					var id = ident(this.id);
					if (mode != 'delete') {
						var selector = mode == "add" ? "#crud_form_add" : "#crud_form\\[" + id + "\\]";				
						if (is_validate) {
							$(selector).validate({
									errorClass: "form_error",
									validClass: "form_valid",
									success: function(label) {
										label.html("&nbsp;").addClass("form_valid");
									}
								}
							
							);
						}
					}
					$(this).click(function() {
						var canRecord = mode != 'delete' ? is_validate ? $(selector).valid() : $(custom_selector).valid() : true;
						if (canRecord) {
							var str_data = $(selector).serialize();
							var unique = $("#:input[name=crud_unique]").val();
							$.ajax({ 
							   type: "POST", 
							   url: "jcrud.control.php?crud=" + unique + "&form_id=" + id, 
							   data: str_data + "&mode=" + mode, 
							   dataType: "json",
							   success: function(json){ 
								
								 func(json);
							   }
							 });
						  }
					});
					
				});
			}
	});
	
	

})(jQuery);





