<?php

namespace app\components;

use yii\base\Object;

class Curl extends Object
{
    private $ssl_verifypeer;
    private $ssl_verifyhost;
    private $header;
    private $timeout;
    private $httpheader;
    private $returntransfer;
    private $useragent;
    private $userpwd;
    private $url;
    private $ch;

    public function __construct($url, $config = [])
    {
        $this->url = $url;
        parent::__construct($config);
    }

    public function init()
    {
        $this->ch = curl_init($this->url);

        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $this->ssl_verifyhost);
        curl_setopt($this->ch, CURLOPT_HEADER, $this->header);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->httpheader);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, $this->returntransfer);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($this->ch, CURLOPT_USERPWD, $this->userpwd);

        parent::init();
    }

    public function execute()
    {
        $result = curl_exec($this->ch);
        curl_close($this->ch);

        return $result;
    }
}