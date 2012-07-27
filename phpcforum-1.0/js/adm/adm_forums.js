function registerOrderForum(displayTxtEtat) {
	$.ajax({
		  url: '../ajax/adm.php',
		  type: 'POST',
		  data: {f: "updateOrderForum", order:orderForum()},
		  success: function(data) {
			if (displayTxtEtat)
				displayEtat(data, "Enregistrement des nouvelles positions réussi", "Enregistrement des nouvelles positions échoué");
		  }
	});
}

function orderForum() {
	var regexp = new RegExp(";{", "g");
	var regexp2 = new RegExp("}([0-9]+)", "g");
	var str = constructOrderForum("#root", 1);
	str = str.replace(regexp, "{");
	str = str.replace(regexp2, "};$1");
	return str;
}

function constructOrderForum(selector, depth) {
	str = "{";
	var i=1;
	var regexp = new RegExp(";$", "g");
	var nb_selector = selector + " > ul:eq(0) > li";
	var first_selector = selector;
	if ($(nb_selector).length == 0) 
		return "";
	for (var j=0 ; j < $(nb_selector).length ; j++) {
		this_selector = " > ul > li:eq(" + j + ")";
		selector += this_selector;
		$(selector).each(function(index) {
			str += i + ":" + $(this).attr("data-id") + ";";
			i++;
			str += constructOrderForum(selector, depth+1);	
		});
		selector = first_selector;
	}
	
	str = str.replace(regexp, "");
	str += "}";
	return str;
	
}