//监听ajax请求
$(document).ajaxComplete(function(event,response, settings){
	var s = response.responseText;
    if (s.indexOf("sessiontimeout", 0) != -1){
    	window.location.href ='/aeci';
    }
});