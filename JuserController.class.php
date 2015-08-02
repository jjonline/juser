<?php
/**
 * Juser前台控制器类 文件被调用时已包含基本库 无需再include
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-30 09:30:09
 * @version $Id$
 */
if(!defined('EMLOG_ROOT')) {exit('Juser 运行在emlog博客框架下!');}
class JuserController {
	private $BlogInfo = array();
	#构造函数
	public function __construct() {
		global $CACHE;
		$this->BlogInfo  =	$CACHE->readCache('options');
		// $blogname    		=   $BlogInfo['blogname'];
		// $bloginfo    		= 	$BlogInfo['bloginfo'];
		// $site_title  		= 	$blogname;
		// $site_key 	 		= 	$BlogInfo['site_key'];
		// $site_description   =   $blogname.'用户中心。';
		// $icp 		 		=   $BlogInfo['icp'];
		// $footer_info 		=   $BlogInfo['footer_info'];
	}

	/**
	 * Juser默认显示页面
	 * @param null
	 * @return mixed
	 */
	public function __empty($isLogin=null) {
		$this->login($isLogin);
	}

	/**
	 * Juser注册页面
	 * @param null
	 * @return mixed
	 */
	public function register($isLogin=null) {
		#判断是不是开放平台登录的用户注册绑定
		$blogUrl  			 =   BLOG_URL;#heredoc语法中不识别常量 仅识别变量
		$OpenUserTips        =   '';
		if(isset($_SESSION['OpenUserInfo']) && !empty($_SESSION['OpenUserInfo'])) {
			$OpenUserInfo    =   $_SESSION['OpenUserInfo'];
			$key    		 =   $OpenUserInfo['type'].'_name';
			$OpenUserTips    =   '<div class="juser_layer_tips"><p class="juser_openlogin_tips"></strong>请注册或<a href="'.$blogUrl.'?plugin=juser&a=login">登录</a>后绑定您的'.$OpenUserInfo['typeName'].'<strong>('.$OpenUserInfo[$key].')</strong></p></div>';
		}
		$rightBar = <<<STR
		{$OpenUserTips}
		<div class="juser_login juser_register juser_login_left">
			<h1>会员注册</h1>
			<form method="POST" id="juser_register_form" class="juser_form" action="{$blogUrl}?plugin=juser&a=doRegister">
				<input type="hidden" value="{$this->__Token()}" id="inputToken" name="token">
				<div class="formFiled"><label for="inputEmail">邮箱</label><input type="text" class="juser_input" placeholder="输入邮箱" id="inputEmail" autocomplete="off" name="u"></div>
				<div class="formFiled"><label for="inputPwd">密码</label><input type="password" class="juser_input" placeholder="设置您密码" id="inputPwd" name="p"></div>
				<div class="formFiled"><label for="inputrPwd">重复密码</label><input type="password" class="juser_input" placeholder="将设置的密码重复一遍" id="inputrPwd" name="rp"></div>
				<div class="formFiled"><label for="inputName">昵称</label><input type="text" class="juser_input" placeholder="设置昵称，可留空" id="inputName" autocomplete="off" name="n"></div>
				<div class="formFiled"><label for="inputUrl">网址</label><input type="text" class="juser_input" placeholder="博客或空间地址，可留空" id="inputUrl" autocomplete="off" name="url"></div>
				<div class="formFiled juser_sub"><input type="submit" class="juser_input" id="inputSub" value="立即注册"></div>
				<div class="formFiled juser_open">
					<span>用其他方式注册：</span>
					<a href="{$blogUrl}?plugin=juser&a=openLogin&type=qq" class="juser_open_way juser_openqq">QQ</a>
					<a href="{$blogUrl}?plugin=juser&a=openLogin&type=sina" class="juser_open_way juser_openwb">微博</a>
				</div>
			</form>
		</div>
STR;
		$leftBar  = <<<STR
		<div class="juser_login juser_login_right">
			<h2>已经是{$this->BlogInfo['blogname']}会员？</h2>
			<h3><a href="{$blogUrl}?plugin=juser&a=login">立马去登录</a></h3>
		</div>
STR;
		$this->__show($rightBar,$leftBar);
	}

	/**
	 * Juser注册处理器
	 * @param null
	 * @return mixed
	 */
	public function doRegister($isLogin=null) {
		#登录情况下还在注册 检测提交方式返回
		if($isLogin) {
			if(IS_POST && IS_AJAX) {#客户端执行刷新页面指令
				$this->ajaxReturn(array('code'=>'500','info'=>'已登录'));
			}
			emDirect(BLOG_URL.'?plugin=juser&a=UserCenter');#get方式 301跳转
		}
		#post+ajax方式处理数据
		if(IS_POST && IS_AJAX) {
			#效验token
			if(!$this->__Token(true)) {
				$this->ajaxReturn(array('code'=>'500','info'=>'Token Error.'));
			}
			#开始效验注册数据
			$RegistData  	   				=	JuserRouter::getInputData('register');
			if($RegistData && $RegistData['mail'] && $RegistData['password']) {
				#检测该邮箱是否被注册过
				$hasRegist     				=	Juser::getUserInfoByMail($RegistData['mail']);
				if($hasRegist) {
					$this->ajaxReturn(array('code'=>'501','info'=>'该邮箱已被注册'));
				}
				#加密密码
				$RegistData['password']		=	Juser::genPassword($RegistData['password']);
				#检测是否开放平台登录后注册的用户
				if(isset($_SESSION['OpenUserInfo']) && !empty($_SESSION['OpenUserInfo'])) {
					$OpenUserInfo    	 	=	$_SESSION['OpenUserInfo'];
					$key    		 		=	$OpenUserInfo['type'].'_name';
					/*=====检测登录的账号该开放平台数据的合理性=====*/
					$hasOpenUser	 		=	Juser::getUserInfoByOpenID($OpenUserInfo['type'],$OpenUserInfo['_pk']);
					unset($_SESSION['OpenUserInfo']);
					#没有绑定过才注册绑定
					if(!$hasOpenUser) {
						#更新注册数据 合并开放平台数据
						unset($OpenUserInfo['type'],$OpenUserInfo['_pk'],$OpenUserInfo['typeName']);
						$RegistData  		=	array_merge($RegistData,$OpenUserInfo);
					}
				}
				#写入数据
				$JuserModel  		=	Juser::getJuserModel();
				$id 		 		=	$JuserModel->data($RegistData)->add();
				if($id) {
					#写入成功 给予登录权限
					Juser::setAuthCookie($id);
					$tips = "注册会员成功";
					if(isset($OpenUserInfo)) {
						$tips = "注册并绑定成功";
					}
					$this->ajaxReturn(array('code'=>'200','info'=>$tips));
				}else {
					$this->ajaxReturn(array('code'=>'501','info'=>'服务器异常，注册失败'));
				}
			}
		}
		#非post
		emDirect(BLOG_URL.'?plugin=juser');#get方式 301跳转	
	}

	/**
	 * Juser登录页面（默认显示页面）
	 * @param null
	 * @return mixed
	 */
	public function login($isLogin=null) {
		$blogUrl  			 =   BLOG_URL;
		$OpenUserTips        =   '';
		if(isset($_SESSION['OpenUserInfo']) && !empty($_SESSION['OpenUserInfo'])) {
			$OpenUserInfo    =   $_SESSION['OpenUserInfo'];
			$key    		 =   $OpenUserInfo['type'].'_name';
			$OpenUserTips    =   '<div class="juser_layer_tips"><p class="juser_openlogin_tips"></strong>请登录或<a href="'.$blogUrl.'?plugin=juser&a=register">注册</a>后绑定您的'.$OpenUserInfo['typeName'].'<strong>('.$OpenUserInfo[$key].')</strong></p></div>';
		}
		$rightBar = <<<STR
		{$OpenUserTips}
		<div class="juser_login juser_login_left">
			<h1>会员登录</h1>
			<form method="POST" id="juser_login_form" class="juser_form" action="{$blogUrl}?plugin=juser&a=doLogin">
				<input type="hidden" value="{$this->__Token()}" id="inputToken" name="token">
				<div class="formFiled"><label for="inputEmail">邮箱</label><input type="text" class="juser_input" placeholder="请输入您的邮箱" id="inputEmail" autocomplete="off" name="u"></div>
				<div class="formFiled"><label for="inputPwd">密码</label><input type="password" class="juser_input" placeholder="请输入您的密码" id="inputPwd" name="p"></div>
				<div class="formFiled juser_sub"><input type="submit" class="juser_input" id="inputSub" value="登录"></div>
				<div class="formFiled juser_open">
					<span>用其他方式登录：</span>
					<a href="{$blogUrl}?plugin=juser&a=openLogin&type=qq" class="juser_open_way juser_openqq">QQ</a>
					<a href="{$blogUrl}?plugin=juser&a=openLogin&type=sina" class="juser_open_way juser_openwb">微博</a>
				</div>
			</form>
		</div>
STR;
		$leftBar  = <<<STR
		<div class="juser_login juser_login_right">
			<h2>还不是{$this->BlogInfo['blogname']}会员？</h2>
			<h3><a href="{$blogUrl}?plugin=juser&a=register">果断去注册</a></h3>
		</div>
STR;
		$this->__show($rightBar,$leftBar);
		
	}

	/**
	 * Juser登录处理器
	 * @param null
	 * @return mixed
	 */	
	public function doLogin($isLogin=null) {
		if($isLogin) {
			if(IS_POST && IS_AJAX) {#客户端执行刷新页面指令
				$this->ajaxReturn(array('code'=>'500','info'=>'已登录'));
			}
			emDirect(BLOG_URL.'?plugin=juser&a=UserCenter');#get方式 301跳转
		}else {
			#post+ajax 处理登录请求(可能存在客户端js运行异常的情况 TODO暂不处理)
			if(IS_POST && IS_AJAX) {
				if(!$this->__Token(true)) {
					$this->ajaxReturn(array('code'=>'500','info'=>'Token Error.'));
				}
				#路由类检测并获取数据
				$userLoginData   =	JuserRouter::getInputData('login');
				if($userLoginData && isset($userLoginData['mail']) && isset($userLoginData['password'])) {
					#读取该用户数据
					$hasUser     =	Juser::getUserInfoByMail($userLoginData['mail']);
					#不存在该用户
					if(!$hasUser) {
						$this->ajaxReturn(array('code'=>'501','info'=>'该会员不存在'));
					}
					#比对密码
					$checkResult =	Juser::checkPassword($userLoginData['password'],$hasUser['password']);
					#密码错误
					if(!$checkResult) {
						$this->ajaxReturn(array('code'=>'501','info'=>'密码效验失败'));
					}
					#给予登录状态
					Juser::setAuthCookie($hasUser['id']);
					#可能存在的开放平台数据写入并效验
					if(isset($_SESSION['OpenUserInfo']) && !empty($_SESSION['OpenUserInfo'])){
						$OpenUserInfo    	 	=	$_SESSION['OpenUserInfo'];
						$key    		 		=	$OpenUserInfo['type'].'_name';
						/*=====检测登录的账号该开放平台数据的合理性=====*/
						$hasOpenUser	 		=	Juser::getUserInfoByOpenID($OpenUserInfo['type'],$OpenUserInfo['_pk']);
						unset($_SESSION['OpenUserInfo']);
						#没有绑定过 直接写数据
						if(empty($hasUser[$key]) && !$hasOpenUser) {
							#更新绑定数据
							$JuserModel  		=	Juser::getJuserModel();
							unset($OpenUserInfo['type'],$OpenUserInfo['_pk'],$OpenUserInfo['typeName']);
							$OpenUserInfo['id']	=	$hasUser['id'];
							$ret 		 		=	$JuserModel->data($OpenUserInfo)->save();
							if($ret) {
								$this->ajaxReturn(array('code'=>'200','info'=>'登录并绑定成功'));
							}
						}
						#其他情况::绑定写入数据失败、登录的用户已绑定过或极端情况：开放平台用户已经存在了
						$this->ajaxReturn(array('code'=>'200','info'=>'登录成功，但绑定失败'));
					}
					unset($_SESSION['__TOKEN__'],$_SESSION['__TOKEN_EXPIRE__']);
					$this->ajaxReturn(array('code'=>'200','info'=>'登录成功'));
				}
				$this->ajaxReturn(array('code'=>'501','info'=>'密码格式错误'));
			}
			#此处不再处理兼容性::Get方式提交的登录请求不处理
			emDirect(BLOG_URL.'?plugin=juser');#get方式 301跳转
		}
	}

	/**
	 * Juser开放平台登录
	 * @param null
	 * @return mixed
	 */
	public function openLogin($isLogin=null) {
		$Open = $this->openInit();
		emDirect($Open->getRedirectUrl());
	}

	/**
	 * Juser开放平台登录回调
	 * @param null
	 * @return mixed
	 */
	public function openCallBack($isLogin=null) {
		JuserRouter::OpenCallBackInit();
		$Open 				=	$this->openInit();
		if(JUSER_OPEN_CODE){
			$acceToken  	= 	$Open->getAccessToken(JUSER_OPEN_CODE);
		}else {
			#用户手动刷新了回调页
			emDirect(BLOG_URL.'?plugin=juser&type='.strtolower($_GET['type']));
		}		
		if($acceToken) {
			$openID 		=	$Open->getOpenID($acceToken);
			if($openID){
				$OpenUser   =	$Open->getUserInfo($acceToken);
				$this->__openLoginHandle($isLogin,$OpenUser);#调度处理
			}
		}else {
			#Token获取异常 回到插件首页
			emDirect(BLOG_URL.'?plugin=juser');
		}
	}
	#open登录初始化
	private function openInit() {
		if(!isset($_GET['type'])) {emDirect(BLOG_URL.'?plugin=juser');}
		$OpenType 	 = 	 strtolower($_GET['type']);
		#预留额外添加微信wx、百度bd、淘宝tb、谷歌gg、点点dd、搜狐sohu、网易wy、豆瓣db、开心网kx、微软msn的type字段
		#TODO 需要添加相应名称的openSDK并修改juser_data字段 字段名称仿造qq、sina即可 例如wx_openid wx_figure
		if(!in_array($OpenType,array('qq','sina','wx','bd','tb','gg','dd','sohu','wy','db','kx','msn'))) {emDirect(BLOG_URL.'?plugin=juser');}
		#appkey appsecret配置
		global $CACHE;
		$Config    	 =  $CACHE->readCache('juser_config');
		if(!isset($Config[$OpenType])) {exit('AppKey、AppSecret不存在，请管理员先配置插件');}
		$Config 	 =	$Config[$OpenType];
		$Key  		 =  $Config['key'];
		$Secret      = 	$Config['secret'];
		$Open  		 =  JuserOpen::getInstance($OpenType,$Key,$Secret,BLOG_URL.'?plugin=juser&a=openCallBack&type='.$OpenType);
		return $Open;
	}
	#open回调并获取用户信息成功后的调度
	private function __openLoginHandle($isLogin = null,$UserInfo = array()) {
		$hasUser = Juser::getUserInfoByOpenID($UserInfo['type'],$UserInfo['_pk']);
		/*=====================尚未登录=====================*/
		#登录或绑定方式进行注册
		if(!$isLogin) {
			#已绑定过执行快速登录步骤
			if($hasUser) {
				#更新数据库
				$JuserModel 		= 	Juser::getJuserModel();
				$Data 				=	$UserInfo;
				unset($Data['type'],$Data['_pk'],$Data['typeName']);
				$Data['id'] 		=	$hasUser['id'];
				$JuserModel->data($Data)->save();
				#给予登录状态
				Juser::setAuthCookie($hasUser['id'],86400*7);#开放平台用户登录状态保留一周
				#登录成功提示
				$this->__success($UserInfo['typeName'].'登录成功',3,'UserCenter');
				return ;
			}
			#尚未绑定，去登录绑定或者注册绑定（完善邮箱、密码、昵称、Url等信息）
			$_SESSION['OpenUserInfo']  = $UserInfo;#session记录开放平台信息 跳转注册或登录绑定
			emDirect(BLOG_URL.'?plugin=juser&a=register&isOpen=1');#判断逻辑放在register或login控制器
		}
		/*=====================已登录情况=====================*/
		#不存在的开放平台用户-->检测是否换绑或绑定
		if(!$hasUser) {
			$key     			=	$UserInfo['type'].'_openid';
			$tips  				=	'绑定'.$UserInfo['typeName'];
			#更换绑定
			if(!empty($isLogin[$key])) {
				$tips 			=	'更换绑定'.$UserInfo['typeName'];
			}
			#更新绑定数据
			$JuserModel 		= 	Juser::getJuserModel();
			$Data 				=	$UserInfo;
			unset($Data['type'],$Data['_pk'],$Data['typeName']);
			$Data['id'] 		=	$isLogin['id'];
			$ret 				= 	$JuserModel->data($Data)->save();
			#提示换绑定成功过
			if($ret) {
				$this->__success($tips.'成功',3,'UserCenter');
				return;
			}else {
				$this->__error($tips.'失败',6,'UserCenter');
				return;
			}
		}else {
			#删除可能存在的session
			if(isset($_SESSION['OpenUserInfo'])) {unset($_SESSION['OpenUserInfo']);}
			#仅存在后台申请绑定 但申请绑定的开放平台账号被其他账号绑定了或账号相同
			if($hasUser['id'] !== $isLogin['id']) {
				$this->__error('换绑失败：该'.$UserInfo['typeName'].'账号已被本站其他账号绑定！',15,'UserCenter');
				return;
			}else {
				$this->__error('换绑失败：已绑定'.$UserInfo['typeName'].'账号与换绑账号相同！',15,'UserCenter');
				return;
			}
			emDirect(BLOG_URL.'?plugin=juser&a=UserCenter&isOpenBind=1');
		}
	}

	/**
	 * Juser会员中心
	 * @param null
	 * @return mixed
	 */
	public function UserCenter($UserInfo=null) {
		$leftBar  			 =	$this->__getLeftBar('UserCenter');
		$rightBar = <<<STR
		<div class="JAuth_right">
			<div class="JAuth_content">欢迎来到晶晶的博客会员中心，本会员中心功能尚在完善，<a href="http://blog.jjonline.cn/theme/juser.html" target=_blank>点此</a>来给我提意见吧！</div>
		</div>
STR;
		$this->__show($leftBar,$rightBar,$UserInfo);
	}

	/**
	 * Juser会员中心查看已有评论
	 * @param null
	 * @return mixed
	 */
	public function UserComment($UserInfo=null) {
		$JuserCommentModel   =  new JuserCommnet();
		$JuserCommentModel->getPageString('jjonline@jjonline.cn');
		$leftBar  			 =	$this->__getLeftBar('UserComment');
		$rightBar = <<<STR
		<div class="JAuth_right">
			<div class="JAuth_content">JAuth_content</div>
		</div>
STR;
		$this->__show($leftBar,$rightBar,$UserInfo);
	}

	/**
	 * Juser会员中心查看修改资料(附绑定QQ、微博)
	 * @param null
	 * @return mixed
	 */
	public function UserInfo($UserInfo=null) {
		$leftBar  			 =	$this->__getLeftBar('UserInfo');
		$rightBar = <<<STR
		<div class="JAuth_right">
			<div class="JAuth_content">修改个人资料</div>
		</div>
STR;
		$this->__show($leftBar,$rightBar,$UserInfo);
	}

	/**
	 * Juser会员中心修改密码
	 * @param null
	 * @return mixed
	 */
	public function UserPassWd($UserInfo=null) {
		$leftBar  			 =	$this->__getLeftBar('UserPassWd');
		$rightBar = <<<STR
		<div class="JAuth_right">
			<div class="JAuth_content">修改账号密码</div>
		</div>
STR;
		$this->__show($leftBar,$rightBar,$UserInfo);
	}
	#登录状态下侧边栏快速生成
	private function __getLeftBar($naviName) {
		$blogUrl  	   =	BLOG_URL;
		$UserCenter    =	$naviName=='UserCenter'?'items current':'items';
		$UserComment   =	$naviName=='UserComment'?'items current':'items';
		$UserInfo 	   =	$naviName=='UserInfo'?'items current':'items';
		$UserPassWd    =	$naviName=='UserPassWd'?'items current':'items';
		$leftBar  = <<<STR
		<div class="JAuth_left">
			<ul class="Juser_nav">
				<li class="{$UserCenter}"><a href="{$blogUrl}?plugin=juser&a=UserCenter">会员中心</a></li>
				<li class="{$UserComment}"><a href="{$blogUrl}?plugin=juser&a=UserComment">评论管理</a></li>
				<li class="{$UserInfo}"><a href="{$blogUrl}?plugin=juser&a=UserInfo">修改资料</a></li>
				<li class="{$UserPassWd}"><a href="{$blogUrl}?plugin=juser&a=UserPassWd">修改密码</a></li>
			</ul>
			<ul class="Juser_nav">
				<li class="items signOut"><a href="{$blogUrl}?plugin=juser&a=SignOut">退出</a></li>
			</ul>
		</div>
STR;
		return $leftBar;
	}

	/**
	 * 手动退出登录
	 * @param string
	 * @return null
	 */
	public function SignOut($UserInfo=null) {
		if($UserInfo) {
			Juser::setAuthOut();
		}
		emDirect(BLOG_URL);
	}

	/**
	 * 输出html片段
	 * @param string  $leftString
	 * @param string  $rightString
	 * @param boolean or array[userInfo] $isUserCenter
	 * @return null
	 */
	private function __show($leftString='',$rightString='',$isUserCenter=false) {
		$class   			=	$isUserCenter?'JAuth':'JnoAuth';
		$userFigure			=	'';
		if($isUserCenter){			
			$userFigure		=	'<div class="Juser_info"><ul class="Juser_info_ul">';
			$userFigure    .=   '<li class="Juser_item Juser_item_mail"><img src="'.Juser_getGravatar($isUserCenter['mail']).'"><h2>'.$isUserCenter['mail'].'</h2><p>'.$isUserCenter['name'].'</p></li>';
			$userFigure     =   empty($isUserCenter['qq_openid'])?
								$userFigure.'<li class="Juser_item Juser_item_open Juser_item_qq"><img src="'.BLOG_URL.'content/plugins/juser/static/default_qq.jpg"><h2>腾讯QQ <em>未启用</em></h2><p><a href="'.BLOG_URL.'?plugin=juser&a=openLogin&type=qq">点击启用QQ登录</a></p></li>':
								$userFigure.'<li class="Juser_item Juser_item_open Juser_item_qq"><img src="'.$isUserCenter['qq_figure'].'"><h2>腾讯QQ <span>已启用</span></h2><p>'.$isUserCenter['qq_name'].'[<a href="'.BLOG_URL.'?plugin=juser&a=openLogin&type=qq">换绑</a>]</p></li>';
			$userFigure     =   empty($isUserCenter['sina_openid'])?
								$userFigure.'<li class="Juser_item Juser_item_open Juser_item_wb"><img src="'.BLOG_URL.'content/plugins/juser/static/default_wb.jpg"><h2>新浪微博 <em>未启用</em></h2><p><a href="'.BLOG_URL.'?plugin=juser&a=openLogin&type=sina">点击启用微博登录</a></p></li>':
								$userFigure.'<li class="Juser_item Juser_item_open Juser_item_wb"><img src="'.$isUserCenter['sina_figure'].'"><h2>新浪微博 <span>已启用</span></h2><p>'.$isUserCenter['sina_name'].'[<a href="'.BLOG_URL.'?plugin=juser&a=openLogin&type=sina">换绑</a>]</p></li>';
			$userFigure	   .=	'</ul></div>';
		}
		echo '<div class="container juser_container" style="position:relative;z-index:9;"><div class="juser_sign_tips juser_sign_success" id="juser_alert">Tips</div></div>';
		echo '<div class="container juser_container '.$class.'">';
		echo    $userFigure;
		echo 	'<div class="pull">';
		echo 		'<div class="pull-left Juser_left">';
		echo 			$leftString;
		echo 		'</div>';
		echo 		'<div class="pull-right Juser_right">';
		echo 			$rightString;
		echo 		'</div>';
		echo 	'</div>';
		echo '</div>';
	}

	/**
	 * 状态提示之错误
	 * @param $tips 提示内容
	 * @param $time 跳转等待时间
	 * @param $url  跳转到的action
	 * @return mixed
	 */
	private function __error($tips="错误！",$time=3,$action=null) {
		$Url = $action?BLOG_URL.'?plugin=juser&a='.$action:BLOG_URL.'?plugin=juser';
		echo '<div class="container juser_container"><div class="juser_tips juser_tips_error"><h3>操作失败</h3><div class="juser_error"><p class="juser_tips_text">'.$tips.'</p><p class="juser_tips_des">本页面将在<span>'.$time.'</span>秒后自动[<a href="'.$Url.'">跳转</a>]</p></div></div></div>';#具体跳转动作由js完成
	}

	/**
	 * 状态提示之成功
	 * @param $tips 提示内容
	 * @param $time 跳转等待时间
	 * @param $url  跳转到的action
	 * @return mixed
	 */
	private function __success($tips="成功！",$time=3,$action=null) {
		$Url = $action?BLOG_URL.'?plugin=juser&a='.$action:BLOG_URL.'?plugin=juser';
		echo '<div class="container juser_container"><div class="juser_tips juser_tips_success"><h3>操作成功</h3><div class="juser_success"><p class="juser_tips_text">'.$tips.'</p><p class="juser_tips_des">本页面将在<span>'.$time.'</span>秒后自动[<a href="'.$Url.'">跳转</a>]</p></div></div></div>';#具体跳转动作由js完成
	}

	/**
	 * Token令牌方法 产生token和效验token
	 * @param boolean $isCheck 默认产生token  传入非false的值则是效验Token
	 * @return mixed (string OR boolean)
	 */
	private function __Token($isCheck=false) {
		#效验token 避免误杀 一个Token仅允许尝试验证3次=>友好用户够用就好
		if($isCheck) {
			if(!isset($_SESSION['__TOKEN__'])) { return false; }
			$TokenExpire 	  			   =  1;
			if(isset($_SESSION['__TOKEN_EXPIRE__'])) {
				$TokenExpire  			   =  $_SESSION['__TOKEN_EXPIRE__'] + 1;
			}
			#该Token效验次数超过3次
			if($TokenExpire>3) {
				unset($_SESSION['__TOKEN__'],$_SESSION['__TOKEN_EXPIRE__']);
				return false;
			}
			$_SESSION['__TOKEN_EXPIRE__']  =  $TokenExpire;#记录效验次数
			$UserInputToken  			   =  $_POST['token'];#form表单中的token字段必须是token
			if(!empty($UserInputToken)) {
				$UserInputToken 		   =  explode('_',$UserInputToken);
				list($key,$val)  	       =  $UserInputToken;
				if(!empty($_SESSION['__TOKEN__'][$key]) && $_SESSION['__TOKEN__'][$key]===$val) {
					return true;
				}
				#非正常的效验失败 删除token 强行重新生成
				unset($_SESSION['__TOKEN__']);
				return false;
			}
			return false;
		}
		#构造Token
		// unset($_SESSION['__TOKEN__']);
		if(isset($_SESSION['__TOKEN__'])) {
			$Token   =	$_SESSION['__TOKEN__'];
			return key($Token).'_'.current($Token);
		}
		$key 							   =   strtoupper(uniqid('jusertokens'));
		$val 							   =   strtoupper(Juser_randString(24));
		$_SESSION['__TOKEN__'][$key]	   =   $val;
		return $key.'_'.$val;
	}

	/**
	 * ajax json返回方法（中断执行）
	 * @param array $data
	 * @return json string
	 */
	private function ajaxReturn($data=array()) {
		header('Content-Type:application/json; charset=utf-8');
		if(is_array($data)) {
            exit(json_encode($data));
		}
		exit(json_encode(array('code'=>500,'info'=>'Error Params.')));
	}
}