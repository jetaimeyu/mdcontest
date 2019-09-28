// JavaScript Document
jQuery.fn.customInput = function(){
	return $(this).each(function(){	
		if($(this).is('[type=checkbox],[type=radio]')){
			var input = $(this);
			
			// 使用输入的ID得到相关的标签
			var label = $('label[for='+input.attr('id')+']');
			//绑定自定义事件，触发它，绑定点击，焦点，模糊事件				
			input.bind('updateState', function(){	
				input.is(':checked') ? label.addClass('checked') : label.removeClass('checked'); 
			})
			.trigger('updateState');
			input.bind('click', function(){ 
				$('input[name="'+ $(this).attr('name') +'"]').trigger('updateState'); 
			});
		}
	});
};

//显示弹层
function showdiv(msg,flag){
	if(flag == '2'){
		$('#trueimg').hide();
		$('#falseimg').show();
	}else{
		$('#trueimg').show();
		$('#falseimg').hide();
	}
	$('#msg').html(msg);
	$('#tandiv').show();
}
$('input').focus(function () {
	$(this).css('border','1px solid #5EAFF6');
})
$('select').focus(function () {
	$(this).css('border','1px solid #5EAFF6');
})
$('select').blur(function () {
	$(this).css('border','1px solid #e5e5e5');
})
$('input').blur(function () {
	$(this).css('border','1px solid #e5e5e5');
})
$('textarea').focus(function () {
	$(this).css('border','1px solid #5EAFF6');
})
$('textarea').blur(function () {
	$(this).css('border','1px solid #e5e5e5');
})
