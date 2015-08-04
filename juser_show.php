<?php
/**
 * 
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-23 10:22:16
 * @version $Id$
 */
if(!defined('EMLOG_ROOT')) {exit('Juser 运行在emlog博客框架下!');}
require_once 'juser_functions.php';#引入函数库、数据库操作类
require_once 'JuserOpen.class.php';
require_once 'JuserController.class.php';
require_once 'JuserRouter.class.php';
require_once 'JuserCommnet.class.php';
/*===================================================================================================*/
global $CACHE;
$BlogInfo  	 		=	$CACHE->readCache('options');
$blogname    		=   $BlogInfo['blogname'];
$bloginfo    		= 	$BlogInfo['bloginfo'];
$site_title  		= 	$blogname;
$site_key 	 		= 	$BlogInfo['site_key'];
$site_description   =   $blogname.'用户中心。';
$icp 		 		=   $BlogInfo['icp'];
$footer_info 		=   $BlogInfo['footer_info'];
/*===================================================================================================*/
$isLogin 			=	Juser::isLogin();
$Acttion  			=	JuserRouter::getActionName();
#登录状态下的控制器矛盾处理
if($isLogin && in_array($Acttion,array('__empty','register','login'))) {
	emDirect(BLOG_URL.'?plugin=juser&a=UserCenter');
}
#非登录状态下的控制器矛盾处理
if(!$isLogin && in_array($Acttion,array('doChange','usercenter','userinfo','userpasswd','usercomment'))) {
	emDirect(BLOG_URL.'?plugin=juser&a=login');
}
$site_title 	    = 	Juser_getTitle($Acttion).$site_title;
$JuserController	=	new JuserController();
$ReflctionClass 	=	new ReflectionClass('JuserController');
#调度执行各种方法
if($ReflctionClass->hasMethod($Acttion)) {
	$ReflectionMethod  = $ReflctionClass->getMethod($Acttion);
	if($ReflectionMethod->isPublic() && !$ReflectionMethod->isStatic()) {
		if(IS_GET && !in_array($Acttion,array('openlogin'))){
			include View::getView('header');
		}
		$ReflectionMethod->inVoke($JuserController,$isLogin);
		if(IS_GET && !in_array($Acttion,array('openlogin'))) {
			include View::getView('footer');
		}
	}else {
		emDirect(BLOG_URL.'?plugin=juser');
	}		
}else {
	emDirect(BLOG_URL.'?plugin=juser');
}
/*===================================================================================================*/
function Juser_getTitle($action) {
	$Title 			= 	array(
		'__empty' 		=>	'登录_',
		'register' 		=>	'注册_',
		'login' 		=>	'登录_',
		'openlogin' 	=>	'',
		'opencallback' 	=>	'',
		'usercenter' 	=>	'会员中心_',
		'userinfo' 		=>	'个人资料_',
		'userpasswd'	=>  '修改密码_',
		'usercomment'	=>	'评论管理_',
	);
	return empty($Title[$action])?'':$Title[$action];
}
/*===================================================================================================*/
// $options_cache = $CACHE->readCache('options');
// $kl_album_config = unserialize($options_cache['kl_album_config']);
// $blogname = $options_cache['blogname'];
// $bloginfo = $options_cache['bloginfo'];
// $site_title = $options_cache['blogname'];
// $site_description = $options_cache['bloginfo'];
// $site_key = $options_cache['site_key'];
// $comments = array('commentStacks'=>array(), 'commentPageUrl'=>'');
// $ckname = $ckmail = $ckurl = $verifyCode = false;
// $icp = $options_cache['icp'];
// $footer_info = $options_cache['footer_info'];
// $allow_remark = 'n';
// $log_content = $log_title = $logid = $content_info = '';
// $site_title = empty($kl_album_title) ? $site_title : $kl_album_title.' - '.$site_title;
// $log_title = $kl_album_title;