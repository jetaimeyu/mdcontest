(function($){
	$.extend({
		/**
		 * message:消息
		 * color:提示框的背景色：yellow,green,red
		 * [speed]:可选参数，提示框显示与隐藏速度默认{showSpeed:300,hideSpeed:2000}
		 * [callback]:可选参数，回调函数
		 * postionid:可选参数，消息显示的位置对象id，默认为body
		 * */
		"tipShow":function(config){
			var config = $.extend({},defaults,config);
			//判断页面是否含有提示框容器
			if(!$(".tip-message-box").get(0)){
				var boxHtml = "<div style='width:auto;min-width:200px;' class='tip-message-box'></div>";
				var postionObj;
				if(config.positionid!="body"){
					postionObj=$("#"+config.positionid);
				}else{
					postionObj=$("body");
				}
				if(!postionObj){
					return;
				}
				postionObj.append(boxHtml);
				
				var width = postionObj.width();
				var boxWidth = $(".tip-message-box").width();
				var boxLeft = (width-boxWidth)/2;
				
				$(".tip-message-box").css({
					"left":boxLeft+"px"
				});
				
			}
			
			var messageHtml = "<div class='message' style='display:none;'>"+ config.message +"</div>";
			$(".tip-message-box").html(messageHtml);
			
			$(".tip-message-box .message").hover(function(){
				clearTimeout(timer);
				$(this).stop();
			},function(){
				$(".tip-message-box .message").slideUp("normal","swing",function(){
					$(".tip-message-box").remove();
					if(config.callback&&$.isFunction(config.callback)){
						config.callback();
					}
				},config.hideSpeed);
			});
			if(config.color=="yellow"){
				$(".tip-message-box .message").addClass("yellow");
			}else if(config.color=="green"){
				$(".tip-message-box .message").addClass("green");
			}else{
				$(".tip-message-box .message").addClass("red");
			}
			$(".tip-message-box .message").slideDown(config.showSpeed,'linear',function(){
				timer = setTimeout(function(){
					clearTimeout(timer);
					$(".tip-message-box .message").slideUp("normal","swing",function(){
						$(".tip-message-box").remove();
						if(config.callback&&$.isFunction(config.callback)){
							config.callback();
						}
					});
				},config.hideSpeed);
			});
		}
	});
	var timer;
	//默认配置
	var defaults={
		"positionid":"body",//消息框出现的位置
		"message":"",
		"color":"red",
		"showSpeed":300,
		"hideSpeed":1000,
		"callback":function(){
		}
	}
})(window.jQuery)