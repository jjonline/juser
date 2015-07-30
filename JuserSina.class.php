<?php
/**
 * Juser 微博开放平台登录
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-29 13:28:03
 * @version $Id$
 */
class JuserSina extends JuserOpen{
	protected $GetRequestCodeURL = 'https://api.weibo.com/oauth2/authorize';
	protected $GetAccessTokenURL = 'https://api.weibo.com/oauth2/access_token';
	protected $GetUserInfoURL    = 'https://api.weibo.com/2/users/show.json';
	protected $ApiBase 			 = 'https://api.weibo.com/2/';
		
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
		$data = json_decode($this->Http($this->GetAccessTokenURL,$params,'POST',array(),true),true);
		if(isset($data['uid']) && isset($data['access_token'])) {
			$this->OpenID 		=  $data['uid'];
			$this->AccessToken  =  $data['access_token'];
			return $this->AccessToken;
		}
		return false;
	}
	#获取openid
	public function getOpenID($accessToken=null) {
		return $this->OpenID;
	}

	#获取用户信息 主要是figure
	public function getUserInfo($accessToken=null){
		$params = array(
			'access_token'		=> $accessToken?$accessToken:$this->AccessToken,
			'uid' 				=> $this->OpenID,
		);
		$data = json_decode($this->Http($this->GetAccessTokenURL,$params,'GET',array(),true),true);
		if(!isset($data['error_code'])) {
			$UserInfo 					=  array();
			$UserInfo['sina_name'] 		=  $data['screen_name'];
			$UserInfo['sina_openid'] 	=  $this->OpenID;
			$UserInfo['sina_token']     =  $this->AccessToken;
			$UserInfo['sina_figure']    =  $data['profile_image_url'];
			if($data['gender']!='n') {
				$UserInfo['sex']		=  $data['gender'];#微博平台返回的性别不是未知 一切返回
			}
			return $UserInfo;
		}
		return false;
	}
}