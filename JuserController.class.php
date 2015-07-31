<?php
/**
 * Juser前台控制器类
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-30 09:30:09
 * @version $Id$
 */
class JuserController {
	
	#构造函数
	public function __construct() {

	}

	/**
	 * Juser默认显示页面
	 * @param null
	 * @return mixed
	 */
	public function __empty() {
		var_dump(__METHOD__);
	}

	/**
	 * Juser注册页面
	 * @param null
	 * @return mixed
	 */
	public function register($isLogin=null) {
		var_dump(__METHOD__);
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
		var_dump(__METHOD__);
	}

	/**
	 * Juser登录处理器
	 * @param null
	 * @return mixed
	 */	
	public function doLogin($isLogin=null) {

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
			emDirect(BLOG_URL.'?plugin=juser&type='.strtolower($_GET['type']));
		}		
		if($acceToken) {
			$openID 		=	$Open->getOpenID($acceToken);
			if($openID){
				$OpenUser   =	$Open->getUserInfo($acceToken);
				$this->__openLoginHandle($isLogin,$OpenUser);#调度处理
			}
		}else {
			#回到插件首页
			emDirect(BLOG_URL.'?plugin=juser');
		}
	}
	#open登录初始化
	private function openInit() {
		if(!isset($_GET['type'])) {emDirect(BLOG_URL.'?plugin=juser');}
		$OpenType 	 = 	 strtolower($_GET['type']);
		#appkey appsecret配置
		global $CACHE;
		$Config    	 =  $CACHE->readCache('jususr_config');
		if(!isset($Config[$OpenType])) {exit('AppKey、AppSecret不存在，请先配置插件');}
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
				$this->__success($UserInfo['typeName'].'登录成功',3);
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
				$this->__success($tips.'成功',3);
			}else {
				$this->__error($tips.'失败',6);
			}
		}else {
			#删除可能存在的session
			if(isset($_SESSION['OpenUserInfo'])) {unset($_SESSION['OpenUserInfo']);}
			#仅存在后台申请绑定 但申请绑定的开放平台账号被其他账号绑定了或账号相同
			if($hasUser['id'] !== $isLogin['id']) {
				$this->__error('更换绑定失败：该'.$UserInfo['typeName'].'账号已被本站其他账号绑定！',6);
			}else {
				$this->__error('更换绑定失败：已绑定的'.$UserInfo['typeName'].'账号与申请更换绑定的'.$UserInfo['typeName'].'账号相同！',6);
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
	 * 状态提示之错误
	 * @param $tips 提示内容
	 * @param $time 跳转等待时间
	 * @return mixed
	 */
	private function __error($tips="错误！",$time=3) {
		echo $tips;
		exit;
	}

	/**
	 * 状态提示之成功
	 * @param $tips 提示内容
	 * @param $time 跳转等待时间
	 * @return mixed
	 */
	private function __success($tips="成功！",$time=3) {
		echo $tips;
		exit;
	}
}