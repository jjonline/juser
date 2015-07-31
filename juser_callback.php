<?php
/**
 * 插件激活禁用时自动触发调用
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-23 10:23:16
 * @version $Id$
 */
if(!defined('EMLOG_ROOT')) {exit('Juser 运行在emlog博客框架下!');}
require_once 'juser_functions.php';
/**
 * 插件激活做的一些初始化的事情--数据库安装检测、标记
 * @param null
 * @return mixed
 */
function callback_init() {
	$plugin_dir   = dirname(__FILE__);
	$Juser 		  = Juser::getInstance();
	$tableName    = $Juser->getTable();
	$dbcharset 	  = 'utf8';
	$type 		  = 'MYISAM';
	$add 		  = $Juser->getDbInstance()->getMysqlVersion()>'4.1'?"ENGINE=$type DEFAULT CHARSET=$dbcharset;":"TYPE=$type;";
	$sql 		  = "
					CREATE TABLE IF NOT EXISTS `{$tableName}` (
						`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
						`time` bigint(20) unsigned DEFAULT NULL,
						`name` char(20) DEFAULT '路人乙',
						`mail` char(128) NOT NULL,
						`url` char(128) DEFAULT NULL,
						`password` char(64) NOT NULL,
						`sex` enum('f','m') NOT NULL DEFAULT 'f',
						`qq_name` char(20) DEFAULT NULL,
						`qq_openid` char(64) DEFAULT NULL,
						`qq_token` char(32) DEFAULT NULL,
						`qq_figure` char(128) DEFAULT NULL,
						`sina_name` char(20) DEFAULT NULL,
						`sina_openid` bigint(20) unsigned DEFAULT NULL,
						`sina_token` char(32) DEFAULT NULL,
						`sina_figure` char(128) DEFAULT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `email` (`mail`),
						KEY `qq_openid` (`qq_openid`),
						KEY `sina_openid` (`sina_openid`)
					)".$add;
	$Juser->getDbInstance()->query($sql);
	#检查并建立配置文件缓存 type=>['key'=>,'secret']
	global $CACHE;
	if(!is_file(EMLOG_ROOT.'/content/cache/jususr_config.php')) {
		$CACHE->cacheWrite(serialize(array(0=>array('key'=>'http://blog.jjonline.cn','secret'=>time()))),'jususr_config');
	}
	#标记已安装
	if(!is_file($plugin_dir.'/install.lock')) {
		$Juser::checkJuser();
		file_put_contents($plugin_dir.'/install.lock','1');
	}
}

/**
 * 插件禁用
 * @param null
 * @return mixed
 */
function callback_rm() {}