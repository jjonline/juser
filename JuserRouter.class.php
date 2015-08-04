<?php
/**
 * Juser路由类 URL解析调度
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-30 09:29:10
 * @version $Id$
 */
if(!defined('EMLOG_ROOT')) {exit('Juser 运行在emlog博客框架下!');}
class JuserRouter {
	/**
     * Open回调
     * @access public
     * @return void
     */
	public static function OpenCallBackInit(){
		if(isset($_GET['plugin']) && $_GET['plugin']=='juser') {
			#open回调参数 自动效验state 防止crsf
			if(isset($_SESSION['state']) && !empty($_GET['state']) && ($_GET['state']==$_SESSION['state']) && !empty($_GET['code'])) {
				unset($_SESSION['state']);#重置state
				define('JUSER_OPEN_CODE',$_GET['code']);
			}
			#控制器参数
			define('JUSER_ACTION_NAME',self::getActionName());
		}
	}

	/**
     * 获得操作名
     * @access public
     * @return string
     */
	public static function getActionName(){
		$action   = !empty($_POST['a'])?$_POST['a']:(!empty($_GET['a'])?$_GET['a']:'__empty');
        unset($_POST['a'],$_GET['a']);
        return str_replace(array("`","'",".","\\",'|','select','update','insert','alter','eval'),'',strtolower(trim($action)));
	}

	/**
     * 获得安全的表单数据
     * @access public
     * @param $type 'login'、'register'、'open'
     * @return array
     */
	public static function getInputData($type=null) {
		$InputData 			   	   				=  array();
		switch ($type) {
			case 'login':
				foreach ($_POST as $key => $value) {
					$_POST[$key] 				=  trim($value);
				}
				if(!empty($_POST['u'])) {
					if(Juser_is_mail($_POST['u'])) {
						$InputData['mail'] 	   	=  strtolower($_POST['u']);#数据库仅记录小写的邮箱	
					}
				}
				if(!empty($_POST['p'])) {
					if(Juser_is_password($_POST['p'])) {
						$InputData['password'] 	=  $_POST['p'];
					}
				}
				return $InputData;
			case 'register':
				foreach ($_POST as $key => $value) {
					$_POST[$key] 				=  trim($value);
				}
				#用户昵称设定  禁止使用管理员、作者昵称以及博客名
				if(!empty($_POST['n']) && mb_strlen($_POST['n'],'UTF-8')<16) {
					$fobidName 					=  array_merge(Juser_get_admin_name(),array('admin','administrator','writer','visitor',Option::get('blogname')));
					$UserName 					=  strip_tags($_POST['n']);
					$InputData['name'] 	   		=  str_replace($fobidName,'**',$UserName);
				}else {
					$InputData['name'] 	   		=  '路人乙';
				}
				#注册邮箱不允许使用管理员的邮箱
				if(!empty($_POST['u']) && Juser_is_mail($_POST['u']) && !in_array($_POST['u'],Juser_get_admin_mail())) {
					$InputData['mail'] 	   		=  strtolower($_POST['u']);	#数据库仅记录小写的邮箱				
				}else {
					$InputData['mail'] 	   		=  false;
				}
				if(!empty($_POST['p']) && !empty($_POST['rp']) && $_POST['p']==$_POST['rp'] && Juser_is_password($_POST['p'])) {
					$InputData['password'] 		=  $_POST['p'];
				}else {
					$InputData['password']  	=  false;
				}
				if(!empty($_POST['url']) && Juser_is_url($_POST['url'])) {
					$InputData['url']  			=  rtrim($_POST['url'],'/').'/';
				}
				return $InputData;
			default:
				return false;
				break;
		}
	}
}