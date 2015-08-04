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
	var loginNode    = $('#Juser_login_form'),
		registerNode = $('#Juser_register_form'),
		manageNode   = $('#Juser_userInfo_form');
	//登录操作
	if(loginNode.html()) {
		var mailNode = $('#Juser_iptMail'),
			pwdNode  = $('#Juser_iptPwd'),
			tokenVal = $('#Juser_iptToken').val(),
			Btn      = $('#Juser_Sub_login'),
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
			Jalert('正在登录，请稍后...','loading',30);
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
		var nameNode = $('#Juser_iptName'),
			mailNode = $('#Juser_iptMail'),
			urlNode  = $('#Juser_iptUrl'),
			pwdNode  = $('#Juser_iptPwd'),
			pwdrNode = $('#Juser_iptrPwd'),
			tokenVal = $('#Juser_iptToken').val(),
			Btn      = $('#Juser_Sub_login'),
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
			Jalert('正在提交，请稍后...','loading',30);
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
	//mange
	if(manageNode.html()) {
		var nameNode = $('#Juser_iptName'),
			mailNode = $('#Juser_iptMail'),
			urlNode  = $('#Juser_iptUrl'),
			pwdoNode = $('#Juser_iptoPwd'),
			pwdNode  = $('#Juser_iptPwd'),
			pwdrNode = $('#Juser_iptrPwd'),
			phoneNode= $('#Juser_iptPhone'),
			qqNode   = $('#Juser_iptQQ'),
			Btn      = $('#Juser_Sub_login'),
			_Url     = manageNode.attr('action'),
			_redirect= _Url.replace('doChange','UserCenter');
		manageNode.submit(function () {
			//修改pwd
			if($('.JAuth_update_pwd').html()) {
				if(!J.isPassWord(pwdoNode.val())) {
					pwdoNode.focus();
					Jalert('原密码格式错误','error');
					return false;
				}
				if(!J.isPassWord(pwdNode.val())) {
					pwdNode.focus();
					Jalert('新密码格式错误','error');
					return false;
				}
				if(!J.isPassWord(pwdrNode.val())) {
					pwdrNode.focus();
					Jalert('重复新密码格式错误','error');
					return false;
				}
				if(pwdrNode.val()!=pwdNode.val()) {
					pwdrNode.focus();
					Jalert('两次新密码不一致','error');
					return false;
				}
				data  = {'p':pwdNode.val(),'rp':pwdrNode.val(),'op':pwdoNode.val()};
			}
			//修改info
			if($('.JAuth_update_info').html()) {
				if(nameNode.val()!='' && nameNode.val().length>8) {
					nameNode.focus();
					Jalert('昵称长度不得大于8位，建议使用中文','error');
					return false;
				}
				if(urlNode.val()!='' && !J.isUrl(urlNode.val())) {
					urlNode.focus();
					Jalert('网址格式错误','error');
					return false;
				}
				if(qqNode.val()!='' && !J.isQQ(qqNode.val())) {
					qqNode.focus();
					Jalert('QQ号格式错误','error');
					return false;
				}
				if(phoneNode.val()!='' && !J.isPhone(phoneNode.val())) {
					phoneNode.focus();
					Jalert('手机号码格式错误','error');
					return false;
				}
				data  = {'n':nameNode.val(),'url':urlNode.val(),'qq':qqNode.val(),'phone':phoneNode.val()};
			}
			Jalert('正在提交，请稍后...','loading',30);
			Btn.attr('disabled',true).fadeTo('slow',0.5);//禁用点击按钮
			$.ajax({
				type: "POST",
				url: _Url,
				dataType:'json',
				data: data,
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
		var tipsNode = $('#Juser_tips');
		tipsNode.removeClass('Juser_tips_success Juser_tips_error Juser_tips_alert Juser_tips_loading').empty();
		if(status == 'error') {
			tipsNode.addClass('Juser_tips_error').append(text).slideDown();
		}else if(status == 'success') {
			tipsNode.addClass('Juser_tips_success').append(text).slideDown();
		}else if(status == 'alert') {
			tipsNode.addClass('Juser_tips_alert').append(text).slideDown();
		}else if(status == 'loading') {
			tipsNode.addClass('Juser_tips_loading').append('<p>'+text+'</p>').slideDown();
		}
		setTimeout(function () {
			tipsNode.slideUp();
		},t*1000);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//提示框相关处理
	function setGo() {
		var t 		 = 3,handle,
			pNode 	 = $('.Juser_status_tips'),
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
	var SuccessNode = $('.Juser_status_success'),ErrorNode = $('.Juser_status_error');
	if(ErrorNode.html()) {setGo();}
	if(SuccessNode.html()) {setGo();}
});