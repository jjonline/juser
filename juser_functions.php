<?php
/**
 * Juser基础函数库
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-23 10:36:14
 * @version $Id$
 */
if(!defined('EMLOG_ROOT')) {exit('Juser 运行在emlog博客框架下!');}
require 'juser_model.php';
/**
 * 会员系统核心操作类
 * @param null
 */
class Juser {
	const Juser_Version = '1.0';

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

	/**
 	 * 显示登录界面->多种待选方式::覆层或页面，默认覆层
 	 * @param boolen $type = false
 	 */
	public function showLogin($type = false) {

	}
}

if(!function_exists('dump')) {
/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}
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
    return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',$mail)===1;
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