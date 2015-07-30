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
require_once 'JusserRouter.class.php';
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
include View::getView('header');var_dump(JusserRouter::getActionName());
include View::getView('footer');