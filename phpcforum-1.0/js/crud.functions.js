/*************************************************

- jCrud Javascript 
- Copyright (c) 2010 Samuel Ronce

Dernière modification 23/03/10

/*************************************************/

$(function(){
	
	initialize();
	
	/*$(".crud_delete").click(function() {
		update("delete", ident(this.id));
	});
	
	$(".crud_edit").click(function() {
		update("edit", ident(this.id));
	});
	
	$(".crud_add").click(function() {
		update("add", null);
	});*/

	
	 /*$("#etat").ajaxError(function(request, settings){ 
			 $(this).css('color', 'red'); 
			$(this).html("Impossible d'enregistrer"); 
	});
	
	 $("#etat").ajaxSend(function(evt, request, settings){ 
			 $(this).css('color', 'yellow'); 
			 $(this).html("Enregistrement en cours"); 
	});
 
	$("#etat").ajaxSuccess(function(evt, request, settings){ 
		$(this).html("Requête reçue"); 
	});*/
	
	/*$(".crud_next_page").click(function() {
		$.ajax({ 
		   type: "GET", 
		   url: "control.php", 
		   data: "start=2&mode=next_page", 
		   success: function(msg){ 
					$("#crud_display_data").hide('slide', null, 500, function() {
						$("#crud_display_data > div").remove();
						$("#crud_display_data").html(msg);
						initialize();
						$("#crud_display_data").show('slide', {direction: 'right'}, 500);
					});
				}
		   });
	});*/
 

	/*$(".edit").click(function() {
		initalizeForms();
		var id = ident(this.id);
	//	$("#edit\\[" + id + "\\]").css("border","3px solid red");
		 var text = $("#edit\\[" + id + "\\]").next("p").next("p").text();
		 $("#edit\\[" + id + "\\]").next("p").next("p").html("<textarea>" + text + "</textarea>");
		 var setting_button = $("#edit\\[" + id + "\\]").parent().find(".setting_button");
		 setting_button.css("display","block");
		  $(setting_button).find(".cancel").click(function() {
			initalizeForms();
		 });
		 $(setting_button).find(".submit").click(function() {
			alert($("#edit\\[" + id + "\\]").next("p").next("p").find("textarea").text());
			/*$.post("test.php", { data_id: id, mode: "sub" }, function(data){ 
				alert("Data Loaded: " + data); 
			});
			initalizeForms();
		 });
	});*/
	
	
});

/*
function update(mode, id) {
	
		if (mode != "add") {
			var input = $("#crud_form\\[" + id + "\\] :input");
			var condition = $("#crud_form\\[" + id + "\\] :input[name=crud_condition]").val();
		}
		else {
			var input = $("#crud_form_add :input");
			var condition = "";
		}

		var unique = $("#:input[name=crud_unique]").val();
		
		var str_data = '';
		var register_data = true;
		for (var i=0 ; i < input.length ; i++) {
			switch(input[i].type) {
				case 'checkbox':
					if (input[i].checked) {
					//	name += '|' + input[i].value
					}
				break;
				case 'radio':
						register_data = input[i].checked;
				break;
				default:
						register_data = true;
			}
			if (input[i].value != "" && register_data && input[i].type != 'checkbox') {
				str_data += (i != 0 ? '&' : '') + input[i].name + "=" + input[i].value;
			}
		}
		//alert(str_data);
		var ident_data = id;

	
		$.ajax({ 
		   type: "POST", 
		   url: "jcrud.control.php?crud=" + unique + "&form_id=" + ident_data, 
		   data: str_data + "&mode=" + mode + "&condition=" + condition, 
		   dataType: "json",
		   success: function(msg){ 
		
					// $(this).css('color', 'black'); 
					 switch(mode) {
						case 'delete':
							var blockData = $("#crud_form\\[" + ident_data + "\\]").parent("div").parent("div");
							 blockData.effect("explode", null, 700,function() {
								$(this).remove();
							});
							;
						break;
						case 'edit':
							onCrudEdit(msg);
							
							//var block = $("#crud_form\\[" + ident_data + "\\]").parent("div").parent("div");
							//block.effect("highlight", {color: "#ddd"}, 700);
						break;
						case 'add': 
							onCrudAdd(msg);
							
							
							/*var form_data = $("#crud_display_data").children("div").eq(0);
							$(form_data).hide();
							$(form_data).show("blind");
							
							var id = ident($("#crud_display_data").children("div").find("form").attr("id"));

							$("#crud_delete\\[" + id + "\\]").click(function() {
								update("delete", id);
							});
							$("#crud_edit\\[" + id + "\\]").click(function() {
								update("edit", id);
							});
							
							
							
							
						//	if(selectedEffect == 'transfer'){ options = { to: "#button", className: 'ui-effects-transfer' }; }
						break;
					}
				 } 
		 });
}
*/
function finishEtat() {
	$(".etat").fadeOut("slow");
}

function initialize() {

	$("ul.tabs").tabs("> .pane");
	//$(".crud_date").datepicker($.datepicker.regional['fr']);
	$(".crud_number").keypress(function (e){
		if (e.which < 48 || e.which > 57) {
			$(this).blur();
			$(this).effect("highlight", {color: "#ff0000"}, 900);
		}
	});
	$(".crud_hexa").keypress(function (e){
		// 65 - 70 	=> A - F
		// 97 - 102 => a - f
		if (!((e.which >= 48 && e.which <= 57) || (e.which >= 65 && e.which <= 70) || (e.which >= 97 && e.which <= 102))) {
			$(this).blur();
			$(this).effect("highlight", {color: "#ff0000"}, 900);
		}
	});


}

function onCrudEdit(msg) {

}
function onCrudAdd(msg) {

}

function displayEtat(msg, succes_txt, miss_txt) {
		if (msg == 1) {
			$(".state").removeClass("ui-state-error");
			$(".state").addClass("ui-state-highlight");
			$(".state").html('<span class="ui-icon ui-icon-info"></span>' + succes_txt);
		}
		else  {
			$(".state").removeClass("ui-state-highlight");
			$(".state").addClass("ui-state-error");
			$(".state").html('<span class="ui-icon ui-icon-alert"></span><span class="ui-state-error-text">' + miss_txt + '<span>');
		}
		$(".state").fadeIn("slow").delay(3000).fadeOut("slow");

}

function ident(text) {
  return text.match(new RegExp("[0-9]+", "gi"));
}

function event(name) {
	if( oDiv.addEventListener ) {
	  oDiv.addEventListener('click',eventHandler,false);
	} else if( oDiv.attachEvent ) {
	  oDiv.attachEvent('onclick',eventHandler);
	}
}


