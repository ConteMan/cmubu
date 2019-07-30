<?php

namespace Cmubu;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use \Exception;

class CmubuQuery
{
    private $client = null;
    private $cookies = null;
    private $username = '';
    private $password = '';
    private $baseUrl = 'https://mubu.com';
    private $baseOption = [];

    const CLIENT_OPTIONS = [];

    public function __construct($config)
    {
        if(!$config['username']  || !$config['password']) {
            throw new Exception('need username and password');
        }
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->cookies = isset($config['cookies']) ? $config['cookies'] : [];
        $this->baseOption = ['base_uri' => $this->baseUrl, 'cookies' => true, 'verify' => false];
        if($this->cookies){
            $this->baseOption['cookies'] = new CookieJar(true, $this->cookies);
        }
        $this->client = new GuzzleClient($this->baseOption);
        if(!$this->cookies){
            $this->login();
        }
    }

    /**
     * get cookies
     */
    public function cookies()
    {
        return $this->cookies;
    }

    /**
     * login
     *
     * @param $username
     * @param $password
     *
     * @return mixed
     */
    public function login()
    {
        $method   = 'POST';
        $uri      = '/api/login/submit';
        $param    = [
            'query' => [
                'phone'    => $this->username,
                'password' => $this->password,
                'remember' => 'true',
            ]
        ];
        $response = $this->requestDeal($method, $uri, $param);
        if($response['code'] != 0) {
            throw new Exception('login failed: '.$response['code'].','.$response['msg']);
        }

        $cookieJar = $this->client->getConfig('cookies');

        $this->baseOption['cookies'] = $cookieJar;
        $this->client = new GuzzleClient($this->baseOption);

        $cookieArr = $cookieJar->toArray();
        $this->cookies = $cookieArr;
        return $cookieArr;
    }

    /**
     * doc list
     *
     * @return mixed
     */
    public function docList($folderId='', $sort='time', $keywords='', $source='')
    {
        $method   = 'POST';
        $uri      = '/api/list/get';
        $param    = [
            'query' => [
                'folderId' => $folderId,
                'sort'     => $sort,
                'keywords' => $keywords,
                'source'   => $source,
            ]
        ];
        return $this->requestDeal($method, $uri, $param);
    }

    /**
     * get doc content
     *
     * @param $docId
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function docContent($docId)
    {
        $method   = 'POST';
        $uri      = '/api/document/get';
        $param    = [
            'query' => [
                'docId'   => $docId,
            ]
        ];
        return $this->requestDeal($method, $uri, $param);
    }

    /**
     * request deal,retry if not login
     *
     * @param $method
     * @param $uri
     * @param $param
     * @param $trySign
     *
     * @return mixed
     */
    public function requestDeal($method, $uri,  $param, $trySign=0)
    {
        try{
            $response = $this->client->request($method, $uri, $param);
            $response = json_decode($response->getBody(), true);
            if(!isset($response['code'])){
                throw new Exception('request failed');
            }
            switch($response['code']){
                case 2: //need login
                    if($trySign < 3){
                        $this->login();
                        $trySign++;
                        $response = $this->requestDeal($method, $uri, $param, $trySign);
                    }
                    break;
                default:
                    break;
            }
            return $response;
        } catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}
