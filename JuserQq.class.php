<?php
/**
 * Juser qq开放平台登录
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-29 13:28:03
 * @version $Id$
 */
class JuserQq extends JuserOpen{
	protected $GetRequestCodeURL = 'https://graph.qq.com/oauth2.0/authorize';
	protected $GetAccessTokenURL = 'https://graph.qq.com/oauth2.0/token';
	protected $GetOpenIDURL  	 = 'https://graph.qq.com/oauth2.0/me';
	protected $GetUserInfoURL    = 'https://graph.qq.com/user/get_user_info';
	#protected $Authorize 		 = 'scope=get_user_info';#默认不传参则get_user_info
	protected $ApiBase 		     = 'https://graph.qq.com/';
	
	/**
	 * 获取用户授权后的accessToken
	 * @param string $code 用户授权后的临时code值
	 * @return string
	 */
	public function getAccessToken($code=null) {
		$params = array(
			'client_id'			=> $this->AppKey,
			'client_secret' 	=> $this->AppSecret,
			'grant_type' 		=> $this->GrantType,
			'code'				=> empty($code)?$this->Code:$code,
			'redirect_uri'		=> $this->Callback,
		);
		$result = $this->Http($this->GetAccessTokenURL,$params,'POST',array(),true);
		parse_str($result, $data);
		if(isset($data['access_token']) && isset($data['expires_in'])){
			$this->AccessToken  = $data['access_token'];
			return $this->AccessToken;
		}
		return false;
	}
	#获取openid
	public function getOpenID($accessToken=null) {
		$result = $this->Http($this->GetOpenIDURL,array('access_token'=>$accessToken?$accessToken:$this->AccessToken),'GET',array(),true);
		$data   = json_decode(trim(substr($result, 9), " );\n"), true);
		if(isset($data['openid'])) {
			$this->OpenID   =  $data['openid'];
			return $this->OpenID;
		}
		return false;
	}
	#获取用户信息 主要是figure
	public function getUserInfo($accessToken=null){
		$params = array(
			'access_token'		=> $this->AccessToken,
			'oauth_consumer_key'=> $this->AppKey,
			'openid'			=> $this->OpenID,
		);
		$data = json_decode($this->Http($this->GetAccessTokenURL,$params,'GET',array(),true),true);
		if(!isset($data['ret']) && $data['ret']=='0') {
			$UserInfo 					=  array();
			$UserInfo['qq_name'] 		=  $data['nickname'];
			$UserInfo['qq_openid'] 		=  $this->OpenID;
			$UserInfo['qq_token']     	=  $this->AccessToken;
			$UserInfo['qq_figure']    	=  $data['figureurl_qq_1'];
			if($data['gender']!='n') {
				$UserInfo['sex']		=  $data['gender'];#微博平台返回的性别不是未知 一切返回
			}
			return $UserInfo;
		}
		return false;
	}
}