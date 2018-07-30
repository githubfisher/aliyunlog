<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

/**
 * Class AliyunLogSimpleLogger
 * a wrapper for submit log message to server, to avoid post log frequently, using a internal cache for messages
 * When the count of messages reach the cache size, SimpleLogger will post the messages in bulk, and reset the cache accordingly.
 */

namespace Aliyun\Log;

use \Exception;

class AliyunLogSimpleLogger{

    /**
     * internal cache for log messages
     * @var array
     */
    private $logItems = [];

    /**
     * max size of cached messages
     * @var int
     */
    private $maxCacheLog;

    /**
     * log topic field
     * @var
     */
    private $topic;

    /**
     * max time before logger post the cached messages
     * @var int
     */
    private $maxWaitTime;

    /**
     * previous time for posting log messages
     * @var int
     */
    private $previousLogTime;

    /**
     * max storage size for cached messages
     * @var int
     */
    private $maxCacheBytes;

    /**
     * messages storage size for cached messages
     * @var int
     */
    private $cacheBytes;

    /**
     * log client which was wrappered by this logger
     * @var log
     */
    private $client;

    /**
     * log project name
     * @var the
     */
    private $project;

    /**
     * logstore name
     * @var the
     */
    private $logstore;

    /**
     * AliyunLogModelsLogBatch constructor.
     * @param $client log client
     * @param $project the corresponding project
     * @param $logstore the logstore
     * @param $topic
     * @param null $maxCacheLog max log items limitation, by default it's 100
     * @param null $maxWaitTime max thread waiting time, bydefault it's 5 seconds
     */
    public function construct($client, $project, $logstore, $topic, $maxCacheLog = null, $maxWaitTime = null, $maxCacheBytes = null)
    {
        if(NULL === $maxCacheLog || !isinteger($maxCacheLog)){
            $this->maxCacheLog = 100;
        }else{
            $this->maxCacheLog = $maxCacheLog;
        }

        if(NULL === $maxCacheBytes || !isinteger($maxCacheBytes)){
            $this->maxCacheBytes = 256 * 1024;
        }else{
            $this->maxCacheBytes = $maxCacheBytes;
        }

        if(NULL === $maxWaitTime || !isinteger($maxWaitTime)){
            $this->maxWaitTime = 5;
        }else{
            $this->maxWaitTime = $maxWaitTime;
        }
        if($client == null || $project == null || $logstore == null){
            throw new Exception('the input parameter is invalid! create SimpleLogger failed!');
        }
        $this->client = $client;
        $this->project = $project;
        $this->logstore = $logstore;
        $this->topic = $topic;
        $this->previousLogTime = time();
        $this->cacheBytes = 0;
    }

    /**
     * add logItem to cached array, and post the cached messages when cache reach the limitation
     * @param $curtime
     * @param $logItem
     */
    private function logItem($curtime, $logItem){
        array_push($this->logItems, $logItem);
        if ($curtime - $this->previousLogTime >= $this->maxWaitTime || sizeof($this->logItems) >= $this->maxCacheLog
            || $this->cacheBytes >= $this->maxCacheBytes)
        {
            $this->logBatch($this->logItems, $this->topic);
            $this->logItems = [];
            $this->previousLogTime = time();
            $this->cacheBytes = 0;
        }
    }

    /**
     * log single string message
     * @param LogLevel $logLevel
     * @param $logMessage
     * @throws Exception
     */
    private function logSingleMessage(LogLevel $logLevel, $logMessage){
        if(is_array($logMessage)){
            throw new Exception('array is not supported in this function, please use logArrayMessage!');
        }
        $curtime = time();
        $contents = array( // key-value pair
            'time'=>date('m/d/Y h:i:s a', $curtime),
            'loglevel'=> LogLevel::getLevelStr($logLevel),
            'msg'=>$logMessage
        );
        $this->cacheBytes += strlen($logMessage) + 32;
        $logItem = new AliyunLogModelsLogItem();
        $logItem->setTime($curtime);
        $logItem->setContents($contents);
        $this->logItem($curtime, $logItem);
    }

    /**
     * log array message
     * @param LogLevel $logLevel
     * @param $logMessage
     * @throws Exception
     */
    private function logArrayMessage(LogLevel $logLevel, $logMessage){
        if(!is_array($logMessage)){
            throw new Exception('input message is not array, please use logSingleMessage!');
        }
        $curtime = time();
        $contents = array( // key-value pair
            'time'=>date('m/d/Y h:i:s a', $curtime)
        );
        $contents['logLevel'] = LogLevel::getLevelStr($logLevel);
        foreach ($logMessage as $key => $value)
        {
            $contents[$key] = $value;
            $this->cacheBytes += strlen($key) + strlen($value);
        }
        $this->cacheBytes += 32;
        $logItem = new AliyunLogModelsLogItem();
        $logItem->setTime($curtime);
        $logItem->setContents($contents);
        $this->logItem($curtime, $logItem);
    }

    /**
     * submit string log message with info level
     * @param $logMessage
     */
    public function info( $logMessage){
        $logLevel = LogLevel::getLevelInfo();
        $this->logSingleMessage($logLevel, $logMessage);
    }

    /**
     * submit string log message with debug level
     * @param $logMessage
     */
    public function debug($logMessage){
        $logLevel = LogLevel::getLevelDebug();
        $this->logSingleMessage($logLevel, $logMessage);
    }

    /**
     * submit string log message with warn level
     * @param $logMessage
     */
    public function warn($logMessage){
        $logLevel = LogLevel::getLevelWarn();
        $this->logSingleMessage($logLevel, $logMessage);
    }

    /**
     * submit string log message with error level
     * @param $logMessage
     */
    public function error($logMessage){
        $logLevel = LogLevel::getLevelError();
        $this->logSingleMessage($logLevel, $logMessage);
    }

    /**
     * submit array log message with info level
     * @param $logMessage
     */
    public function infoArray($logMessage){
        $logLevel = LogLevel::getLevelInfo();
        $this->logArrayMessage($logLevel, $logMessage);
    }

    /**
     * submit array log message with debug level
     * @param $logMessage
     */
    public function debugArray($logMessage){
        $logLevel = LogLevel::getLevelDebug();
        $this->logArrayMessage($logLevel, $logMessage);
    }

    /**
     * submit array log message with warn level
     * @param $logMessage
     */
    public function warnArray($logMessage){
        $logLevel = LogLevel::getLevelWarn();
        $this->logArrayMessage($logLevel, $logMessage);
    }

    /**
     * submit array log message with error level
     * @param $logMessage
     */
    public function errorArray( $logMessage){
        $logLevel = LogLevel::getLevelError();
        $this->logArrayMessage($logLevel, $logMessage);
    }

    /**
     * get current machine IP
     * @return string
     */
    private function getLocalIp(){
        $localip = getHostByName(php_uname('n'));
        if(strlen($localip) == 0){
            $localip = getHostByName(getHostName());
        }
        return $localip;
    }

    /**
     * submit log messages in bulk
     * @param $logItems
     * @param $topic
     */
    private function logBatch($logItems, $topic){
        $ip = $this->getLocalIp();
        $request = new AliyunLogModelsPutLogsRequest($this->project, $this->logstore,
            $topic, $ip, $logItems);
        $errorexception = NULL;
        for($i = 0 ;  $i < 3 ; $i++)
        {
            try{
                $response = $this->client->putLogs($request);
                return;
            } catch (AliyunLogException $ex) {
                $errorexception = $ex;
            } catch (Exception $ex) {
                var_dump($ex);
                $errorexception = $ex;
            }
        }
        if ($errorexception != NULL)
        {
            var_dump($errorexception);
        }
    }

    /**
     * manually flush all cached log to log server
     */
    public function logFlush(){
        if(sizeof($this->logItems) > 0){
            $this->logBatch($this->logItems, $this->topic);
            $this->logItems = [];
            $this->previousLogTime= time();
            $this->cacheBytes = 0;
        }
    }

    function destruct() {
        if(sizeof($this->logItems) > 0){
            $this->logBatch($this->logItems, $this->topic);
        }
    }
}
