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
			tokenVal = $('#inputToken').val(),
			Btn      = $('#inputSub'),
			_Url     = loginNode.attr('action'),
			_redirect= _Url.replace('doLogin','UserCenter');
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
			Btn.attr('disabled',true).fadeTo('slow',0.5);//禁用点击按钮
			$.ajax({
				type: "POST",
				url: _Url,
				dataType:'json',
				data: {'u':mailNode.val(),'p':pwdNode.val(),'token':tokenVal},
				success: function(msg){
					if(msg.code==200) {
						//登录成功  跳转至用户中心
						Jalert(msg.info,'success');
						setTimeout(function () {
							window.location.href = _redirect;
						},2000);						
						return false;
					}else if(msg.code==500) {
						//刷新该页
						window.location.reload(true);
						return false;
					}else if(msg.code==501) {
						Jalert(msg.info,'error');
					}else {
						alert(msg.info);
					}
					Btn.attr('disabled',false).fadeTo('slow',1);//再次启用点击按钮
				},
				error:function () {
					Jalert('服务器故障，请稍后再试','error');
					Btn.attr('disabled',false).fadeTo('slow',1);//再次启用点击按钮
				}
			});
			//prevet default event
			return false;
		});
	}
	//注册操作
	if(registerNode.html()) {
		var nameNode = $('#inputName'),
			mailNode = $('#inputEmail'),
			urlNode  = $('#inputUrl'),
			pwdNode  = $('#inputPwd'),
			pwdrNode = $('#inputrPwd'),
			tokenVal = $('#inputToken').val(),
			Btn      = $('#inputSub'),
			_Url     = registerNode.attr('action'),
			_redirect= _Url.replace('doRegister','UserCenter');
		registerNode.submit(function () {
			if(nameNode.val()!='' && nameNode.val().length>8) {
				nameNode.focus();
				Jalert('昵称长度不得大于8位，建议使用中文','error');
				return false;
			}
			if(!J.isMail(mailNode.val())) {
				mailNode.focus();
				Jalert('邮箱格式错误','error');
				return false;
			}
			if(urlNode.val()!='' && !J.isUrl(urlNode.val())) {
				urlNode.focus();
				Jalert('网址格式错误','error');
				return false;
			}
			if(!J.isPassWord(pwdNode.val())) {
				pwdNode.focus();
				Jalert('密码格式错误','error');
				return false;
			}
			if(!J.isPassWord(pwdrNode.val())) {
				pwdrNode.focus();
				Jalert('重复密码格式错误','error');
				return false;
			}
			if(pwdrNode.val()!=pwdNode.val()) {
				pwdNode.focus();
				Jalert('密码和重复密码不一致','error');
				return false;
			}
			Jalert('正在提交，请稍后...','success',30);
			Btn.attr('disabled',true).fadeTo('slow',0.5);//禁用点击按钮
			$.ajax({
				type: "POST",
				url: _Url,
				dataType:'json',
				data: {'u':mailNode.val(),'p':pwdNode.val(),'rp':pwdrNode.val(),'n':nameNode.val(),'url':urlNode.val(),'token':tokenVal},
				success: function(msg){
					if(msg.code==200) {
						//登录成功  跳转至用户中心
						Jalert(msg.info,'success');
						setTimeout(function () {
							window.location.href = _redirect;
						},2000);
						return false;
					}else if(msg.code==500) {
						//刷新该页
						window.location.reload(true);
						return false;
					}else if(msg.code==501) {
						Jalert(msg.info,'error');
					}else {
						alert(msg.info);
					}
					Btn.attr('disabled',false).fadeTo('slow',1);//再次启用点击按钮
				},
				error:function () {
					Jalert('服务器故障，请稍后再试','error');
					Btn.attr('disabled',false).fadeTo('slow',1);//再次启用点击按钮
				}
			});
			//prevet default event
			return false;
		});		
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