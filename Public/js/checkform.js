
//==================================验证方法
//==================================验证方法
//==================================验证方法
$.extend($.fn.validatebox.defaults.rules, {
	//=========密码是否相同
	//用法<input validType="xiangtong['password']">
	xiangtong: {
	validator: function(value, param){
	return value == $("#"+param[0]).val();
	},message: '密码必须相同！'}
}); 