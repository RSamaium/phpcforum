(function($) {
  $.fn.sorted = function(customOptions) {
    var options = {
      reversed: false,
      by: function(a) { return a.text(); }
    };
    $.extend(options, customOptions);
    $data = $(this);
    arr = $data.get();
    arr.sort(function(a, b) {
      var valA = options.by($(a));
      var valB = options.by($(b));
      if (options.reversed) {
        return (valA < valB) ? 1 : (valA > valB) ? -1 : 0;				
      } else {		
        return (valA < valB) ? -1 : (valA > valB) ? 1 : 0;	
      }
    });
    return $(arr);
  };
})(jQuery);

var $sortedData = null;
var $list_pm = null

$(function() {

	var check_all = false;
	$("#check_all_pm").click(function() {
		var checkbox = $("#setting_pm input:checkbox");
		check_all = check_all ? false : true;
		for (var i=0 ; i < checkbox.length ; i++) {
			checkbox[i].checked = check_all;
		}
	});


	var button_ok = $("#confirm p:eq(1) span").html();
	$("#confirm_pm").overlay({
		expose: {
			color: '#333',
			loadSpeed: 200,
			opacity: 0.9
		},
		
		onBeforeLoad: function() {
			var input = $("#setting_pm input:checked");
			var str_pm = "<ul>";
			
			if (input.length > 0) {
				for (var i=0 ; i < input.length ; i++) {
					str_pm  += "<li>" + $("#title_" + input[i].value).text() + "</li>";
				}
				str_pm += "</ul>";
				$("#text_confirm").html(str_pm);
				$("#confirm p:eq(0)").html(textConfirmDeletePm(true));
				$("#confirm p:eq(1) span").html(button_ok);
				
			}
			else {
				$("#text_confirm").html("");
				$("#confirm p:eq(0)").html(textConfirmDeletePm(false));
				$("#confirm p:eq(1) span").html("");
			}
		}
	});

	$list_pm = $('#list_pm');
	var $filterType = $('#filter input[name="type"]');
	var $filterSort = $('#filter input[name="sort"]');

  var $data = $list_pm.clone();
  initPositionPm($data);

  $filterType.add($filterSort).change(function(e) {
  
	var reverse = false;
  
    if ($($filterType+':checked').val() == 'all') {
      var $filteredData = $data.find('li');
    } else {
		
      var $filteredData = $data.find('li[data-type=' + $($filterType+":checked").val() + ']');
    }
	
	var input_reverse = $('#filter input[name="sort"]:checkbox');
	reverse = input_reverse[0].checked;

    if ($('#filter input[name="sort"]:checked').val() == "name") {
      var $sortedData = $filteredData.sorted({
        by: function(v) {
          return $(v).find('strong').text().toLowerCase();
        },
		reversed: reverse
      });
    } 
	else if ($('#filter input[name="sort"]:checked').val() == "author") {
      var $sortedData = $filteredData.sorted({
        by: function(v) {
          return $(v).find('.author_name').text().toLowerCase();
        },
		reversed: reverse
      });
    }
	else {
	  var $sortedData = $filteredData.sorted({
        by: function(v) {
          return parseFloat($(v).find('span[data-type=date]').text());
        },
		reversed: reverse ? false : true
      });
    }

    $list_pm.quicksand($sortedData, {
      duration: 800,
      easing: 'easeInOutQuad'
    });
	
  });

});

function initPositionPm(data) {
	$sortedData = data.find('li').sorted({
        by: function(v) {
          return parseFloat($(v).find('span[data-type=date]').text());
        },
		reversed: true
      });
	  
	setTimeout("initSort()", 800);
}

function initSort() {
	$list_pm.quicksand($sortedData, {
      duration: 400,
      easing: 'easeInOutQuad'
    });
	
}

/*function initTooltipPm() {
	$(".pm-grid li a").tooltip({ 
		position: "center right", 
		offset: [-2, 10], 
		effect: "fade", 
		opacity: 0.7, 
		tip: '.tooltip' 
	});	
}*/

 function animationPMIsFinish(id_anim, nb_total_anim) {
	if (id_anim == nb_total_anim-1) {
		//initTooltipPm();
	}
};




