<?php
/*
Plugin Name: 会员系统
Version: 1.0
Plugin URL:http://blog.jjonline.cn/theme/juser.html
Description:加入QQ、微博开放平台登录功能的emlog会员系统，本会员系统与J2主题完全兼容，也会兼容后续本po出的其他主题。
ForEmlog: 5.3.0+
Author: Jea杨
Author Email: JJonline@JJonline.Cn
Author URL: http://blog.jjonline.cn
*/
if(!defined('EMLOG_ROOT')) {exit('Juser 运行在emlog博客框架下!');}
require_once 'juser_functions.php';#引入函数库、数据库操作类
#引入sdk
require_once 'JuserOpen.class.php';#引入开放平台sdk
#实例化
// $obj = JuserOpen::getInstance('sina','4025051940','ee3278d08ec58d98e85de5024734e660','http://blog.jjonline.cn/');
// #获得跳转url 让用户授权 
// echo $obj->getRedirectUrl().PHP_EOL;
// $obj1 = JuserOpen::getInstance('qq','200730','194bccdb27a20ee1a6831eec141d81c2','http://blog.jjonline.cn/');
// echo $obj1->getRedirectUrl();
// var_dump($_GET);
// var_dump(Juser_is_url('http://www.jjonline.cn/casd/index.gif?c=a&b=1#code'));
// global $CACHE;
// $user_cache = $CACHE->readCache('user');
// var_dump($user_cache);
// var_dump(Juser_get_admin_mail());
// $obj = Juser::getinstance();
// var_dump($obj::getUserInfoByID(1));
// var_dump($obj->setAuthCookie(1,false));
// var_dump($_COOKIE['JuserCookie']);
//var_dump($obj->checkPassword('sd152134','$P$BrNij/fRUh3vPBPm0YNW3zbKOsFPQw/'));
/**
 * Juser载入后台菜单
 * @param null
 * @return mixed
 */
function juser_menu() {
	echo '<div class="sidebarsubmenu" id="juser"><a href="./plugin.php?plugin=juser" style="background:url('.BLOG_URL.'content/plugins/juser/static/user.png) no-repeat 20px 1px;">会员</a></div>';
}

/**
 * Juser载入后台设置静态文件
 * @param null
 * @return mixed
 */
function juser_static() {
	echo '<script type="text/javascript">'.PHP_EOL;
	echo 'if(typeof jQuery == "undefined") {'.PHP_EOL;
	echo '  document.write(unescape("%3Cscript%20type%3D%22text/javascript%22%20src%3D%22http%3A//apps.bdimg.com/libs/jquery/1.9.1/jquery.min.js%22%3E%3C/script%3E"));'.PHP_EOL.'}'.PHP_EOL;
	echo '</script>'.PHP_EOL;
	echo '<link href="'.BLOG_URL.'content/plugins/juser/static/view.css" type="text/css" rel="stylesheet"/>'.PHP_EOL;
	echo '<script src="'.BLOG_URL.'content/plugins/juser/static/Jlib.js" type="text/javascript"></script>'.PHP_EOL;
	echo '<script src="'.BLOG_URL.'content/plugins/juser/static/view.js" type="text/javascript"></script>'.PHP_EOL;
}
#数据库备份动作--备份juser表
function juser_data_backup(){
	global $tables;
	$Juser      = Juser::getInstance();
	$JuserModel = $Juser->getDbInstance();
	$isExist 	= $JuserModel->query('show tables like "'.$Juser->getTable().'"');
	if($JuserModel->num_rows($isExist) != 0) { array_push($tables, 'juser_data'); }
}
addAction('data_prebakup', 'juser_data_backup');#后台数据库备份动作添加juser_data表
addAction('adm_head','juser_static');#后台载入css、js等文件
addAction('adm_sidebar_ext', 'juser_menu');#后台载入侧边栏