<?php
/*
Plugin Name: 会员系统
Version: 1.0
Plugin URL:http://blog.jjonline.cn/theme/juser.html
Description:**加入QQ、微博开放平台登录功能的emlog会员系统**，本会员系统与J2主题完全兼容，也会兼容后续本po出的其他主题(星号范围内为付费版功能)。
Author: Jea杨
Author Email: JJonline@JJonline.Cn
Author URL: http://blog.jjonline.cn
*/
if(!defined('EMLOG_ROOT')) {exit('Juser 运行在emlog博客框架下!');}
require_once 'juser_functions.php';#引入插件基础函数库::语言结构，当函数使用会使zend引擎额外更多的事儿

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
	echo '<script type="text/javascript">';
	echo 'if(typeof jQuery == "undefined") {';
	echo '  document.write(unescape("%3Cscript%20type%3D%22text/javascript%22%20src%3D%22http%3A//apps.bdimg.com/libs/jquery/1.9.1/jquery.min.js%22%3E%3C/script%3E"));}';
	echo '</script>'."\r\n";
	echo '<link href="'.BLOG_URL.'content/plugins/juser/static/view.css" rel="stylesheet" type="text/css" />'."\r\n";
	echo '<script src="'.BLOG_URL.'content/plugins/juser/static/Jlib.js" type="text/javascript"></script>'."\r\n";
	echo '<script src="'.BLOG_URL.'content/plugins/juser/static/view.js" type="text/javascript"></script>'."\r\n";
	// $obj = juser::getInstance();
	// dump($obj->getDbInstance());
}

/**
 * Juser后台c层
 * @param null
 * @return mixed
 */
function plugin_setting_view() {
	echo '<div class="juser_pull">';#begin wrap
	echo '<h1 class="juser_title">会员系统管理 <em>对会员系统进行设置以及会员数据管理</em></h1>';



	echo '</div>';#end wrap
}

/**
 * Juser后台m层
 * @param null
 * @return mixed
 */
function plugin_setting() {

}
addAction('adm_head','juser_static');
addAction('adm_sidebar_ext', 'juser_menu');