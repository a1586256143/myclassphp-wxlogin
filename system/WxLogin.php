<?php
namespace system;
use EasyWeChat\Factory;

class WxLogin {
	/**
	 * @var string AppId
	 */
	private $appid = '';
	/**
	 * @var string AppSecret
	 */
	private $appSecret = '';
	/**
	 * @var string 授权回调地址
	 */
	private $callback = '';
	/**
	 * @var string 授权方式
	 */
	private $scope = 'snsapi_userinfo';
	/**
	 * @var \EasyWeChat\OfficialAccount\Application $app
	 */
	protected static $app = '';
	/**
	 * @var string 错误信息
	 */
	protected $error = '';

	/**
	 * 初始化
	 * @param string $appid
	 * @param string $appSecret
	 */
	public function __construct($appid = '' , $appSecret = '') {
		$appid && $this->appid = $appid;
		$appSecret && $this->appSecret = $appSecret;
		$this->argsCheck();
		$this->getEasyWeChatApp();
	}

	/**
	 * 设置回调地址
	 * @param $callback
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午4:15
	 */
	public function setCallbackUri($callback){
		$this->callback = $callback;
		self::$app = '';
		$this->getEasyWeChatApp();
	}

	/**
	 * 设置授权方式
	 * @param $scope
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午4:15
	 */
	public function setScopes($scope){
		$this->scope = $scope;
		self::$app = '';
		$this->getEasyWeChatApp();
	}

	/**
	 * 发起授权请求
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午3:24
	 */
	public function auth(){
		if (!$this->callback){
			throw new \InvalidArgumentException("callbackUri not empty!");
		}
		return self::$app->oauth->scopes([$this->scope])->redirect();
	}

	/**
	 * 用code换openid
	 * @param string $code
	 * @return mixed
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午3:48
	 */
	public function codeToOpenid($code = ''){
		return $this->runApi(function() use ($code){
			$info = self::$app->oauth->getAccessToken($code);
			return $info['openid'] ?? '';
		});
	}

	/**
	 * 获取授权的用户信息
	 * @param string $openid
	 * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Overtrue\Socialite\User|\Psr\Http\Message\ResponseInterface|string
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午3:50
	 */
	public function getUser($openid = ''){
		return $this->runApi(function() use ($openid){
			if ($openid){
				return self::$app->user->get($openid);
			}
			return self::$app->oauth->user();
		} , false);
	}

	/**
	 * 根据Code拿到用户信息
	 * @param string $code
	 * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Overtrue\Socialite\User|\Psr\Http\Message\ResponseInterface|string
	 * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午3:52
	 */
	public function getUserInfo($code = ''){
		$openid = $this->codeToOpenid($code);
		if (!$openid){
			$this->error = "openid not empty!";
			return false;
		}
		return $this->getUser($openid);
	}

	/**
	 * 获取AccessToken
	 * @return string
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午3:12
	 */
	public function getAccessToken(){
		return $this->runApi(function(){
			$token = self::$app->access_token->getToken();
			return $token['access_token'];
		});
	}

	/**
	 * 获取App
	 * @return \EasyWeChat\OfficialAccount\Application|string
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午3:17
	 */
	protected function getEasyWeChatApp(){
		if (!self::$app){
			self::$app = Factory::officialAccount($this->getEasyWeChatConfig());
		}
		return self::$app;
	}

	/**
	 * 获取配置信息
	 * @return array
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午3:15
	 */
	protected function getEasyWeChatConfig(){
		return [
			'app_id' => $this->appid ,
			'secret' => $this->appSecret ,
			'response_type' => 'array',
			'oauth' => [
				'scopes'   => [$this->scope],
				'callback' => $this->callback,
			],
			'http' => [
				'max_retries' => 1,
				'retry_delay' => 500,
				'timeout' => 5.0,
			],
		];
	}

	/**
	 * 获取错误信息
	 * @return string
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午4:07
	 */
	public function getError(){
		return $this->error;
	}

	/**
	 * 校验Appid和AppSecret
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午3:12
	 */
	protected function argsCheck(){
		if (!$this->appid){
			throw new \InvalidArgumentException("appid not empty!");
		}
		if (!$this->appSecret){
			throw new \InvalidArgumentException("appSecret not empty!");
		}
	}

	/**
	 * 执行Api
	 * @param \Closure $callback
	 * @param string $default
	 * @return mixed|string
	 * @author Colin <amcolin@126.com>
	 * @date 2021-07-07 下午4:10
	 */
	protected function runApi(\Closure $callback , $default = ''){
		try {
			return $callback();
		}catch (\Exception $e){
			$this->error = $e->getMessage();
		}
		return $default;
	}
}