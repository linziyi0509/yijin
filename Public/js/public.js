/**
 * 王志飞创建 公共.js
 */

function Fly(){

	this.load = function() {
		$("<div class=\"datagrid-mask\"></div>").css({ display: "block", width: "100%", height: $(window).height() }).appendTo("body");
		$("<div class=\"datagrid-mask-msg\"></div>").html("正在运行，请稍候。。。").appendTo("body").css({ display: "block", left: ($(document.body).outerWidth(true) - 190) / 2, top: ($(window).height() - 45) / 2 });
	}

	this.disload = function() {
		$(".datagrid-mask").remove();
		$(".datagrid-mask-msg").remove();
	}

	this.request = function(paras) {  
            var url = location.href;  
            var paramStr = url.substring(url.indexOf('?') + 1, url.length).split('&');  
            var j;  
            var paramObj = {};  
            for (var i = 0; j = paramStr[i]; i++) {  
                paramObj[j.substring(0, j.indexOf('=')).toLowerCase()] = j.substring(j.indexOf('=') + 1, j.length);  
            }  
              
            var returnValue=paramObj[paras.toLowerCase()];  
  
            if (typeof (returnValue) == "undefined") {  
                return "";  
            } else {  
                return returnValue.replace('#','');  
            }  
        }  


	//获得所有选中
	this.getchecked = function(){
			var nodes = $('#mytable').datagrid('getChecked');
			var s = '';
			for(var i=0; i<nodes.length; i++){
				if (s != '') s += ',';
				s += nodes[i].id;
			}
			return s;
	}

	this.gethtml=function(url,title,width,end,top){
	if(width==null){
		width = '90%';
	}
	if(end==null){
		var height = document.body.clientHeight;
		end=(height-30)+'px';
	}
	if(top==null){
		top='10px';
	}
	$.layer({
		type: 2,
		maxmin: true,
		shadeClose: true,
		title: title,
		shade: [0.1,'#fff'],
		offset: [top,''],
		area: [width, end],
		iframe: {src: url}
	}); 
}

//=======================自动完成开始
//=======================自动完成开始
//=======================自动完成开始

this.autocomplete = function (id,id2){
	$("#"+id).focus(function(){
		$("#autoselect").remove();
		$("#"+id).after('<div id="autoselect" style="-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;-webkit-box-shadow: #CCC 0px 0px 5px;-moz-box-shadow: #CCC 0px 0px 5px;box-shadow: #CCC 0px 0px 5px;border:1px solid #CCC;margin-left:4px;margin-top:6px;background-color: #FFFFFF;width:'+$("#"+id).css('width')+';position: absolute;"><input type="text" autocomplete="off" style="width:95%;" id="'+id+'_search"><div id="autoselect_"></div></div>');
		$("#"+id+'_search').focus();
		$fly.loadlist(id,id2);
		setTimeout(function(){
			$("#"+id+'_search').keyup(function(){
				$fly.loadlist(id,id2);
			});

			$("#"+id+'_search').blur(function(){
				setTimeout(function(){
					$("#autoselect").remove();
				},100);
			});
		},100);
	});


	
}

this.loadlist = function (id,id2){
	$.getJSON('/admin/activity/merchantslist',{key:$("#"+id+'_search').val()},function(data){
		var str = '';
		for (var i = 0; i < data.length; i++) {
			str += '<div class="autoselect_select" style="margin:5px;height:25px;width:97%;border-bottom-width: 1px;border-bottom-style: solid;border-bottom-color: #eeeeee;color: #999999;line-height:25px;" onclick="$fly.selectid(\''+id+'\',\''+id2+'\','+data[i]['id']+',\''+data[i]['name']+'\')">'+data[i]['name']+'</div>';
		};
		$("#autoselect_").html(str);
	});
}

this.selectid = function(id,id2,value,value2){
	$("#autoselect").remove();
	$("#"+id).val(value2);
	$("#"+id2).val(value);
	$("#autoselect").remove();
}

this.msg = function(message){
	layer.msg(message,1,-1);
}

//=======================自动完成end
//=======================自动完成end
//=======================自动完成end
	


}

//实例化fly类
var $fly = new Fly();

//禁止右键
// $(document).bind("contextmenu",function(){return false;});
// //禁止f12
// document.onkeydown = function(e)  {
//     if (123 == (e || {}).keyCode)  return false;
// }

