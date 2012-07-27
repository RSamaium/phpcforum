$(function(){

	<!-- BEGIN growl -->
	$.gritter.add({
		title: '{growl.TITLE} ({growl.AUTHOR})',
		text: '{growl.TEXT}'
	});
	<!-- END growl -->
	
	<!-- IF USER_LOGIN -->
	var dockOptions =
    { align: 'top',
		labels: true,
		size: 35,
		distance: 100
    };
	$('#profile_menu_').jqDock(dockOptions);
	<!-- ENDIF -->
	
	
	
	$("#form :input, .form :input[title], .viewtooltip").tooltip({ 
		position: "center right", 
		offset: [-20, 5], 
		effect: "fade", 
		opacity: 0.7, 
		tip: '.tooltip' 
 
	});	
	
	$(".report").tooltip({ 
		position: "center right", 
		offset: [-2, 10], 
		effect: "fade", 
		opacity: 0.7, 
		tip: '.tooltip_report' 
 
	});	
	
	
	 $("#quick_login").click(function() {	 
			$(this).expose({api: true}).load();
	}); 
	
	$("#quick_login").expose({ 
		  onBeforeLoad: function(event) { 
			this.getExposed().animate({
				opacity: 1
			}, 1500); 
			$("#quick_login_options").slideDown("slow"); 
		},
		  onBeforeClose: function(event) { 
			this.getExposed().animate({
				opacity: 0.4
			}, 1500); 
			 $("#quick_login_options").slideUp("slow"); 
		} 
		 
	});
	
    $("#pm").tooltip({ 
        tip: '.tooltip_pm',  
        offset: [0, -10], 
        effect: 'fade' ,
		opacity: 0.8
    });

<!-- IF QUICK_MOD_TOOLS -->	
	$("#mod_tools").overlay({
		expose: {
			color: '#333',
			loadSpeed: 200,
			opacity: 0.9
		},
		
		onBeforeLoad: function() {
			var option = $("select[name='quick_mod_tools']").val();
            var wrap = this.getContent().find("#content_window_confirm"); 
			$("#content_quick_modo input[name=mod]").val(option);
			var content_global = $("#content_quick_modo .content_global").html();
			var content = $("#content_quick_modo ." + option).html();
			wrap.html(content_global + content);
		}
	});
<!-- ENDIF -->

	$(".mod_delete_msg").overlay({
		expose: {
			color: '#333',
			loadSpeed: 200,
			opacity: 0.9
		},
		
		onBeforeLoad: function() {
			var option = 'msg_delete';
            var wrap = this.getContent().find("#content_window_confirm"); 
            var post_id = this.getTrigger().attr('data-id'); 
			$("#content_quick_modo input[name=mod]").val(option);
			$("#content_quick_modo input[name='delete_post_id']").val(post_id);
			var content_global = $("#content_quick_modo .content_global").html();
			var content = $("#content_quick_modo ." + option).html();
			wrap.html(content_global + content);
		}
	});
	
	$(".openoverlay[rel]").overlay({
		expose: {
			color: '#333',
			loadSpeed: 200,
			opacity: 0.9
		},
		oneInstance: false, 
		top: overlay_position('top'),
		left: overlay_position('left'),
		onBeforeLoad: function() {
			var size = parseInt($(".openoverlay").attr("size"));
			$(".global_overlay_content").css('width', size);
			var wrap = this.getOverlay().find(".global_overlay_content"); 
            wrap.load(this.getTrigger().attr("href")); 
		}
	});
	
	function overlay_position(corner) {
		var attr = $(".openoverlay").attr(corner);
		if (typeof attr == 'undefined')
			return corner == 'top' ? '10%' : 'center';
		else
			return  parseInt(attr);
	}
	
	//$("#date").datepicker();
	//$("#date").click(function() {alert('h');});

	$(window).load(function() { 
		$(".classic").animate({
			opacity: 1
		}, 2000);  
    });
	
	$("a[rel]").overlay({
		effect: 'apple',
		
		 onBeforeLoad: function() { 
 
            var wrap = this.getContent().find(".contentWrap"); 
			var trigger = this.getTrigger();
            wrap.load(trigger.attr("href")); 
			$("#href_next_profile").attr("href", "memberlist.php?mode=direct_viewprofile_p2&u=" + trigger.attr("user_id"));
        } 
		
	});
	
	<!-- IF NEW_REPORTS -->
	$("#report_popup").overlay({ 
 
    top: 150, 
    expose: { 
 
        color: '#000',  
        opacity: 0.5 
    }, 
 
    closeOnClick: true, 
    api: true ,
	
	 /*onBeforeLoad: function(event) { 
		$("#reportlist").slideDown("slow"); 
	}*/
 
	}).load();
	<!-- ENDIF -->
	
	<!-- IF USER_ACTIV -->
	$("#activ_compte").overlay({ 
		expose: { 
			color: '#000',  
			opacity: 0.5 
		}, 
		closeOnClick: true, 
		api: true,
		load: true
	}).load();
	<!-- ENDIF -->
	
	
	$('#auto_users').autocomplete({ serviceUrl:'ajax/users.php' });
	$('#auto_search').autocomplete({ serviceUrl:'ajax/search.php' });
		/*ac.disable();
		ac.enable();*/
		
   $('.lightbox').lightBox({
		imageLoading: '{I_LIGTH_BOX_LOADING}',
		imageBtnClose: '{I_LIGTH_BOX_CLOSE}',
		imageBtnPrev: '{I_LIGTH_BOX_PREV}',
		imageBtnNext: '{I_LIGTH_BOX_NEXT}',
		txtImage: '',
		txtOf: '/'
	});
		
	

  
   
	
	
});


function textConfirmDeletePm(checked) {
	if (checked)
		return "{L_CONFIRM_DEL}";
	else
		return "{L_CONFIRM_DEL_NO_CHECKED}";
}
	