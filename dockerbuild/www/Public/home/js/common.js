
/**
 * 同步分页
 * cont:分页所在容器id
 * sum:总页数
 * color:分页颜色（默认：#AF0000）
 * group:连续显示分页数（默认：3）
 * 
 */
function initLinkPage(page){
	var skip=page.skip!=undefined?page.skip:true;
	var first=page.first!=undefined?page.first:'首页';
	var last=page.last!=undefined?page.last:'尾页';
	laypage({
	    cont: $("#"+page.cont), //容器。值支持id名、原生dom对象，jquery对象,
	    pages: page.sum, //总页数
	    skip: skip, //是否开启跳页
	    skin: '#47b9f8',//颜色
	    groups: page.group || 3, //连续显示分页数
	    first:first,
	    last:last,
	    curr: function(){ //通过url获取当前页，也可以同上（pages）方式获取
	        var page = location.search.match(/page=(\d+)/);
	        return page ? page[1] : 1;
	    }(), 
	    jump: function(e, first){ //触发分页后的回调
	        if(!first){ //一定要加此判断，否则初始时会无限刷新
	            location.href = '?page='+e.curr;
	        }
	        if(page.view){
	            var pageurl = location.search.match(/page/);
	        	if(pageurl != null){
	        		//定位
	            	var y=$("#"+page.view).offset().top;
	            	window.scrollTo(0,y);
	        	}
	        }
	    }
	});
}
/**
 * ajax分页
 * url:请求地址
 * cont:分页所在容器id
 * view:内容所在容器id
 * color:分页颜色（默认：#AF0000）
 * group:连续显示分页数（默认：3）
 */
function initAjaxPage(page){
	$.getJSON(page.url, {
        page: page.curr || 1 //向服务端传的参数，此处只是演示
    }, function(res){
    	$("#"+page.view).html($("#"+page.tmp).tmpl(res.data));
    	if(page.curr!=undefined){
    		//定位
        	var y=$("#"+page.view).offset().top;
        	window.scrollTo(0,y);
    	}
        laypage({
            cont: page.cont, 
            pages: res.sum, //通过后台拿到的总页数
            curr: page.curr || 1, //当前页
    	    skip: true, //是否开启跳页
    	    skin: '#64bfc3',//颜色
    	    groups: page.group || 3, //连续显示分页数
            jump: function(obj, first){ //触发分页后的回调
                if(!first){ //点击跳页触发函数自身，并传递当前页：obj.curr
                	page.curr=obj.curr;
                	initAjaxPage(page);
                }
            }
        });
    });
}
/*
 * 利用Get的方式获取数据
 */
function getAjaxData(url, callback, needToken, isAsync, contentType) {
    ajaxData(url, false, callback, "get", needToken, isAsync, contentType);
};
/*
 * 向服务器Post数据，并获取返回数据
 */
function postAjaxData(url, data, callback, needToken, contentType) {
    ajaxData(url, data, callback, "post", needToken, contentType);
};
function ajaxData(url, data, callback, ajaxType, needToken, isAsync, contentType) {
    jQuery.support.cors = true;

    // if (typeof (data) == 'object') {
    //     data = JSON.stringify(data);
    // }
    //验证是否需要加token
    var header = {};

    if (needToken) {
        header.Authorization = "Bearer " + getToken();
    }
    if (!isAsync && isAsync != false) {
        isAsync = true;
    }
    $.ajax(url, {
        dataType: 'JSON', //服务器返回json格式数据
        contentType: contentType || 'application/x-www-form-urlencoded', //  提交到服务器的数据的格式
        type: ajaxType, //请求类型
        timeout: 10000, //超时时间设置为10秒；
        headers: header,
        data: data,
        cache: false,
        async: false,
        crossDomain: true,
        success: function (data) {
            callback(data);
        },
        error: function (xhr, type, errorThrown) {
            callback({
                State: -99,
                ErrorMessage: xhr.statusText
            });
        }
    });

};

//提示弹窗提示参数 content 提示内容json2,type页面来源 1为注册页 2 为报名页
function openPopup(content,type){
    if(type==2){
        $(".goPersoninfo").css("display","none");
    }
    $(".popupContent .popupContentTitle span").text(content.popupContentTitle);
    $(".markedWords .firsttip").html(content.firsttip);
    $(".markedWords .secondtip").html(content.secondtip);
    $(".markedWords .thirdtip").text(content.thirdtip);
    $(".markedWords .gobtn").html(content.btn);
    $(".popupbg").css("display","block");
    $(".popupContent").css("display","block");
}
//关闭弹窗
function closePopup(skipurl){
    $(".popupbg").css("display","none");
    $(".popupContent").css("display","none");
    if(skipurl){
        window.location.href = skipurl;
    }

}





