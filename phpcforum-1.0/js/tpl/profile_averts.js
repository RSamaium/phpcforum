$(function() {
	$(".confirm_avert").overlay({
		expose: {
			color: '#333',
			loadSpeed: 200,
			opacity: 0.9
		},
		
		onBeforeLoad: function() {
			var option = this.getTrigger().attr('data-id');
            var wrap = this.getContent().find("#content_window_confirm"); 
			$("#window_confirm input[name=mode]").val(option);
			var content = $("#confirm_window div[data-id='" + option + "']").html();
			wrap.html(content);
		}
	});
});