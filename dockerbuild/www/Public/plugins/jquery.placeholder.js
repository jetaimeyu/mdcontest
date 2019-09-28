(function($){
	$.fn.placeholder = function(){
		function valueIsPlaceholder(input){
			return ($(input).val() == $(input).attr("placeholder"));
		}
		return this.each(function(){
					//用占位文本替换其值
					//可选的reload参数用来解决firefox和ie缓存域值的问题
			function showPlaceholder(input, reload){
				if($(input).val() == "" || (reload && valueIsPlaceholder(input))){
					$(input).val($(input).attr("placeholder")).addClass('placeholder');
				}
			};
			
			$(this).focus(function(){
				if($(this).val() == $(this).attr("placeholder")){
					$(this).val("").removeClass('placeholder');
				}
			});
			
			$(this).blur(function(){
				showPlaceholder($(this),false);
			});
			showPlaceholder($(this),false);
			
			//禁止表单提交默认值
			$(this).submit(function(){
				if($(this).val() == $(this).attr("placeholder")){
					$(this).val('').removeClass('placeholder');
				}
			});
		});
	};
})(jQuery);
