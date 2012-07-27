function Ajax(options) {
	if ($.browser.msie && window.XDomainRequest) {
            var xdr = new XDomainRequest();
            xdr.open(options.type, options.url);
            xdr.onload = function() {
				var data = xdr.responseText;
				if (options.dataType == 'json') {
					options.success(eval('(' + data + ')'));
				}
				else {
					options.success(data);
				}
            };
			var str = options.data;
			if (typeof(options.data) == 'object') {		
				str = '';
				for (var key in options.data) {			
					if (typeof(options.data[key]) == 'object') {
						str += encodeURIarray(options.data[key], key);
					}
					else {
						str += key + '=' + encodeURIComponent(options.data[key]) + '&';
					}
				}
				str = str.replace(/&$/, "");
			}
			// alert(str);
            xdr.send(str);
     } else {
		$.ajax({
		  url:  options.url,
		  type: options.type,
		  data: options.data,
		  dataType: options.dataType,
		  error: function(XMLHttpRequest, textStatus, errorThrown) {
				var e = {};
				switch(textStatus) {
					case 'timeout': 
						e = {num: 2, msg: errorThrown};
					break;
					case 'notmodified': 
						e = {num: 3, msg: errorThrown};
					break;
					case 'parsererror': 
						e = {num: 4, msg: errorThrown};
					break;
					default: 
						e = {num: 5, msg: errorThrown};
				}
				new Error(e);
		  },
		  timeout: 4000,
		  success: function(data) {	  
				options.success(data);
		  }
	   });
	}
}