$(function(){
	displayGraphics(true);

	$("#window_design").dialog({ height: 530,  position: [350, 50]});
	$("#window_selector_design").dialog({ height: 530, width: 250, position: [50, 50] });
	
	$(".ui-dialog").css('position', 'fixed');
	
	$(".ui-resizable").stop(function() {
		$(".ui-dialog").css({position:"fixed"});
	});
	
	//$(".ui-dialog").css('opacity', 0.3);
	/*$(".ui-dialog").hover(function() {
		$(this).animate({'opacity': 1});
	}, function() {
		$(this).delay(3000).animate({'opacity': 0.3})
	});*/
	
	var select_div = "";
	var li = "";
	var color_li = $("li > a").css('color');
	
	$("#window_selector_design li").each(function() {
		var selector = $(this).attr('data-id');
		//alert(this);
		$("li[data-id='" + selector + "'] > a").hover(function() {
			//$(selector).css('border', '1px solid red');
			$("#view_select").css('height', $(selector).css('height'));
			$("#view_select").css('width', $(selector).css('width'));
			$("#view_select").css('top',  $(selector).position().top);
			$("#view_select").css('left',  $(selector).position().left);
			$("#view_select").show();
		}, function() {
			$("#view_select").hide();
		});
		
		$("li[data-id='" + selector + "'] > a").click(function() {
			
			var exception = $(this).parent().attr("data-exception");
			var activ = $(this).parent().attr("data-activ");
			if (activ == undefined) {
				activ = false;
			}
			$("li > a").css('color', color_li);
			$(this).css('color', 'red');
			select_div = selector;
			li = $(this).parent();
			var array_except = exception.split(",");
			displayGraphics(false, array_except, activ);
			initializeGraphicPanel(selector);
		});
	});
	
	
	$('#backgroundColor').ColorPicker({
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			var value = $('#backgroundColor div').css('backgroundColor');
			saveChanged("backgroundColor", rgbToHex(value));
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			if (!$('#backgroundColor_null').attr('checked')) {
				$(select_div).css('backgroundColor', '#' + hex);
			}
			$('#backgroundColor div').css('backgroundColor', '#' + hex);	
		}
	});
	
	$('#backgroundColor_null').click(function() {
		if ($(this).attr('checked')) {
			  $(select_div).css('backgroundColor', 'transparent');
			  saveChanged("backgroundColor", "");
		}
		else {
			var value = $('#backgroundColor div').css('backgroundColor');
			$(select_div).css('backgroundColor', value);
			saveChanged("backgroundColor", rgbToHex(value));
		}
	});
	
	$('input[name="repeat-x"], input[name="repeat-y"]').click(function() {
		var str1 = "";
		var str2 = "";
		var val = $('#backgroundImage').val();
		if ($(this).attr('name') == 'repeat-x') {
			str1 = "repeat-y";
			str2 = "repeat-x";
		}
		else {
			str1 = "repeat-x";
			str2 = "repeat-y";
		}
		if ($(this).attr('checked')) {
			if ($('input[name="' + str1 + '"]').attr('checked')) {
				$(select_div).css('background-repeat', 'repeat');
			}
			else {
			    $(select_div).css('background-repeat', str2);
			}
			 $('input[name="no-repeat"]').attr('checked', false);
		}
		else {
			if ($('input[name="' + str1 + '"]').attr('checked')) {
				$(select_div).css('background-repeat', str1);
			}
			else {
			    $(select_div).css('background-repeat', 'no-repeat');
				 $('input[name="no-repeat"]').attr('checked', true);
			}
		}
		saveChanged("backgroundRepeat", $(select_div).css('background-repeat'));
		if (val == "") {
			clearRepeat();
		}
	});
	
	$('input[name="no-repeat"]').click(function() {
		var val = $('#backgroundImage').val();
		if ($(this).attr('checked')) {
			 $('input[name="repeat-x"]').attr('checked', false);
			 $('input[name="repeat-y"]').attr('checked', false);
			 $(select_div).css('background-repeat', 'no-repeat');
			 saveChanged("backgroundRepeat", 'no-repeat');
		}
		else {
			if (!$('input[name="repeat-x"]').attr('checked') &&  !$('input[name="repeat-y"]').attr('checked')) {
				 $('input[name="no-repeat"]').attr('checked', true);
			}
		}
		if (val == "") {
			clearRepeat();
		}
	});
	
	$('input[name="activ"]').click(function() {
		var val = $('#backgroundImage').val();
		if ($(this).attr('checked')) {	
			 saveChanged("selector_activ", "1");
			 li.attr('data-activ', 1);
		}
		else {
			 saveChanged("selector_activ", "0");
			  li.attr('data-activ', 0);
		}
	});
	
	$('#backgroundImage').focusout(function() {
		var val = $('#backgroundImage').val();
		if (val != "") {
			$(select_div).css('backgroundImage', 'url(' + val + ')');
			saveChanged("backgroundImage", val);
			if (!$('input[name="repeat-x"]').attr('checked') &&  !$('input[name="repeat-y"]').attr('checked')) {
				 $('input[name="no-repeat"]').attr('checked', true);
				 saveChanged("backgroundRepeat", 'no-repeat');
			}
		}
		else {
			$(select_div).css('backgroundImage', 'none');
			saveChanged("backgroundImage", 'none');
			clearRepeat();
		}
	});
	
	$('#borderColor').ColorPicker({
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			var value = $('#borderColor div').css('backgroundColor');
			saveChanged("borderColor", rgbToHex(value));
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('#borderColor div').css('backgroundColor', '#' + hex);
			$(select_div).css('borderColor', '#' + hex);
			
		}
	});
	
	$('#color').ColorPicker({
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			var value = $('#color div').css('backgroundColor');
			saveChanged("color", rgbToHex(value));
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('#color div').css('backgroundColor', '#' + hex);
			$(select_div).css('color', '#' + hex);
			if (select_div == '.button_header') {
				$(select_div + ' a').css('color', '#' + hex);
			}
			$(select_div).children().css('color', '#' + hex);
			
		}
	});
	

	
	$("#slider-range-min").slider({
			range: "min",
			min: 1,
			max: 100,
			slide: function(event, ui) {
				$("#fontSize").text(ui.value + 'px');
				if (select_div == '.button_header') {
					$(select_div + ' a').css('fontSize', ui.value + 'px');
				}
				$(select_div).css('fontSize', ui.value + 'px');
			},
			stop: function(event, ui) {
				saveChanged("fontSize", ui.value);
			}
		});
	$("#slider-height").slider({
			range: "min",
			min: 5,
			max: 500,
			slide: function(event, ui) {
				$("#height").text(ui.value + 'px');
				$(select_div).css('height', ui.value + 'px');
			},
			stop: function(event, ui) {
				saveChanged("height", ui.value);
			}
		});
		
		$("#slider-width").slider({
			range: "min",
			min: 1,
			max: 2000,
			slide: function(event, ui) {
				$("#width").text(ui.value + 'px');
				$(select_div).css('width', ui.value + 'px');
			},
			stop: function(event, ui) {
				saveChanged("width", ui.value);
			}
		});
	
	$("#slider-opacity").slider({
			range: "min",
			min: 0,
			max: 100,
			slide: function(event, ui) {
				$("#opacity").text(ui.value + '%');
				$(select_div).css('opacity', ui.value/100);
			},
			stop: function(event, ui) {
				saveChanged("opacity", ui.value);
			}
		});
		
	$("#slider-border-size").slider({
			range: "min",
			min: 0,
			max: 10,
			slide: function(event, ui) {
				$("#borderSize").text(ui.value + 'px');
				$(select_div).css('borderWidth', ui.value + 'px');
			}		,
			stop: function(event, ui) {
				saveChanged("borderSize", ui.value);
			}
		});
	
	
	function initializeGraphicPanel(selector) {
		clear();
		var bg_color = $(selector).css('backgroundColor');
		var color = $(selector).css('color');
		var border_color = $(selector).css('borderColor');
		var border_size = $(selector).css('borderWidth');
		var font_size = $(selector).css('fontSize');
		var height = $(selector).css('height');
		var width = $(selector).css('width');
		var opacity = $(selector).css('opacity');
		var bg_img = $(selector).css('backgroundImage');
		var bg_repeat = $(selector).css("background-repeat");
		
		if (border_size == '') {
			$(selector).css('border', '0px solid #000');
			border_size = '0px';
		}
		bg_img = String(bg_img.match(new RegExp("\\((.*?)\\)", "gi")));
		bg_img = bg_img.replace("(", "");
		bg_img = bg_img.replace(")", "");
		if (bg_img == 'null') {
			bg_img = "";
		}
		$("#backgroundImage").val(bg_img);
		
		if (bg_repeat == 'repeat-x') {
			$('input[name="repeat-x"]').attr('checked', true);
		}
		if (bg_repeat == 'repeat-y') {
			$('input[name="repeat-y"]').attr('checked', true);
		}
		if (bg_repeat == 'no-repeat') {
			$('input[name="no-repeat"]').attr('checked', true);
		}
	
		colorPick(bg_color, '#backgroundColor');
		colorPick(color, '#color');
		colorPick(border_color, '#borderColor');
		
		$("#fontSize").text(font_size);
		$("#slider-range-min").slider("value", parseInt(ident(font_size)));
		$("#height").text(height);
		$("#slider-height").slider("value", parseInt(ident(height)));
		$("#width").text(width);
		$("#slider-width").slider("value", parseInt(ident(width)));
		$("#borderSize").text(border_size);
		$("#slider-border-size").slider("value", parseInt(ident(border_size)));
		$("#opacity").text(opacity * 100);
		$("#slider-opacity").slider("value", parseInt(opacity) * 100);
	}
	
	function clear() {
		$('#backgroundColor').css('backgroundColor', 'transparent');
		$('#backgroundColor_null').attr('checked', false);
		$('#color').css('backgroundColor', 'transparent');
		clearRepeat();
	}
	
	function clearRepeat() {
		$('input[name="no-repeat"]').attr('checked', false);
		$('input[name="repeat-x"]').attr('checked', false);
		$('input[name="repeat-y"]').attr('checked', false);
	}
	
	function ident(text) {
		return text.match(new RegExp("[0-9]+", "gi"));
	}	
	
	function colorPick(color, id) {
		if (color == 'rgba(0, 0, 0, 0)') {
			$(id + '_null').attr('checked', true);
		}
		$(id).ColorPickerSetColor(color);
		$(id + ' div').css('backgroundColor', color);
	}
	
	function displayGraphics(ini, exception, activ) {
		var fields = ["activ", "bg", "text", "opacity", "height", "width", "border"];
		
		$('input[name="activ"]').attr('checked', activ == 1);
		
		for (var i=0 ; i < fields.length ; i++) {
			if (ini) {
				$("#field_" + fields[i]).hide();
			}
			else {
				if ($.inArray(fields[i], exception) >= 0) {			
					$("#field_" + fields[i]).hide();
				}
				else {
					$("#field_" + fields[i]).show();
				}
			}
		}
	}
	
	function saveChanged(field, value) {
		$.ajax({
			  url: 'ajax/adm.php',
			  type: 'POST',
			  data: {f: "changeTmpDesign", selector_name: select_div, field: field, value: value},
			  success: function(data) {

			  }
		});
	}
	function rgbToHex(srgb) { 
	  var rgbvals = /rgba?\((.+),(.+),(.+)(,(.+))?\)/i.exec(srgb); 
	  if (rgbvals == null) {
		return;
	  }
	  var r = parseInt(rgbvals[1]).toString(16);
	  var g = parseInt(rgbvals[2]).toString(16);
	  var b = parseInt(rgbvals[3]).toString(16);
	  
	  if (r.length == 1) {
		r = "0" + r;
	  }
	  if (g.length == 1) {
		g = "0" + g;
	  }
	  if (b.length == 1) {
		b = "0" + b;
	  }
	  
	  return (r + g + b).toUpperCase(); 
	  
	} 
});