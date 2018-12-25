$(function(){
	//导航点击事件
	$(".nav li").click(function(){
		$(this).addClass("chosen").siblings().removeClass("chosen");

		var i = $(this).index();//下标第一种写法
        //var i = $('tit').index(this);//下标第二种写法
        $('.main ul li').eq(i).show().siblings().hide();
	})
	
	//点击查看套餐详情
	$(".xiangqing").click(function(){
		$(this).children("span").hide();
		$(this).children(".xiangqing_nr").show();
	})
	
	
	//选择
   //全选
   $(".select_q").click(function(){
   		if(!$(this).hasClass("sel_ok")){
		   $(this).addClass("sel_ok");
		   $(".sel").addClass("sel_ok")
		 }else{
		    $(this).removeClass("sel_ok");
		    $(".sel").removeClass("sel_ok");
		 }
   })
	
	//单选
	$(".sel").click(function(){
		if(!$(this).hasClass("sel_ok")){
		   $(this).addClass("sel_ok");
		 }else{
		    $(this).removeClass("sel_ok");
		 }
	})
})
