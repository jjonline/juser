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
			$OpenUserTips    =   '<div class="Juser_opentips_container"><p class="Juser_opentips_text"></strong>请注册或<a href="'.$blogUrl.'?plugin=juser&a=login">登录</a>后绑定您的'.$OpenUserInfo['typeName'].'<strong>('.$OpenUserInfo[$key].')</strong></p></div>';
		}
		$rightBar = <<<STR
		{$OpenUserTips}
		<div class="Juser_sign_left">
			<h1>会员注册</h1>
			<form method="POST" id="Juser_register_form" class="Juser_form" action="{$blogUrl}?plugin=juser&a=doRegister">
				<input type="hidden" value="{$this->__Token()}" id="Juser_iptToken" name="token">
				<div class="Juser_ipt">
					<label for="Juser_iptMail">邮箱</label>
					<input type="text" class="Juser_input" placeholder="输入邮箱" id="Juser_iptMail" autocomplete="off" name="u">
				</div>
				<div class="Juser_ipt">
					<label for="Juser_iptPwd">密码</label>
					<input type="password" class="Juser_input" placeholder="设置您密码" id="Juser_iptPwd" name="p">
				</div>
				<div class="Juser_ipt">
					<label for="Juser_iptrPwd">重复密码</label>
					<input type="password" class="Juser_input" placeholder="将设置的密码重复一遍" id="Juser_iptrPwd" name="rp">
				</div>
				<div class="Juser_ipt">
					<label for="Juser_iptName">昵称</label>
					<input type="text" class="Juser_input" placeholder="设置昵称，可留空" id="Juser_iptName" autocomplete="off" name="n">
				</div>
				<div class="Juser_ipt">
					<label for="Juser_iptUrl">网址</label>
					<input type="text" class="Juser_input" placeholder="博客或空间地址，可留空" id="Juser_iptUrl" autocomplete="off" name="url">
				</div>
				<div class="Juser_ipt juser_ipt_sub">
					<input type="submit" class="Juser_input Juser_Btn" id="Juser_Sub_register" value="立即注册">
				</div>
				<div class="Juser_ipt Juser_ipt_open">
					<span>用其他方式注册：</span>
					<a href="{$blogUrl}?plugin=juser&a=openLogin&type=qq" class="Juser_ipt_open_Btn Juser_ipt_open_qq">QQ</a>
					<a href="{$blogUrl}?plugin=juser&a=openLogin&type=sina" class="Juser_ipt_open_Btn Juser_ipt_open_wb">微博</a>
				</div>
			</form>
		</div>
STR;
		$leftBar  = <<<STR
		<div class="Juser_sign_right">
			<h2>已经是{$this->BlogInfo['blogname']}会员？</h2>
			<h3><a href="{$blogUrl}?plugin=juser&a=login" class="Juser_Btn">立马去登录</a></h3>
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
			}else {
				#效验数据失败 可能是邮箱、密码格式问题
				if(!$RegistData['mail']) {
					$this->ajaxReturn(array('code'=>'501','info'=>'该邮箱禁止注册'));
				}
				$this->ajaxReturn(array('code'=>'501','info'=>'密码格式有误'));
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
			$OpenUserTips    =   '<div class="Juser_opentips_container"><p class="Juser_opentips_text"></strong>请登录或<a href="'.$blogUrl.'?plugin=juser&a=register">注册</a>后绑定您的'.$OpenUserInfo['typeName'].'<strong>('.$OpenUserInfo[$key].')</strong></p></div>';
		}
		$rightBar = <<<STR
		{$OpenUserTips}
		<div class="Juser_sign_left Juser_login_left">
			<h1>会员登录</h1>
			<form method="POST" id="Juser_login_form" class="Juser_form" action="{$blogUrl}?plugin=juser&a=doLogin">
				<input type="hidden" value="{$this->__Token()}" id="Juser_iptToken" name="token">
				<div class="Juser_ipt">
					<label for="Juser_iptMail">邮箱</label>
						<input type="text" class="Juser_input" placeholder="请输入您的邮箱" id="Juser_iptMail" autocomplete="off" name="u">
				</div>
				<div class="Juser_ipt">
					<label for="Juser_iptPwd">密码</label>
					<input type="password" class="Juser_input" placeholder="请输入您的密码" id="Juser_iptPwd" name="p">
				</div>
				<div class="Juser_ipt juser_ipt_sub">
					<input type="submit" class="Juser_input Juser_Btn" id="Juser_Sub_login" value="登录">
				</div>
				<div class="Juser_ipt Juser_ipt_open">
					<span>用其他方式登录：</span>
					<a href="{$blogUrl}?plugin=juser&a=openLogin&type=qq" class="Juser_ipt_open_Btn Juser_ipt_open_qq">QQ</a>
					<a href="{$blogUrl}?plugin=juser&a=openLogin&type=sina" class="Juser_ipt_open_Btn Juser_ipt_open_wb">微博</a>
				</div>
			</form>
		</div>
STR;
		$leftBar  = <<<STR
		<div class="Juser_sign_right">
			<h2>还不是{$this->BlogInfo['blogname']}会员？</h2>
			<h3><a href="{$blogUrl}?plugin=juser&a=register" class="Juser_Btn">果断去注册</a></h3>
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

	/**
	 * 开放平台登录初始化
	 * @param null
	 * @return mixed
	 */
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

	/**
	 * 开放平台登录回调后的判定调度
	 * @param miexd $isLogin 是否登录的参数[登录情况下该参数将传递用户信息数组]
	 * @param array $UserInfo 开放平台登录后获取到的开放平台用户信息
	 * @return mixed
	 */
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
		$leftBar  			 =	$this->__getAuthLeft('UserCenter');
		$rightBar = <<<STR
		<div class="JAuth_right">
			<div class="JAuth_content">欢迎来到晶晶的博客会员中心，本会员中心功能尚在完善或添加，<a href="http://blog.jjonline.cn/theme/juser.html" target=_blank>[点此]</a>来给我提意见吧！</div>
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
		$page   				=  isset($_GET['page']) && ctype_digit((string)$_GET['page']) ?intval($_GET['page']):1;
		$JuserCommentModel   	=  new JuserCommnet();
		$Comment  	 		 	=  $JuserCommentModel->page($page)->select($UserInfo['mail']);
		$PageString          	=  $JuserCommentModel->getPageString($UserInfo['mail']);
		$leftBar  			 	=	$this->__getAuthLeft('UserComment');
		$CommentString       	=  '<div class="JAuth_right">
										<div class="JAuth_content">
											<ul class="Juser_comment">';
		if($Comment) {
			foreach ($Comment as $key => $value) {
				$CommentString .= '<li class="Juser_comment_items"><p class="Juser_comment_time">'.date('Y-m-d H:i:s',$value['date']).'</p>';
				$CommentString .= '<div class="Juser_comment_list"><p class="Juser_comment_text">'.htmlClean($value['comment']).'</p>';
				$CommentString .= '<p class="Juser_comment_info">评论文章：<a href="'.$value['log_url'].'" target=_blank>《'.$value['log_title'].'》</a> 评论：'.$value['comnum'].'</p>';
				$CommentString .= '</div></li>';
			}
		}else {
			$CommentString     .=  '<li class="Juser_comment_none">不给力，暂无评论！</li>';
		}
		$CommentString         .=  			'</ul>'.$PageString.'
										</div>
									</div>';
		$this->__show($leftBar,$CommentString,$UserInfo);
	}

	/**
	 * Juser会员中心查看修改资料(附绑定QQ、微博)
	 * @param null
	 * @return mixed
	 */
	public function UserInfo($UserInfo=null) {
		$leftBar  			 =	$this->__getAuthLeft('UserInfo');
		$retTime  			 =  date('Y-m-d H:i:s',$UserInfo['time']);
		$name                =  empty($UserInfo['name'])?'路人乙':$UserInfo['name'];
		$url                 =  empty($UserInfo['url'])?'':$UserInfo['url'];
		$qq  				 =  empty($UserInfo['qq'])?'':$UserInfo['qq'];
		$phone				 =  empty($UserInfo['phone'])?'':$UserInfo['phone'];
		$rightBar = <<<STR
	<div class="JAuth_right">
		<div class="JAuth_content">
			<div class="JAuth_update_info">
				<div class="Juser_ipt">
					<label for="Juser_iptName">注册时间</label>
					{$retTime}
				</div>
				<form method="POST" id="Juser_userInfo_form" class="Juser_form" action="{$blogUrl}?plugin=juser&a=doChange">
				<div class="Juser_ipt">
					<label for="Juser_iptName">昵称</label>
					<input type="text" class="Juser_input" placeholder="设置昵称，可留空" id="Juser_iptName" autocomplete="off" name="n" value="{$name}">
				</div>
				<div class="Juser_ipt">
					<label for="Juser_iptUrl">网址</label>
					<input type="text" class="Juser_input" placeholder="博客或空间地址，可留空" id="Juser_iptUrl" autocomplete="off" name="url" value="{$url}">
				</div>
				<div class="Juser_ipt">
					<label for="Juser_iptQQ">QQ</label>
					<input type="text" class="Juser_input" placeholder="QQ号码，可留空" id="Juser_iptQQ" autocomplete="off" name="qq" value="{$qq}">
				</div>
				<div class="Juser_ipt">
					<label for="Juser_iptPhone">手机号</label>
					<input type="text" class="Juser_input" placeholder="手机号码，可留空" id="Juser_iptPhone" autocomplete="off" name="phone" value="{$phone}">
				</div>
				<div class="Juser_ipt juser_ipt_sub">
					<input type="submit" class="Juser_input Juser_Btn" id="Juser_Sub_register" value="确认修改资料">
				</div>
				</form>
			</div>
		</div>
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
		$leftBar  			 =	$this->__getAuthLeft('UserPassWd');
		$rightBar = <<<STR
		<div class="JAuth_right">
			<div class="JAuth_content">
				<div class="JAuth_update_pwd">
				<div class="Juser_ipt">
					<h2>修改账户密码</h2>
				</div>
				<form method="POST" id="Juser_userInfo_form" class="Juser_form" action="{$blogUrl}?plugin=juser&a=doChange">
				<div class="Juser_ipt">
					<label for="Juser_iptoPwd">原密码</label>
					<input type="password" class="Juser_input" placeholder="正在使用的密码" id="Juser_iptoPwd" autocomplete="off" name="n">
				</div>
				<div class="Juser_ipt">
					<label for="Juser_iptPwd">新密码</label>
					<input type="password" class="Juser_input" placeholder="新密码" id="Juser_iptPwd" autocomplete="off" name="url">
				</div>
				<div class="Juser_ipt">
					<label for="Juser_iptrPwd">重复新密码</label>
					<input type="password" class="Juser_input" placeholder="重复新密码" id="Juser_iptrPwd" autocomplete="off" name="phone">
				</div>
				<div class="Juser_ipt juser_ipt_sub">
					<input type="submit" class="Juser_input Juser_Btn" id="Juser_Sub_register" value="确认修改资料">
				</div>
				</form>
			</div>
			</div>
		</div>
STR;
		$this->__show($leftBar,$rightBar,$UserInfo);
	}
	
	/**
	 * Juser会员中心修改资料或修改密码操作方法
	 * @param null
	 * @return mixed
	 */
	public function doChange($UserInfo=null) {
		if(!IS_POST || !IS_AJAX || !$UserInfo) {
			emDirect(BLOG_URL.'?plugin=juser&a=UserCenter');
		}
		$InputData       		=	array();
		foreach($_POST as $key => $value) {
			$_POST[$key] 		=	trim($value);
		}
		#用户昵称处理
		if(!empty($_POST['n']) && mb_strlen($_POST['n'],'UTF-8')<8 && $UserInfo['name']!=$_POST['n']) {
			$fobidName 			=	array_merge(Juser_get_admin_name(),array('admin','administrator','writer','visitor',Option::get('blogname')));
			$UserName 			=	strip_tags($_POST['n']);
			$InputData['name'] 	=	str_replace($fobidName,'**',$UserName);
		}
		#url
		if(!empty($_POST['url']) && Juser_is_url($_POST['url']) && $UserInfo['url']!=$_POST['url']) {
			$InputData['url']  	=	rtrim($_POST['url'],'/').'/';
		}
		#qq
		if(!empty($_POST['qq']) && Juser_is_uid($_POST['qq']) && $UserInfo['qq']!=$_POST['qq']) {
			$InputData['qq']  	=	$_POST['qq'];
		}
		#phone
		if(!empty($_POST['phone']) && Juser_is_phone($_POST['phone']) && $UserInfo['phone']!=$_POST['phone']) {
			$InputData['phone'] =	$_POST['phone'];
		}
		#修改密码动作
		$isChangePwd            =   (!empty($_POST['op']) || !empty($_POST['p']) || !empty($_POST['rp']));
		if($isChangePwd) {
			if(empty($_POST['op']) || !Juser_is_password($_POST['op'])) {
				$this->ajaxReturn(array('code'=>'501','info'=>'原密码格式错误'));
			}
			if(empty($_POST['p']) || !Juser_is_password($_POST['p'])) {
				$this->ajaxReturn(array('code'=>'501','info'=>'新密码格式错误'));
			}
			if(empty($_POST['rp']) || !Juser_is_password($_POST['rp'])) {
				$this->ajaxReturn(array('code'=>'501','info'=>'重复新密码格式错误'));
			}
			if($_POST['rp']!=$_POST['p']) {
				$this->ajaxReturn(array('code'=>'501','info'=>'原密码和新密码不一致'));
			}
			if($_POST['p']==$_POST['op']) {
				$this->ajaxReturn(array('code'=>'501','info'=>'密码未修改'));
			}
			#效验原始密码 能执行到此步骤则用户一定存在
			$isCheck                   =	Juser::checkPassword($_POST['op'],$UserInfo['password']);
			if($isCheck) {
				$InputData['password'] =	Juser::genPassword($_POST['op']);
			}else {
				$this->ajaxReturn(array('code'=>'501','info'=>'效验原密码失败'));
			}
		}
		if(!$InputData) {
			$this->ajaxReturn(array('code'=>'501','info'=>'资料未修改'));
		}
		#执行写入数据
		$InputData['id']    =	$UserInfo['id'];
		$JuserModel  		=	Juser::getJuserModel();
		$ret 		 		=	$JuserModel->data($InputData)->save();
		if(!$ret) {
			$this->ajaxReturn(array('code'=>'501','info'=>'操作失败，服务器异常'));
		}
		if($isChangePwd) {
			$this->ajaxReturn(array('code'=>'200','info'=>'密码修改成功'));
		}
		$this->ajaxReturn(array('code'=>'200','info'=>'资料修改成功'));
	}

	/**
	 * 快速获取登录状态下左侧导航栏html
	 * @param string $naviName 当前导航名
	 * @return null
	 */
	private function __getAuthLeft($naviName) {
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
		$class   			=	$isUserCenter?'Juser_Auth':'Juser_noAuth';
		$userFigure			=	'';
		#登录状态下顶部用户头像 mail 开放平台信息展示
		if($isUserCenter){			
			$userFigure		=	'<div class="Juser_figure">
									<ul class="Juser_figure_ul">';
			$userFigure    .=   		'<li class="Juser_figure_item Juser_figure_item_mail">
											<img src="'.Juser_getGravatar($isUserCenter['mail']).'">
											<h2>'.$isUserCenter['mail'].'</h2>
											<p>'.$isUserCenter['name'].'</p>
										</li>';
			$userFigure     =   empty($isUserCenter['qq_openid'])?
										$userFigure.'
										<li class="Juser_figure_item Juser_figure_item_open Juser_figure_item_qq">
											<img src="'.BLOG_URL.'content/plugins/juser/static/default_qq.jpg">
											<h2>腾讯QQ <em>未启用</em></h2>
											<p><a href="'.BLOG_URL.'?plugin=juser&a=openLogin&type=qq">点击启用QQ登录</a></p>
										</li>':
										$userFigure.'
										<li class="Juser_figure_item Juser_figure_item_open Juser_figure_item_qq">
											<img src="'.$isUserCenter['qq_figure'].'">
											<h2>腾讯QQ <span>已启用</span></h2>
											<p>'.$isUserCenter['qq_name'].'[<a href="'.BLOG_URL.'?plugin=juser&a=openLogin&type=qq">换绑</a>]</p>
										</li>';
			$userFigure     =   empty($isUserCenter['sina_openid'])?
										$userFigure.'
										<li class="Juser_figure_item Juser_figure_item_open Juser_figure_item_wb">
											<img src="'.BLOG_URL.'content/plugins/juser/static/default_wb.jpg">
											<h2>新浪微博 <em>未启用</em></h2>
											<p><a href="'.BLOG_URL.'?plugin=juser&a=openLogin&type=sina">点击启用微博登录</a></p>
										</li>':
										$userFigure.'
										<li class="Juser_figure_item Juser_figure_item_open Juser_figure_item_wb">
											<img src="'.$isUserCenter['sina_figure'].'">
											<h2>新浪微博 <span>已启用</span></h2>
											<p>'.$isUserCenter['sina_name'].'[<a href="'.BLOG_URL.'?plugin=juser&a=openLogin&type=sina">换绑</a>]</p>
										</li>';
			$userFigure	   .=		'</ul>
								</div>';
		}
		echo '<div class="Juser '.$class.' container">';
		echo '<div class="Juser_tips_container"><div class="Juser_tips Juser_tips_success Juser_tips_error Juser_tips_alert Juser_tips_loading" id="Juser_tips"><p>Tips</p></div></div>';
		echo    $userFigure;
		echo 	'<div class="Juser_container">';
		echo 		'<div class="Juser_left">';
		echo 			$leftString;
		echo 		'</div>';
		echo 		'<div class="Juser_right">';
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
		echo '<div class="Juser container">
				<div class="Juser_status Juser_status_error">
					<h3>操作失败</h3>
					<div class="Juser_status_text">
						<p class="Juser_status_des">'.$tips.'</p>
						<p class="Juser_status_tips">本页面将在<span>'.$time.'</span>秒后自动[<a href="'.$Url.'">跳转</a>]</p>
					</div>
				</div>
			</div>';#具体跳转动作由js完成
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
		echo '<div class="Juser container">
				<div class="Juser_status Juser_status_success">
					<h3>操作成功</h3>
					<div class="Juser_status_text">
						<p class="Juser_status_des">'.$tips.'</p>
						<p class="Juser_status_tips">本页面将在<span>'.$time.'</span>秒后自动[<a href="'.$Url.'">跳转</a>]</p>
					</div>
				</div>
			</div>';#具体跳转动作由js完成
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