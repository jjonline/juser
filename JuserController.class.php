<?php
/**
 * Juser前台控制器类
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-30 09:30:09
 * @version $Id$
 */
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
		#判断是不是开放平台登录的用户今夕注册绑定
		$blogUrl  			 =   BLOG_URL;
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
			<form method="POST" id="juser_register_form" class="juser_form" action="{$blogUrl}?plugin=juser&a=doLogin">
				<input type="hidden" value="{$this->__Token()}" id="inputToken" name="token">
				<div class="formFiled"><label for="inputName">昵称</label><input type="text" class="juser_input" placeholder="请设置昵称" id="inputName" autocomplete="off" name="n"></div>
				<div class="formFiled"><label for="inputEmail">邮箱</label><input type="text" class="juser_input" placeholder="请输入邮箱" id="inputEmail" autocomplete="off" name="u"></div>
				<div class="formFiled"><label for="inputUrl">网址</label><input type="text" class="juser_input" placeholder="请输入博客或空间地址" id="inputUrl" autocomplete="off" name="url"></div>
				<div class="formFiled"><label for="inputPwd">密码</label><input type="password" class="juser_input" placeholder="请设置您密码" id="inputPwd" name="p"></div>
				<div class="formFiled"><label for="inputrPwd">重复密码</label><input type="password" class="juser_input" placeholder="请将设置的密码重复一遍" id="inputrPwd" name="rp"></div>
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
		var_dump(__METHOD__);
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
			#post+ajax 处理登录请求
			if(IS_POST && IS_AJAX) {
				if($this->__Token(true)) {
					$this->ajaxReturn(array('code'=>'500','info'=>'Token Err.'));
				}
				$this->ajaxReturn();
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
			}else {
				$this->__error($tips.'失败',6,'UserCenter');
			}
		}else {
			#删除可能存在的session
			if(isset($_SESSION['OpenUserInfo'])) {unset($_SESSION['OpenUserInfo']);}
			#仅存在后台申请绑定 但申请绑定的开放平台账号被其他账号绑定了或账号相同
			if($hasUser['id'] !== $isLogin['id']) {
				$this->__error('更换绑定失败：该'.$UserInfo['typeName'].'账号已被本站其他账号绑定！',15,'UserCenter');
			}else {
				$this->__error('更换绑定失败：已绑定的'.$UserInfo['typeName'].'账号与申请更换绑定的'.$UserInfo['typeName'].'账号相同！',15,'UserCenter');
			}
			emDirect(BLOG_URL.'?plugin=juser&a=UserCenter&isOpenBind=1');
		}
	}

	/**
	 * Juser会员中心
	 * @param null
	 * @return mixed
	 */
	public function UserCenter($isLogin=null) {
		var_dump(__METHOD__);
	}

	/**
	 * Juser会员中心查看已有评论
	 * @param null
	 * @return mixed
	 */
	public function UserComment($isLogin=null) {

	}

	/**
	 * Juser会员中心查看修改资料(附绑定QQ、微博)
	 * @param null
	 * @return mixed
	 */
	public function UserInfo($isLogin=null) {

	}

	/**
	 * Juser会员中心修改密码
	 * @param null
	 * @return mixed
	 */
	public function UserPassWd($isLogin=null) {

	}

	/**
	 * 输出html片段
	 * @param string  $leftString
	 * @param string  $rightString
	 * @param boolean $isUserCenter
	 * @return null
	 */
	private function __show($leftString='',$rightString='',$isUserCenter=false) {
		$class   =  $isUserCenter?'JAuth':'JnoAuth';
		echo '<div class="container juser_container" style="position:relative;z-index:9;"><div class="juser_sign_tips juser_sign_success" id="juser_alert">Tips</div></div>';
		echo '<div class="container juser_container '.$class.'">';
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