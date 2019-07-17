<?php

namespace Cmubu;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use \Exception;

class CmubuQueryService
{
    private $redis = '';
    private $client = null;
    private $prefix = '';
    private $cookieKey = '';
    private $cookies = [];

    const CLIENT_OPTIONS = [];

    public function __construct($config)
    {
        $userId   = $config['username'];
        $password = $config['password'];
        if(!$userId || !$password) {
            throw new Exception('need username and password');
        }
        //缓存
        $this->redis   = Redis::connection(config('cmubu.redis_config'));
        $this->prefix  = config('cmubu.cache_prefix');
        $this->cookieKey = $this->prefix . 'cookie';
        //请求配置
        $baseUrl      = 'https://mubu.com';
        $this->client = new GuzzleClient(['base_uri' => $baseUrl, 'cookies' => true]);
        //登录校验
        $this->loginCheck($userId, $password);
    }

    /**
     * 登录校验，无效则登录
     * @param $userId
     * @param $password
     * @return bool
     */
    private function loginCheck($userId, $password)
    {
        $cookies = $this->cookie();
        if($cookies) {
            $checkRes = $this->docList();
            if(isset($checkRes['code']) && $checkRes['code'] == 0) {
                $this->cookies = $cookies;
                return true;
            }
        }
        return $this->login($userId, $password);
    }

    /**
     * 登录
     * @param $userId
     * @param $password
     * @return mixed
     */
    public function login($userId, $password)
    {
        $method   = 'POST';
        $uri      = '/api/login/submit';
        $param    = [
            'query' => [
                'phone'    => $userId,
                'password' => $password,
                'remember' => 'true',
            ]
        ];
        $response = $this->client->request($method, $uri, $param);
        $response = json_decode($response->getBody(), true);
        if($response['code'] != 0) {
            throw new Exception('登录失败，请检查');
        }

        $cookieJar = $this->client->getConfig('cookies');
        $cookieArr = $cookieJar->toArray();
        $cookieJson = json_encode($cookieArr);

        $this->redis->set($this->cookieKey, $cookieJson);
        $this->cookies = $cookieArr;
        return $cookieArr;
    }

    /**
     * 缓存获取 cookie 信息
     * @return mixed
     */
    public function cookie()
    {
        $cookies = $this->redis->get($this->cookieKey);
        return $cookies ? json_decode($cookies, true) : [];
    }

    /**
     * 接口获取文档列表
     * @return mixed
     */
    public function docList($folderId='', $sort='time', $keywords='', $source='')
    {
        $cookies =  $this->cookies;
        $cookie_jar = new CookieJar(true,$cookies);

        $method   = 'POST';
        $uri      = '/api/list/get';
        $param    = [
            'query' => [
                'cookies'  => $cookie_jar,
                'folderId' => $folderId,
                'sort'     => $sort,
                'keywords' => $keywords,
                'source'   => $source,
            ]
        ];
        $response = $this->client->request($method, $uri, $param);
        return json_decode($response->getBody(), true);
    }

    /**
     * 获取文档具体内容
     * @param $docId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function docContent($docId)
    {
        $cookies =  $this->cookies;
        $cookie_jar = new CookieJar(true,$cookies);

        $method   = 'POST';
        $uri      = '/api/document/get';
        $param    = [
            'query' => [
                'cookies' => $cookie_jar,
                'docId'   => $docId,
            ]
        ];
        $response = $this->client->request($method, $uri, $param);
        return json_decode($response->getBody(), true);
    }
}
