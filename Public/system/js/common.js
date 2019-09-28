
/**
 * 打开窗口(在iframe之上)
 */
var currrentWindowId = null;
function openDialog(config){
	//检查是否含有windowId属性，无则默认为window
	if(!config.windowId){
		config.windowId="window"
	}
	currrentWindowId = config.windowId;
	var href = config.href;
	delete config.href; 
	
	var content = '<iframe id="'+ config.windowId +'_iframe" scrolling="yes" frameborder="0"  src="'+href+'" style="width:100%;height:99%;">';
	var defaults = {
		modal:true,//默认模式窗口
		width:600,//默认宽600，高400    
	    height:400,
	    collapsible:true,//可以收缩
	    maximizable:true,//最大化
	    shadow:true,//显示阴影
	    border:"thin",//边框
	    iconCls:"icon-extend-window",
	    loadingMessage:"正在加载，请稍后...",
	    content:content, 
	}
	
	var dialogConfig = $.extend({},defaults,config);
	
	if(window.parent!=window.self){
		//判断窗口div是否存在
		if(!parent.$("#"+config.windowId).get(0)){
			var windowHtml = "<div id='"+ config.windowId +"' style='overflow:hidden;'></div>";
			parent.$("body").append(windowHtml);
		}
		return parent.$("#"+config.windowId).dialog(dialogConfig);
	}else{
		//判断窗口div是否存在
		if(!$("#"+config.windowId).get(0)){
			var windowHtml = "<div id='"+ config.windowId +"' style='overflow:hidden;'></div>";
			$("body").append(windowHtml);
		}
		return $("#"+config.windowId).dialog(dialogConfig);
	}
	
}

/**
 * 关闭window
 */
function closeDialog(config){
	var defaults = {
		"windowId":"window",//默认关闭窗口的id
		"needRefresh":false,//是否需要刷新
		"controlType":"datagrid",//刷新的控件类型，默认为：datagrid,例如，datagrid,treegrid
		"gridId":"datagrid"//需要刷新的gridid,默认为datagrid
	}
	
	config = $.extend({},defaults,config);
	
	if(window.parent!=window.self){
		parent.$("#"+config.windowId).dialog("close");
		if(config.needRefresh){
			parent.$(".right-frames iframe").each(function(){
				if(!$(this).is(":hidden")){
					/* var url = $(this).get(0).contentWindow.location.href;
					$(this).attr('src',url); */
					if(config.controlType=="datagrid"){
						//刷新datagrid
						var datagrid = $(this).get(0).contentWindow.$("#"+config.gridId);
						if(datagrid.length){
							datagrid.datagrid("reload");
						}
					}else if(config.controlType=="treegrid"){
						//刷新treegrid
						var treegrid = $(this).get(0).contentWindow.$("#"+config.gridId);
						if(treegrid.length){
							treegrid.treegrid("reload");
						}
					}
				}
			});
		}
	}else{
		$("#"+config.windowId).dialog("close");
		if(config.needRefresh){
			$(".right-frames iframe").each(function(){
				if(!$(this).is(":hidden")){
					if(config.controlType=="datagrid"){
						//刷新datagrid
						var datagrid = $(this).get(0).contentWindow.$("#"+config.gridId);
						if(datagrid.length){
							datagrid.datagrid("reload");
						}
					}else if(config.controlType=="treegrid"){
						//刷新treegrid
						var treegrid = $(this).get(0).contentWindow.$("#"+config.gridId);
						if(treegrid.length){
							treegrid.treegrid("reload");
						}
					}
				}
			});
		}
	}
}

/**
 * 提交from
 * */
function submitForm(formid){
	if(!formid){
		return;
	}
	var form = parent.$("#"+ currrentWindowId +" iframe")[0].contentWindow.window.$("#"+formid);
	if(form){
		form.submit();
	}
	/*if(window.parent!=window.self){
		parent.$("#"+formid).submit();
	}else{
		$("#"+formid).submit();
	}*/
}

/**
 * 重置from
 * */
function resetForm(formid){
	if(!formid){
		return;
	}
	
	var form = parent.$("#"+ currrentWindowId +" iframe")[0].contentWindow.window.$("#"+formid);
	if(form){
		form.form("reset");
	}
	
	/*if(window.parent!=window.self){
		parent.$("#"+formid).form("reset");
	}else{
		$("#"+formid).form("reset");
	}*/
}

/**
 * 确认框
 * */
function confirm(title,message,callback){
	if(window.parent!=window.self){
		window.parent.$.messager.confirm(title,message,callback);
	}else{
		$.messager.confirm(title,message,callback);
	}
}
$.fn.serializeJson=function(){ 
        var serializeObj={}; // 目标对象 
        var array=this.serializeArray(); // 转换数组格式 
        // var str=this.serialize(); 
        $(array).each(function(){ // 遍历数组的每个元素 {name : xx , value : xxx} 
                if(serializeObj[this.name]){ // 判断对象中是否已经存在 name，如果存在name 
                      if($.isArray(serializeObj[this.name])){ 
                             serializeObj[this.name].push(this.value); // 追加一个值 hobby : ['音乐','体育'] 
                      }else{ 
                              // 将元素变为 数组 ，hobby : ['音乐','体育'] 
                              serializeObj[this.name]=[serializeObj[this.name],this.value]; 
                      } 
                }else{ 
                        serializeObj[this.name]=this.value; // 如果元素name不存在，添加一个属性 name:value 
                } 
        }); 
        return serializeObj; 
};     



