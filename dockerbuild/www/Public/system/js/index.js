$(function(){
	var curTab ;
	/*左侧查询菜单*/
	$("#queryMenu").click(function(){
		var searchText = $.trim($("#queryText").val());
		if(searchText){
			$(".menus-list .menu-box ul li a").each(function(){
				var linkText = $.trim($(this).text());
				if(linkText.indexOf(searchText)!=-1){
					$(this).css({
						color:"red",
						fontWeight:"bold"
					});
					$(this).closest("ul").slideDown();
				}else{
					$(this).css({
						color:"#333",
						fontWeight:"normal"
					});
				}
			});
		}else{
			$(".menus-list .menu-box ul li a").each(function(){
				$(this).css({
					color:"#333",
					fontWeight:"normal"
				});
				$(this).closest("ul").slideUp();
			});
		}
	});
	
	$("#queryText").keyup(function(event){
        if(event.keyCode == 13){
        	$("#queryMenu").trigger("click");
        }
    });
	
		/*头部右侧用户信息*/
	$(".main-top-logo .top-user-true").hover(function(){
		$(this).addClass("top-user-hover");
		$(".top-user-down-true").show();
	},function(){
		$(this).removeClass("top-user-hover");
		$(".top-user-down-true").hide();
	});
	/*头部右侧会议信息*/
	$(".main-top-logo .top-meeting").hover(function(){
		$(this).addClass("top-user-hover");
		$(".top-meeting-down").show();
	},function(){
		$(this).removeClass("top-user-hover");
		$(".top-meeting-down").hide();
	});
	$(".top-meeting-down").hover(function(){
		$(this).show();
		$(".main-top-logo .top-meeting").addClass("top-user-hover");
	},function(){
		$(this).hide();
		$(".main-top-logo .top-meeting").removeClass("top-user-hover");
	});
	
	/*左侧点击系统*/
	$(".main-left-systems .sysbox").click(function(){
		//去除其他系统的选中状态
		$(".main-left-systems .sysbox").each(function(){
			$(this).removeClass("selected-sysbox");
		});
		//选中点击的系统
		$(this).addClass("selected-sysbox");
		//系统菜单id
		var systemid = $(this).attr("id").split("_")[1];
		var systemText = $.trim($(this).find(".sysbox-middle span").text());
		$(".menu-scroll .menus-header span").html(systemText);
		var url =  $(this).attr("data");
		$.ajax({
			url:url,
			data:{
				systemid:systemid
			},
			type:"post",
			success:function(response){
				$(".menus-list").html(response);
				
				//页面第一次加载根据情况显示与隐藏按钮
				displayUpAndDown();
				
				/*左侧菜单标题点击事件*/
				$(".menu-box .menu-header").click(function(){
					//隐藏其他的菜单
					$(".menu-box ul").each(function(){
						$(this).prev(".menu-header").removeClass("menu-header-selected");
						$(this).slideUp();
					});
					
					if($(this).next("ul").is(":hidden")){
						$(this).addClass("menu-header-selected");
						$(this).next("ul").slideDown("normal",displayUpAndDown);
					}else{
						$(this).removeClass("menu-header-selected");
						$(this).next("ul").slideUp("normal",displayUpAndDown);
					}
				});
				
				//菜单点击打开tab页签
				$(".menu-box ul .link").click(function linkOrTabClick(){
					//取消其他tab页签的选中状态
					$(".tab-box ul li").each(function(){
						$(this).removeClass("tab-box-selected");
					});
					
					//循环隐藏掉所有iframe
					$(".right-frames iframe").each(function(){
						$(this).hide();
					});
					
					//循环去除tablist项选中状态
					$("#tabs-box li").each(function(){
						$(this).removeClass("current-tab");
					});
					
					//菜单id
					var menuid = $(this).attr("id").split("_")[1];
					//菜单名称
					var menuText= $.trim($(this).text());
					//tab模块id
					var tabid ="tab_"+menuid;
					//iframeid
					var iframeid="iframe_"+menuid;
					
					//tablistItemid
					var tablistItemid='tabitem_'+menuid;
					//链接
					var linkUrl = $(this).attr("data");
					//判断是否已经创建过tab(是否已经存在)
					if($("#"+tabid).get(0)){
						$("#"+tabid).removeClass("tab-box-selected").addClass("tab-box-selected");
						$("#"+iframeid).show();
						$("#"+tablistItemid).removeClass("current-tab").addClass("current-tab");
					}else{
						var tabHtml = "<li id='"+tabid+"' data='"+ linkUrl +"' class='tab-box-selected'>"
						+ "<span class='tab-left'></span>"
						+ "<span class='tab-right'>"
						+ "<a  title='"+ menuText +"' class='link-title'>"+ menuText +"</a>"
						+ "<a  title='关闭' class='tab-close'></a>"
						+ "</span>"
									+ "</li>";
						$(".tab-box ul").append(tabHtml);
						
						var iframeHtml = '<iframe id="'+ iframeid +'" frameBorder="0" scrolling="yes"  src="'+ linkUrl +'"></iframe>';
						$(".right-frames").append(iframeHtml);
						
						var tabListItem ="<li id='"+ tablistItemid +"' class='current-tab'>"+ menuText +"</li>";
						$("#tabs-box").append(tabListItem);

						$("#"+tabid).rightclick(function (e) {
							curTab = $("#"+tabid);
							e.stopPropagation();
							var clientX = e.clientX;
							var clientY = e.clientY;
							if($(".tab-list-box").is(":hidden")){
								$(".tab-list-box").css('left',clientX).css('top',clientY).show();
							}else{
								$(".tab-list-box").hide();
							}
							$(document).one('click',function () {
								//$(".tab-list-box").hide();
							});
							  
       						$('iframe').contents().find('body').one('click',function () {
       							$(".tab-list-box").hide();
       						});
      
						})
						//绑定tab点击事件
						/*$("#"+tabid).click(function(){
							var link_id = "link_"+ $(this).attr("id").split("_")[1];
							$("#"+link_id).trigger("click");
						});*/
						$("#"+tabid).click(linkOrTabClick);
						
						//绑定tablistitem点击事件
						$("#"+tablistItemid).click(tab_list_click);
						
						//绑定tab关闭事件
						$("#"+tabid).find(".tab-close").click(function(){
							//当前选中tab
							var curTab = $(".tab-box-selected");
							//需要关闭的tab
							var closeTab = $(this).closest("li");
							var closeIframeid = "iframe_" + closeTab.attr("id").split("_")[1];
							var tablistItemid = "tabitem_" + closeTab.attr("id").split("_")[1];
							
							//判断需要关闭的tab和选中的tab是否是同一个对象，如果是则删除选中的tab后默认在选中前一个
							if(curTab.get(0)==closeTab.get(0)){
								//选中前一个标签
								closeTab.prev().trigger("click");
							}
							
							
							var closeWidth = closeTab.width();
							var left = parseInt($(".tab-box ul").css("left"));
							var leftValue = left +closeWidth;
							closeTab.remove();
							$("#"+closeIframeid).remove();
							$("#"+tablistItemid).remove();
							
							if(leftValue>0){
								$(".tab-box ul").css("left","0px")
								$(".tab-arrow-left").addClass("disable-arrow-left");
							}else{
								$(".tab-box ul").css("left",left+closeWidth)
							}
							
							changeRightTabButton();
						});
					}
					changeRightTabButton();
				});
			}
		});
		
	});
	//默认选中第一个系统
	$(".main-left-systems .sysbox").first().trigger("click");
	
	
	//点击我的桌面
	$("#tab_mydesktop").click(function(){
		//取消其他tab页签的选中状态
		$(".tab-box ul li").each(function(){
			$(this).removeClass("tab-box-selected");
		});
		
		//循环去除tablist项选中状态
		$("#tabs-box li").each(function(){
			$(this).removeClass("current-tab");
		});
		
		//循环隐藏掉所有iframe
		$(".right-frames iframe").each(function(){
			$(this).hide();
		});
		
		//选中我的桌面tab
		$(this).addClass("tab-box-selected");
		//显示当前我的桌面iframe
		$("#iframe_mydesktop").show();
		//选中tablist我的桌面
		$("#tab_item_mydesktop").addClass("current-tab");
		
	});
	
	
	/*隐藏与显示左侧菜单*/
	$(".left-arrow").click(function(){
		if($(this).hasClass("left-arrow")){
			$(this).removeClass("left-arrow").addClass("right-arrow");
			$(".main .main-center .main-middle").animate({
				left:"-118px"
			});
			
			$(".main .main-center .main-right").animate({
				left:"43px"
			});
		}else{
			$(this).removeClass("right-arrow").addClass("left-arrow");
			$(".main .main-center .main-middle").animate({
				left:"43px"
			});
			$(".main .main-center .main-right").animate({
				left:"203px"
			});
		}
		
	});
	
	/*隐藏与显示头部*/
	$(".up-arrow").click(function(){
		if($(this).hasClass("up-arrow")){
			$(this).removeClass("up-arrow").addClass("down-arrow");
			$(".main .main-top").animate({
				top:"-97px"
			});
			
			$(".main .main-center").animate({
				top:"0px"
			});
		}else{
			$(this).removeClass("down-arrow").addClass("up-arrow");
			$(".main .main-top").animate({
				top:"0px"
			});
			
			$(".main .main-center").animate({
				top:"97px"
			});
		}
	});
	
	/*显示与隐藏下拉tab列表项*/
	$(".tab-arrow-down").click(function(){
		if($(".tab-list-box").is(":hidden")){
			$(".tab-list-box").show();
		}else{
			$(".tab-list-box").hide();
		}
	});
	
	function tab_list_click(){
		$(".tab-list-box").hide();
		var tablistItemContent = $(this).text();
		//当前选中tab
		// var curTab = $(".tab-box-selected");
		//当前选择的tab的text
		var curTabText =  $(".tab-box-selected .link-title").text();
		
		if(tablistItemContent=="关闭页签"){
			if(curTabText!="我的桌面"){
				//选中后一个节点
				if(curTab.next()){
					curTab.next().trigger("click");
				}else{
					curTab.prev().trigger("click");
				}
				//执行关闭事件
				curTab.find(".tab-close").trigger("click");
			}
		}else if(tablistItemContent=="关闭全部页签"){
			$(".tab-box ul li").each(function(){
				if($(this).find(".link-title").text()!="我的桌面"){
					$(this).find(".tab-close").trigger("click");
					//显示我的桌面
					$("#tab_mydesktop").trigger("click");
				}
			});
		}else if(tablistItemContent=="关闭其他页签"){
			$(".tab-box ul li").each(function(){
				if($(this).find(".link-title").text()!="我的桌面"&&$(this)[0]!=curTab[0]){
					$(this).find(".tab-close").trigger("click");
				}
			});
			//选中当前tab
			curTab.trigger("click");
		}else if(tablistItemContent=="刷新"){
			if(curTabText!="我的桌面"){
				var menuid = curTab.attr("id").split("_")[1];
				var curFrameid = "iframe_"+menuid;
				$("#"+curFrameid).attr('src',$("#"+curFrameid).attr('src'));
			}else{
				$("#iframe_mydesktop").attr("src",$("#iframe_mydesktop").attr("src"));
			}
		}else{
			if($(this).text()=="我的桌面"){
				$("#tab_mydesktop").trigger("click");
			}else{
				var tabid = "tab_"+$(this).attr("id").split("_")[1];
				$("#"+tabid).trigger("click");
			}
		}
	}
	//tablist点击事件
	$(".tab-list ul li").click(tab_list_click);
	
	//==========================左侧菜单上下滚动=============================
	//显示向上或者向下的滚动按钮
	function displayUpAndDown(){
		var scollHeight = $(".menu-scroll").outerHeight();
		var containerHeight = $(".main-middle-menus").outerHeight();
		
		//如果需要滚动的区域大于了容器，则出现向下的按钮
		if(scollHeight>containerHeight){
			$(".menu-down").show();
		}else{
			$(".menu-down").hide();
			$(".menu-up").hide();
			$(".menu-scroll").css({
				"top":"0px"
			});
		}
	}
	
	var scollTimer;
	//偏移量
	var offset=0;
	//滚动幅度,大于0的整数
	var step = 3;
	
	//----------向下滚动------------
	//向下滚动箭头事件
	$(".menu-down").hover(function(){
		scrollDown();
	},function(){
		clearInterval(scollTimer);
	});
	
	//向下滚动方法
	function scrollDown(){
		var scollHeight = $(".menu-scroll").outerHeight();
		var containerHeight = $(".main-middle-menus").outerHeight();
		
		scollTimer = setInterval(function(){
			var top = parseInt($(".menu-scroll").css("top"));
			var res = scollHeight - containerHeight + top;
			if(res>=offset){
				$(".menu-scroll").css({
					"top":(top-step)+"px"
				});
				$(".menu-up").show();
			}else{
				//隐藏向下按钮
				$(".menu-down").hide();
				clearInterval(scollTimer);
			}
			
		},20);
	}
	
	//--------------向上滚动----------------
	//向上滚动箭头事件
	$(".menu-up").hover(function(){
		scrollUp();
	},function(){
		clearInterval(scollTimer);
	});
	
	//向上滚动方法
	function scrollUp(){
		scollTimer = setInterval(function(){
			var top = parseInt($(".menu-scroll").css("top"));
			
			if(top<0){
				var scollTop = top+step;
				if(scollTop>0){
					scollTop=0;
				}
				$(".menu-scroll").css({
					"top":(scollTop)+"px"
				});
				$(".menu-down").show();
			}else{
				$(".menu-up").hide();
				clearInterval(scollTimer);
			}
			
		},20);
	}
	
	//--------------鼠标滚轮----------------
	//滚动步长
	var wheelStep=5;
	//滚轮事件
	$(".main-middle-menus").on("mousewheel",function(e){
		 e.preventDefault();
		 var currentTop = parseInt($(".menu-scroll").css("top"));
		 //e.deltaY:值是负的即-1，那么滚轮就是向下滚动，正的1就是向上。
		 //向上滚动
		 if(e.deltaY==1){
			 var newTop = currentTop+wheelStep;
			 //$(".left_search_text").val("wheelup:"+newTop);
			if(newTop<=0){
				$(".menu-scroll").css({
					"top":newTop+"px"
				});
				$(".menu-down").show();
			}else{
				$(".menu-scroll").css({
					"top":"0px"
				});
				
				$(".menu-up").hide();
			}
		 }else{
			 var scollHeight = $(".menu-scroll").outerHeight();
			 var containerHeight = $(".main-middle-menus").outerHeight();
			 var newTop = currentTop-wheelStep;
			 var res = scollHeight - containerHeight + newTop;
			 //$(".left_search_text").val("wheeldown:"+res);
			 if(res>=offset){
				$(".menu-scroll").css({
					"top":newTop+"px"
				});
				$(".menu-up").show();
			 }else{
				//隐藏向下按钮
				$(".menu-down").hide();
			 }
		 }
	});
	
	//=========================tab=================================
	//改变tab向右按钮的启用与禁用
	function changeRightTabButton(){
		var tabsWidth = $(".tab-box ul").width();
		var tabContainerWidth= $(".tab-box").width();
		
		if(tabsWidth>=tabContainerWidth){
			$(".tab-arrow-right").removeClass("disable-arrow-right");
		}else{
			$(".tab-arrow-right").addClass("disable-arrow-right");
		}
	}
	
	//右移动按钮点击事件
	$(".arrow-box .tab-arrow-right").click(function(){
		//判断是否已禁用
		if($(this).hasClass("disable-arrow-right")){
			return;
		}
		$(".arrow-box .tab-arrow-left").removeClass("disable-arrow-left");
		
		var tabsWidth = $(".tab-box ul").width();
		var left = parseInt($(".tab-box ul").css("left"));
		var tabContainerWidth= $(".tab-box").width();
		
		
		if(tabsWidth+left-tabContainerWidth>=0){
			$(".tab-box ul").animate({
				left:"-=60px"
			},100);
		}else{
			$(this).addClass("disable-arrow-right");
		}
		//$(".query-text").val(tabsWidth+left-tabContainerWidth);
	});
	
	//左移动按钮点击事件
	$(".arrow-box .tab-arrow-left").click(function(){
		//判断是否已禁用
		if($(this).hasClass("disable-arrow-left")){
			return;
		}
		changeRightTabButton();
		var left = parseInt($(".tab-box ul").css("left"));
		
		if(left<0){
			if((left+60)<0){
				$(".tab-box ul").animate({
					left:"+=60px"
				},10);
			}else{
				$(".tab-box ul").css({"left":"0px"});
			}
		}else{
			$(this).addClass("disable-arrow-left");
		}
	});
	$(".menu-box ul .link").click(function linkOrTabClick(){
		alert(23);

	})
	
});