/*
 *
 *Plugin Name: Juser
 *URL: http://blog.jjonline.cn
 *Description: 晶晶的博客emlog会员系统插件
 *Author: Jea杨
 *Version: 1.0
 *
 */
$(function () {
	/*登录*/
	var loginNode    = $('#juser_login_form'),
		registerNode = $('#juser_register_form');
	//登录操作
	if(loginNode.html()) {
		var mailNode = $('#inputEmail'),
			pwdNode  = $('#inputPwd'),
			tokenVal = $('#inputToken').val();
		loginNode.submit(function () {
			if(!J.isMail(mailNode.val())) {
				mailNode.focus();
				Jalert('邮箱格式错误','error');
				return false;
			}
			if(!J.isPassWord(pwdNode.val())) {
				pwdNode.focus();
				Jalert('密码格式错误','error');
				return false;
			}
			Jalert('正在登录，请稍后...','success',30);
			//prevet default event
			return false;
		});
	}
	//注册操作
	if(registerNode.html()) {
		
	}
	//signTipsFunc
	function Jalert(text,status,time) {
		var t = time || 3;//默认3s
		var tipsNode = $('#juser_alert');
		if(status == 'error') {
			tipsNode.removeClass('juser_sign_success').addClass('juser_sign_error').empty().append(text).slideDown();
		}else {
			tipsNode.removeClass('juser_sign_error').addClass('juser_sign_success').empty().append(text).slideDown();
		}		
		setTimeout(function () {
			tipsNode.slideUp();
		},t*1000);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//提示框相关处理
	function setGo() {
		var t 		 = 3,handle,
			pNode 	 = $('.juser_tips_des'),
			sNode 	 = pNode.find('span'),
			redirect = pNode.find('a').attr('href');
			t 	 	 = sNode.text();
		handle = setInterval(function () {
			t--;
			if(t==0) {
				clearInterval(handle);
				pNode.empty().append('正在跳转，请稍后！');
				window.location.href = redirect;return;
			}
			sNode.empty().text(t);
		},1000);
	}
	var SuccessNode = $('.juser_success'),ErrorNode = $('.juser_error');
	if(ErrorNode.html()) {setGo();}
	if(SuccessNode.html()) {setGo();}
});