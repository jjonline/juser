<?php
/**
 * Juser开放平台登录抽象类
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-29 13:28:03
 * @version $Id$
 */
abstract class JuserOpen {
    protected $AppKey 			 = 	'';#key
    protected $AppSecret 		 =	'';#secret
    protected $OpenID			 =	'';#获取的唯一标识符OpenID
    protected $AccessToken		 =	'';#获取到的授权token
    protected $ResponseType 	 = 	'code';#response_type=>code
    protected $GrantType    	 = 	'authorization_code';#ant_type=>authorization_code
    protected $Callback 		 = 	'';#回调域url
    protected $Code 			 =  '';#初步授权后的code值
    protected $ApiBase 		     = 	'';#api基础构成部分
    protected $GetRequestCodeURL =  '';#获取初步授权的api
	protected $GetAccessTokenURL =  '';#获取token的api
	protected $GetOpenIDURL 	 =  '';#获取Openid的api
	protected $GetUserInfoURL 	 =	'';#获取用户信息的api
	
	/**
	 * 构造方法，配置应用信息
	 * @param string $Type {qq OR SINA}
	 * @param string $AppKey
	 * @param string $AppSecret
	 */
    public function __construct($AppKey,$AppSecret,$Callback){
        $this->AppKey		=	$AppKey;
        $this->AppSecret 	=	$AppSecret;
        $this->Callback 	=	$Callback;
    }

	/**
     * 取得Oauth实例
     * @static
     * @param $Type open类型
     * @param $AppKey 
     * @param $AppSecret 
     * @param $Callback 
     * @return object
     */
    public static function getInstance($Type,$AppKey,$AppSecret,$Callback) {
    	$name = 'Juser'.ucfirst(strtolower($Type));
    	require_once "{$name}.class.php";
    	#5.3可以写法为：new $name
    	if($name === 'JuserQq') {
    		return new JuserQq($AppKey,$AppSecret,$Callback);
    	}else {
    		return new JuserSina($AppKey,$AppSecret,$Callback);
    	}    	
    }

 	/**
	 * 跳转让用户登录授权
	 * @param null
	 */   
    public function getRedirectUrl() {
    	$_SESSION['state'] =  uniqid('Juser');  	  
		$params = array(
			'client_id'     => $this->AppKey,
			'redirect_uri'  => $this->Callback,
			'response_type' => $this->ResponseType,
			'state'			=> $_SESSION['state']
		);
		return $this->GetRequestCodeURL.'?'.http_build_query($params);
    }

	/**
	 * 发送HTTP请求方法，仅支持CURL发送请求
	 * @param  string $url    请求URL
	 * @param  array  $params 请求参数
	 * @param  string $method 请求方法GET/POST
	 * @return array  $data   响应数据
	 */
    protected function Http($url, $params, $method='GET', $header=array(), $multi=false){
    	$opts = array(
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HTTPHEADER     => $header
		);
		switch(strtoupper($method)){
			case 'GET':
				$opts[CURLOPT_URL] 		  = 	$url . '?' . http_build_query($params);
				break;
			case 'POST':
				$params 		    	  = 	$multi ? $params : http_build_query($params);
				$opts[CURLOPT_URL]  	  = 	$url;
				$opts[CURLOPT_POST] 	  = 	1;
				$opts[CURLOPT_POSTFIELDS] = 	$params;
				break;
			default:
				throw new Exception('请求方式有误！');
		}
		$ch 	= curl_init();
		curl_setopt_array($ch, $opts);
		$data   = curl_exec($ch);
		$error  = curl_error($ch);
		curl_close($ch);
		if($error) throw new Exception('cURL请求发生错误：'.$error);
		return  $data;
    }

	/**
	 * 抽象方法 子类实现 注意调用顺序  先token再openid再用户信息
	 */    
    abstract function getAccessToken($Code=null);#获取accessToken
    abstract function getOpenID($AccessToken=null);#获取OpenID 
    abstract function getUserInfo($AccessToken=null);#获取用户信息
}