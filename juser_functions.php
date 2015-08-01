<?php
/**
 * Juser基础函数库
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-23 10:36:14
 * @version $Id$
 */
if(!defined('EMLOG_ROOT')) {exit('Juser 运行在emlog博客框架下!');}
session_start();#开启session
require 'juser_model.php';
#定义请求方法类型 便于控制器直接调用
defined('REQUEST_METHOD') || define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']);
defined('IS_GET')         || define('IS_GET',REQUEST_METHOD =='GET' ? true : false);
defined('IS_POST')        || define('IS_POST',REQUEST_METHOD =='POST' ? true : false);
defined('IS_AJAX')        || define('IS_AJAX',((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) ? true : false);
/**
 * 会员系统核心操作类
 * @param null
 */
class Juser {
	const Juser_Version = 'Juser1.0';

    private static $JuserModel = '';

	private $_db;#数据库连接实例

	private $_db_prefix = 'juser_data';#数据库表前缀

	private static $_instance;#实例对象句柄

	private $_sql;
	
	private function __construct() {}

	/**
	 * 实例化入口
	 * @return object
	 */
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
     * 获取JuserModel实例
     * @param null
     */
    public static function getJuserModel() {
        if(empty(self::$JuserModel)) {
            self::$JuserModel   =   new JuserModel();
        }
        return self::$JuserModel;
    }

	/**
 	 * 获取Db实例
 	 * @param null
 	 */
	public function getDbInstance() {
		if(!is_null($this->_db)) {
			return $this->_db;
		}
		if(class_exists('mysqli')) {
			$this->_db = MySqlii::getInstance();
		}else{
			$this->_db = MySql::getInstance();
		}
		return $this->_db;
	}

	/**
 	 * 获取juser数据表
 	 * @param null
 	 */
	public function getTable() {
		return DB_PREFIX.$this->_db_prefix;
	}

    /**
     * Juser用户是否登录
     * @param null
     * @return false OR UserInfo(boolean for True)
     */
    public static function isLogin() {
        if(!isset($_COOKIE['JuserCookie'])) {
            return false;
        }
        $Cookie = explode('|',$_COOKIE['JuserCookie']);
        if (count($Cookie) != 3) {
            return false;
        }
        list($juser_id,$expiration,$hmac) = $Cookie;
        if (!empty($expiration) && $expiration < time()) {
            return false;
        }
        #依据cookie中的明文加密后对比密文是否一致
        $key      =  hash_hmac('md5',$juser_id.'|'.$expiration,AUTH_KEY);
        $hash     =  hash_hmac('md5',$juser_id.'|'.$expiration,$key);
        if($hmac != $hash) {
            return false;
        }
        $UserInfo = self::getUserInfoByID($juser_id);
        if (!$UserInfo) {
            return false;
        }
        return $UserInfo;
    }
    #通过id查找juser用户
    public static function getUserInfoByID($juser_id) {
        if(empty($juser_id) || !ctype_digit((string)$juser_id)) { return false; }
        if(empty(self::$JuserModel)) {
            self::$JuserModel   =   new JuserModel();
        }
        return self::$JuserModel->field(true)->where(array('id'=>$juser_id))->find();
    }
    #通过邮箱查找juser用户
    public static function getUserInfoByMail($mail) {
        if(empty($juser_id) || !Juser_is_mail($mail)) { return false; }
        if(empty(self::$JuserModel)) {
            self::$JuserModel   =   new JuserModel();
        }
        return self::$JuserModel->field(true)->where(array('mail'=>$mail))->find();
    }
    #通过开放平台openid查找用户
    public static function getUserInfoByOpenID($type,$openid) {
        if(empty($type) || empty($openid)) { return false; }
        $key    = strtolower($type).'_openid';
        if(empty(self::$JuserModel)) {
            self::$JuserModel   =   new JuserModel();
        }
        #检查字段是否存在
        $Fields = self::$JuserModel->getDbFields();
        if(!in_array($key,$Fields)) {
            throw new Exception('不存在的开放平台字段，请添加juser_data表字段');
        }
        return self::$JuserModel->field(true)->where(array($key=>$openid))->find();
    }
    /**
     * 对明文密码进行加密::与em内置明文密码加密方法一致 一个密码对应N个hash值，对比hash值即可知道密码是否一致
     * @param string $password 明文密码
     * @return hash string
     */
    public static function genPassword($password) {
        $PHPASS   = new PasswordHash(8, true);
        return $PHPASS->HashPassword($password);
    }

    /**
     * 对比明文 密码与数据库中保存的hash字符串是否一致
     * @param string $password 明文密码
     * @param string $hash 数据库保存的hash值||password字段内容
     * @return boolean
     */
    public static function checkPassword($password,$hash) {
        global $em_hasher;
        if(empty($em_hasher)) {
            $em_hasher = new PasswordHash(8, true);
        }
        return $em_hasher->CheckPassword($password,$hash);
    }

    /**
     * 退出登录
     * @param null
     * @return void
     */
    public static function setAuthOut() {
        $expire    =  time()-3600 * 24 * 30 * 12;
        setcookie('JuserCookie','deleted',$expire,'/');
    }

    /**
     * 给予登录状态的cookie
     * @param int $juser_id
     * @param boolean $is_expire cookie是否永久 默认1年
     * @return void
     */
    public static function setAuthCookie($juser_id,$is_expire=true) {
        if($is_expire){
            $is_expire  = time() + 3600 * 24 * 30 * 12;
        }else {
            $is_expire  = null;
        }
        $key    =  hash_hmac('md5',$juser_id.'|'.$is_expire,AUTH_KEY);
        $hash   =  hash_hmac('md5',$juser_id.'|'.$is_expire,$key);
        $value  =  $juser_id.'|'.$is_expire.'|'.$hash;
        // $_SESSION['Juser_ID']  =  $juser_id;
        setcookie('JuserCookie',$value,$is_expire,'/');
    }

    #Juser效验
	public static function checkJuser() {
		$params = stream_context_create(array(
				'http'=>array(
						'method'  => 'GET',
						'timeout' => 15
						)
				)
		);
		return file_get_contents('http://www.jjonline.cn/report.php?version='.self::Juser_Version.'&url='.BLOG_URL,false,$params);
	}
}
/*获取管理员、投稿作者的昵称和用户名--juser禁用这些昵称进行注册*/
function Juser_get_admin_name() {
    global $CACHE;
    $user      = $CACHE->readCache('user');
    $nickName  = array();
    foreach ($user as $key => $value) {
       if(!empty($value['name'])) {
            $nickName[]  = $value['name'];
       }       
    }
    return $nickName;
}
function Juser_get_admin_mail() {
    global $CACHE;
    $user      = $CACHE->readCache('user');
    $mail      = array();
    foreach ($user as $key => $value) {
       if(!empty($value['mail'])) {
            $mail[]  = $value['mail'];
       }       
    }
    return $mail;
}
/**
 * 获取随机字符串
 * @param $len 需要的字符串长度
 * @return boolean
 */
function Juser_randString($len=8) {
    #去掉了容易混淆的字符oOLl和数字01
    $chars   =   'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    $chars   =   str_shuffle($chars);
    $str     =   substr($chars,0,$len);
    return $str;
}
/**
 * 判断参数字符串是否为密码格式（必须包含数字、字母的6至18位密码串）
 * @param $password 需要被判断的字符串
 * @return boolean
 */
function Juser_is_password($password) {
    if(strlen($password)>18 || strlen($password)<6) {return false;}
    return (preg_match('/\d{1,16}/',$password)===1 && preg_match('/[a-zA-Z]{1,16}/',$password)===1 && strlen($password)<=16);
}
/**
 * 判断参数字符串是否为邮箱格式
 * @param $mail 需要被判断的字符串
 * @return boolean
 */
function Juser_is_mail($mail) {
    return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',$mail)===1 && strlen($mail)<128;
}
/**
 * 判断参数字符串是否为天朝手机号
 * @param $phone 需要被判断的字符串或数字
 * @return boolean
 */
function Juser_is_phone($phone) {
    return preg_match('/^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$|^170\d{8}$/',$phone)===1;
}
/**
 * 判断参数字符串是否为数字账号[discuz、phpwind、qq、小米等数字账号格式判断] 4至11位的正整数
 * @param $uid 需要被判断的字符串或数字
 * @return boolean
 */
function Juser_is_uid($uid) {
    //is_numeric ctype_digit的参数必须是字符串格式的数字才会返回true
    //不用正则的判断方法 return strlen($uid)>=4 && strlen($uid)<=11 && ctype_digit((string)$uid); 
    return preg_match('/^[1-9]\d{3,10}$/',$uid)===1;
}
function Juser_is_url($url) {
    return !!preg_match('/^http[s]?:\/\/(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z_!~*\'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(:[0-9]{1,4})?((\/\?)|(\/[0-9a-zA-Z_!~\*\'\(\)\.;\?:@&=\+\$,%#-\/]*)?)$/i',$url);
}
/**
 * 判断参数字符串是否为天朝身份证号
 * @param $uid 需要被判断的字符串或数字
 * @return mixed false 或 array[有内容的array boolean为真]
 */
function Juser_is_citizen_id($id) {
    //长度效验  18位身份证中的X为大写
    $id  = strtoupper($id);
    if(!(preg_match('/^\d{17}(\d|X)$/',$id) || preg_match('/^\d{15}$/',$id))) {
      return false;
    }
    //15位老号码转换为18位 并转换成字符串
    $Wi          = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1); 
    $Ai          = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); 
    $cardNoSum   = 0;
    if(strlen($id)==16) {
      $id        = substr(0, 6).'19'.substr(6, 9); 
      for($i = 0; $i < 17; $i++) {
          $cardNoSum += substr($id,$i,1) * $Wi[$i];
      }  
      $seq       = $cardNoSum % 11; 
      $id        = $id.$Ai[$seq];
    }
    //效验18位身份证最后一位字符的合法性
    $cardNoSum   = 0;
    $id17        = substr($id,0,17);
    $lastString  = substr($id,17,1);
    for($i = 0; $i < 17; $i++) {
        $cardNoSum += substr($id,$i,1) * $Wi[$i];
    }  
    $seq         = $cardNoSum % 11;
    $realString  = $Ai[$seq];
    if($lastString!=$realString) {return false;}
    //地域效验
    $oCity       =  array(11=>"北京",12=>"天津",13=>"河北",14=>"山西",15=>"内蒙古",21=>"辽宁",22=>"吉林",23=>"黑龙江",31=>"上海",32=>"江苏",33=>"浙江",34=>"安徽",35=>"福建",36=>"江西",37=>"山东",41=>"河南",42=>"湖北",43=>"湖南",44=>"广东",45=>"广西",46=>"海南",50=>"重庆",51=>"四川",52=>"贵州",53=>"云南",54=>"西藏",61=>"陕西",62=>"甘肃",63=>"青海",64=>"宁夏",65=>"新疆",71=>"台湾",81=>"香港",82=>"澳门",91=>"国外");
    $City        = substr($id, 0, 2);
    $BirthYear   = substr($id, 6, 4);
    $BirthMonth  = substr($id, 10, 2);
    $BirthDay    = substr($id, 12, 2);
    $Sex         = substr($id, 16,1) % 2 ;//男1 女0
    //$Sexcn       = $Sex?'男':'女';
    //地域验证
    if(is_null($oCity[$City])) {return false;}
    //出生日期效验
    if($BirthYear>2078 || $BirthYear<1900) {return false;}
    $RealDate    = strtotime($BirthYear.'-'.$BirthMonth.'-'.$BirthDay);
    if(date('Y',$RealDate)!=$BirthYear || date('m',$RealDate)!=$BirthMonth || date('d',$RealDate)!=$BirthDay) {
      return false;
    }
    return array('id'=>$id,'location'=>$oCity[$City],'Y'=>$BirthYear,'m'=>$BirthMonth,'d'=>$BirthDay,'sex'=>$Sex);
}