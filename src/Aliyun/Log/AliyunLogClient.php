<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

/**
 * AliyunLogClient class is the main class in the SDK. It can be used to
 * communicate with LOG server to put/get data.
 *
 * @author logdev
 */

namespace Aliyun\Log;

use Aliyun\Log\RequestCore;
use Aliyun\Log\Protobuf;
use Aliyun\Log\ProtobufMessage;
use Aliyun\Log\ProtobufEnum;
use Aliyun\Log\SlsLog;
use Aliyun\Log\SlsLogContent;
use Aliyun\Log\SlsLogGroup;
use Aliyun\Log\SlsLogGroupList;
use Aliyun\Log\AliyunLogUtil;

datedefaulttimezoneset ( 'Asia/Shanghai' );

/*
requireonce realpath ( dirname ( FILE ) . '/../../LogAutoload.php' );
requireonce realpath ( dirname ( FILE ) . '/requestcore.class.php' );
requireonce realpath ( dirname ( FILE ) . '/sls.proto.php' );
requireonce realpath ( dirname ( FILE ) . '/protocolbuffers.inc.php' );
*/
if(!defined('APIVERSION'))
	define('APIVERSION', '0.6.0');
if(!defined('USERAGENT'))
	define('USERAGENT', 'log-php-sdk-v-0.6.0');

class AliyunLogClient {

    /**
     * @var string aliyun accessKey
     */
    protected $accessKey;
    
    /**
     * @var string aliyun accessKeyId
     */
    protected $accessKeyId;

    /**
     *@var string aliyun sts token
     */
    protected $stsToken;

    /**
     * @var string LOG endpoint
     */
    protected $endpoint;

    /**
     * @var string Check if the host if row ip.
     */
    protected $isRowIp;

    /**
     * @var integer Http send port. The dafault value is 80.
     */
    protected $port;

    /**
     * @var string log sever host.
     */
    protected $logHost;

    /**
     * @var string the local machine ip address.
     */
    protected $source;
    
    /**
     * AliyunLogClient constructor
     *
     * @param string $endpoint
     *            LOG host name, for example, http://cn-hangzhou.sls.aliyuncs.com
     * @param string $accessKeyId
     *            aliyun accessKeyId
     * @param string $accessKey
     *            aliyun accessKey
     */
    public function construct($endpoint, $accessKeyId, $accessKey,$token = "") {
        $this->setEndpoint ( $endpoint ); // set $this->logHost
        $this->accessKeyId = $accessKeyId;
        $this->accessKey = $accessKey;
        $this->stsToken = $token;
        $this->source = AliyunLogUtil::getLocalIp();
    }
    private function setEndpoint($endpoint) {
        $pos = strpos ( $endpoint, "://" );
        if ($pos !== false) { // be careful, !==
            $pos += 3;
            $endpoint = substr ( $endpoint, $pos );
        }
        $pos = strpos ( $endpoint, "/" );
        if ($pos !== false) // be careful, !==
            $endpoint = substr ( $endpoint, 0, $pos );
        $pos = strpos ( $endpoint, ':' );
        if ($pos !== false) { // be careful, !==
            $this->port = ( int ) substr ( $endpoint, $pos + 1 );
            $endpoint = substr ( $endpoint, 0, $pos );
        } else
            $this->port = 80;
        $this->isRowIp = AliyunLogUtil::isIp ( $endpoint );
        $this->logHost = $endpoint;
        $this->endpoint = $endpoint . ':' . ( string ) $this->port;
    }
     
    /**
     * GMT format time string.
     * 
     * @return string
     */
    protected function getGMT() {
        return gmdate ( 'D, d M Y H:i:s' ) . ' GMT';
    }
    

    /**
     * Decodes a JSON string to a JSON Object. 
     * Unsuccessful decode will cause an AliyunLogException.
     * 
     * @return string
     * @throws AliyunLogException
     */
    protected function parseToJson($resBody, $requestId) {
        if (! $resBody)
          return NULL;
        
        $result = jsondecode ( $resBody, true );
        if ($result === NULL){
          throw new AliyunLogException ( 'BadResponse', "Bad format,not json: $resBody", $requestId );
        }
        return $result;
    }
    
    /**
     * @return array
     */
    protected function getHttpResponse($method, $url, $body, $headers) {
        $request = new RequestCore ( $url );
        foreach ( $headers as $key => $value )
            $request->addheader ( $key, $value );
        $request->setmethod ( $method );
        $request->setuseragent(USERAGENT);
        if ($method == "POST" || $method == "PUT")
            $request->setbody ( $body );
        $request->sendrequest ();
        $response = array ();
        $response [] = ( int ) $request->getresponsecode ();
        $response [] = $request->getresponseheader ();
        $response [] = $request->getresponsebody ();
        return $response;
    }
    
    /**
     * @return array
     * @throws AliyunLogException
     */
    private function sendRequest($method, $url, $body, $headers) {
        try {
            list ( $responseCode, $header, $resBody ) = 
                    $this->getHttpResponse ( $method, $url, $body, $headers );
        } catch ( Exception $ex ) {
            throw new AliyunLogException ( $ex->getMessage (), $ex->toString () );
        }
        
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';

        if ($responseCode == 200) {
          return array ($resBody,$header);
        } 
        else {
            $exJson = $this->parseToJson ( $resBody, $requestId );
            if (isset($exJson ['errorcode']) && isset($exJson ['errormessage'])) {
                throw new AliyunLogException ( $exJson ['errorcode'], 
                        $exJson ['errormessage'], $requestId );
            } else {
                if ($exJson) {
                    $exJson = ' The return json is ' . jsonencode($exJson);
                } else {
                    $exJson = '';
                }
                throw new AliyunLogException ( 'RequestError',
                        "Request is failed. Http code is $responseCode.$exJson", $requestId );
            }
        }
    }
    
    /**
     * @return array
     * @throws AliyunLogException
     */
    private function send($method, $project, $body, $resource, $params, $headers) {
        if ($body) {
            $headers ['Content-Length'] = strlen ( $body );
            if(isset($headers ["x-log-bodyrawsize"])==false)
                $headers ["x-log-bodyrawsize"] = 0;
            $headers ['Content-MD5'] = AliyunLogUtil::calMD5 ( $body );
        } else {
            $headers ['Content-Length'] = 0;
            $headers ["x-log-bodyrawsize"] = 0;
            $headers ['Content-Type'] = ''; // If not set, http request will add automatically.
        }
        
        $headers ['x-log-apiversion'] = APIVERSION;
        $headers ['x-log-signaturemethod'] = 'hmac-sha1';
        if(strlen($this->stsToken) >0)
            $headers ['x-acs-security-token'] = $this -> stsToken;
        if(isnull($project))$headers ['Host'] = $this->logHost;
        else $headers ['Host'] = "$project.$this->logHost";
        $headers ['Date'] = $this->GetGMT ();
        $signature = AliyunLogUtil::getRequestAuthorization ( $method, $resource, $this->accessKey,$this->stsToken, $params, $headers );
        $headers ['Authorization'] = "LOG $this->accessKeyId:$signature";
        
        $url = $resource;
        if ($params)
            $url .= '?' . AliyunLogUtil::urlEncode ( $params );
        if ($this->isRowIp)
            $url = "http://$this->endpoint$url";
        else{
          if(isnull($project))
              $url = "http://$this->endpoint$url";
          else  $url = "http://$project.$this->endpoint$url";           
        }
        return $this->sendRequest ( $method, $url, $body, $headers );
    }
    
    /**
     * Put logs to Log Service.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsPutLogsRequest $request the PutLogs request parameters class
     * @throws AliyunLogException
     * @return AliyunLogModelsPutLogsResponse
     */
    public function putLogs(AliyunLogModelsPutLogsRequest $request) {
        if (count ( $request->getLogitems () ) > 4096)
            throw new AliyunLogException ( 'InvalidLogSize', "logItems' length exceeds maximum limitation: 4096 lines." );
        
        $logGroup = new LogGroup ();
        $topic = $request->getTopic () !== null ? $request->getTopic () : '';
        $logGroup->setTopic ( $request->getTopic () );
        $source = $request->getSource ();
        
        if ( ! $source )
            $source = $this->source;
        $logGroup->setSource ( $source );
        $logitems = $request->getLogitems ();
        foreach ( $logitems as $logItem ) {
            $log = new Log ();
            $log->setTime ( $logItem->getTime () );
            $content = $logItem->getContents ();
            foreach ( $content as $key => $value ) {
                $content = new LogContent ();
                $content->setKey ( $key );
                $content->setValue ( $value );
                $log->addContents ( $content );
            }

            $logGroup->addLogs ( $log );
        }

        $body = AliyunLogUtil::toBytes( $logGroup );
        unset ( $logGroup );
        
        $bodySize = strlen ( $body );
        if ($bodySize > 3 * 1024 * 1024) // 3 MB
            throw new AliyunLogException ( 'InvalidLogSize', "logItems' size exceeds maximum limitation: 3 MB." );
        $params = array ();
        $headers = array ();
        $headers ["x-log-bodyrawsize"] = $bodySize;
        $headers ['x-log-compresstype'] = 'deflate';
        $headers ['Content-Type'] = 'application/x-protobuf';
        $body = gzcompress ( $body, 6 );
        
        $logstore = $request->getLogstore () !== null ? $request->getLogstore () : '';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $shardKey = $request -> getShardKey();
        $resource = "/logstores/" . $logstore.($shardKey== null?"/shards/lb":"/shards/route");
        if($shardKey)
            $params["key"]=$shardKey;
        list ( $resp, $header ) = $this->send ( "POST", $project, $body, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsPutLogsResponse ( $header );
    }

    /**
     * create shipper service
     * @param AliyunLogModelsCreateShipperRequest $request
     * return AliyunLogModelsCreateShipperResponse
     */
    public function createShipper(AliyunLogModelsCreateShipperRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper";
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["Content-Type"] = "application/json";

        $body = array(
            "shipperName" => $request->getShipperName(),
            "targetType" => $request->getTargetType(),
            "targetConfiguration" => $request->getTargetConfigration()
        );
        $bodystr = jsonencode($body);
        $headers["x-log-bodyrawsize"] = strlen($bodystr);
        list($resp, $header) = $this->send("POST", $project,$bodystr,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new AliyunLogModelsCreateShipperResponse($resp, $header);
    }

    /**
     * create shipper service
     * @param AliyunLogModelsUpdateShipperRequest $request
     * return AliyunLogModelsUpdateShipperResponse
     */
    public function updateShipper(AliyunLogModelsUpdateShipperRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper/".$request->getShipperName();
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["Content-Type"] = "application/json";

        $body = array(
            "shipperName" => $request->getShipperName(),
            "targetType" => $request->getTargetType(),
            "targetConfiguration" => $request->getTargetConfigration()
        );
        $bodystr = jsonencode($body);
        $headers["x-log-bodyrawsize"] = strlen($bodystr);
        list($resp, $header) = $this->send("PUT", $project,$bodystr,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new AliyunLogModelsUpdateShipperResponse($resp, $header);
    }

    /**
     * get shipper tasks list, max 48 hours duration supported
     * @param AliyunLogModelsGetShipperTasksRequest $request
     * return AliyunLogModelsGetShipperTasksResponse
     */
    public function getShipperTasks(AliyunLogModelsGetShipperTasksRequest $request){
        $headers = array();
        $params = array(
            'from' => $request->getStartTime(),
            'to' => $request->getEndTime(),
            'status' => $request->getStatusType(),
            'offset' => $request->getOffset(),
            'size' => $request->getSize()
        );
        $resource = "/logstores/".$request->getLogStore()."/shipper/".$request->getShipperName()."/tasks";
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["x-log-bodyrawsize"] = 0;
        $headers["Content-Type"] = "application/json";

        list($resp, $header) = $this->send("GET", $project,null,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new AliyunLogModelsGetShipperTasksResponse($resp, $header);
    }

    /**
     * retry shipper tasks list by task ids
     * @param AliyunLogModelsRetryShipperTasksRequest $request
     * return AliyunLogModelsRetryShipperTasksResponse
     */
    public function retryShipperTasks(AliyunLogModelsRetryShipperTasksRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper/".$request->getShipperName()."/tasks";
        $project = $request->getProject () !== null ? $request->getProject () : '';

        $headers["Content-Type"] = "application/json";
        $body = $request->getTaskLists();
        $bodystr = jsonencode($body);
        $headers["x-log-bodyrawsize"] = strlen($bodystr);
        list($resp, $header) = $this->send("PUT", $project,$bodystr,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new AliyunLogModelsRetryShipperTasksResponse($resp, $header);
    }

    /**
     * delete shipper service
     * @param AliyunLogModelsDeleteShipperRequest $request
     * return AliyunLogModelsDeleteShipperResponse
     */
    public function deleteShipper(AliyunLogModelsDeleteShipperRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper/".$request->getShipperName();
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["x-log-bodyrawsize"] = 0;
        $headers["Content-Type"] = "application/json";

        list($resp, $header) = $this->send("DELETE", $project,null,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new AliyunLogModelsDeleteShipperResponse($resp, $header);
    }

    /**
     * get shipper config service
     * @param AliyunLogModelsGetShipperConfigRequest $request
     * return AliyunLogModelsGetShipperConfigResponse
     */
    public function getShipperConfig(AliyunLogModelsGetShipperConfigRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper/".$request->getShipperName();
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["x-log-bodyrawsize"] = 0;
        $headers["Content-Type"] = "application/json";

        list($resp, $header) = $this->send("GET", $project,null,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new AliyunLogModelsGetShipperConfigResponse($resp, $header);
    }

    /**
     * list shipper service
     * @param AliyunLogModelsListShipperRequest $request
     * return AliyunLogModelsListShipperResponse
     */
    public function listShipper(AliyunLogModelsListShipperRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper";
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["x-log-bodyrawsize"] = 0;
        $headers["Content-Type"] = "application/json";

        list($resp, $header) = $this->send("GET", $project,null,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new AliyunLogModelsListShipperResponse($resp, $header);
    }

    /**
     * create logstore 
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsCreateLogstoreRequest $request the CreateLogStore request parameters class.
     * @throws AliyunLogException
     * return AliyunLogModelsCreateLogstoreResponse
     */
    public function createLogstore(AliyunLogModelsCreateLogstoreRequest $request){
        $headers = array ();
        $params = array ();
        $resource = '/logstores';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["x-log-bodyrawsize"] = 0;
        $headers["Content-Type"] = "application/json";
        $body = array(
            "logstoreName" => $request -> getLogstore(),
            "ttl" => (int)($request -> getTtl()),
            "shardCount" => (int)($request -> getShardCount())
        );
        $bodystr =  jsonencode($body);
        $headers["x-log-bodyrawsize"] = strlen($bodystr);
        list($resp,$header)  = $this -> send("POST",$project,$bodystr,$resource,$params,$headers);
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsCreateLogstoreResponse($resp,$header);
    }
    /**
     * update logstore 
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsUpdateLogstoreRequest $request the UpdateLogStore request parameters class.
     * @throws AliyunLogException
     * return AliyunLogModelsUpdateLogstoreResponse
     */
    public function updateLogstore(AliyunLogModelsUpdateLogstoreRequest $request){
        $headers = array ();
        $params = array ();
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["Content-Type"] = "application/json";
        $body = array(
            "logstoreName" => $request -> getLogstore(),
            "ttl" => (int)($request -> getTtl()),
            "shardCount" => (int)($request -> getShardCount())
        );
        $resource = '/logstores/'.$request -> getLogstore();
        $bodystr =  jsonencode($body);
        $headers["x-log-bodyrawsize"] = strlen($bodystr);
        list($resp,$header)  = $this -> send("PUT",$project,$bodystr,$resource,$params,$headers);
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsUpdateLogstoreResponse($resp,$header);
    }
    /**
     * List all logstores of requested project.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsListLogstoresRequest $request the ListLogstores request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsListLogstoresResponse
     */
    public function listLogstores(AliyunLogModelsListLogstoresRequest $request) {
        $headers = array ();
        $params = array ();
        $resource = '/logstores';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsListLogstoresResponse ( $resp, $header );
    }

    /**
     * Delete logstore
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsDeleteLogstoreRequest $request the DeleteLogstores request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsDeleteLogstoresResponse
     */
    public function deleteLogstore(AliyunLogModelsDeleteLogstoreRequest $request) {
        $headers = array ();
        $params = array ();
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $logstore = $request -> getLogstore() != null ? $request -> getLogstore() :"";
        $resource = "/logstores/$logstore";
        list ( $resp, $header ) = $this->send ( "DELETE", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsDeleteLogstoreResponse ( $resp, $header );
    }

    /**
     * List all topics in a logstore.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsListTopicsRequest $request the ListTopics request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsListTopicsResponse
     */
    public function listTopics(AliyunLogModelsListTopicsRequest $request) {
        $headers = array ();
        $params = array ();
        if ($request->getToken () !== null)
            $params ['token'] = $request->getToken ();
        if ($request->getLine () !== null)
            $params ['line'] = $request->getLine ();
        $params ['type'] = 'topic';
        $logstore = $request->getLogstore () !== null ? $request->getLogstore () : '';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $resource = "/logstores/$logstore";
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsListTopicsResponse ( $resp, $header );
    }

    /**
     * Get histograms of requested query from log service.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsGetHistogramsRequest $request the GetHistograms request parameters class.
     * @throws AliyunLogException
     * @return array(json body, http header)
     */
    public function getHistogramsJson(AliyunLogModelsGetHistogramsRequest $request) {
        $headers = array ();
        $params = array ();
        if ($request->getTopic () !== null)
            $params ['topic'] = $request->getTopic ();
        if ($request->getFrom () !== null)
            $params ['from'] = $request->getFrom ();
        if ($request->getTo () !== null)
            $params ['to'] = $request->getTo ();
        if ($request->getQuery () !== null)
            $params ['query'] = $request->getQuery ();
        $params ['type'] = 'histogram';
        $logstore = $request->getLogstore () !== null ? $request->getLogstore () : '';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $resource = "/logstores/$logstore";
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return array($resp, $header);
    }
    
    /**
     * Get histograms of requested query from log service.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsGetHistogramsRequest $request the GetHistograms request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsGetHistogramsResponse
     */
    public function getHistograms(AliyunLogModelsGetHistogramsRequest $request) {
        $ret = $this->getHistogramsJson($request);
        $resp = $ret[0];
        $header = $ret[1];
        return new AliyunLogModelsGetHistogramsResponse ( $resp, $header );
    }

    /**
     * Get logs from Log service.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsGetLogsRequest $request the GetLogs request parameters class.
     * @throws AliyunLogException
     * @return array(json body, http header)
     */
    public function getLogsJson(AliyunLogModelsGetLogsRequest $request) {
        $headers = array ();
        $params = array ();
        if ($request->getTopic () !== null)
            $params ['topic'] = $request->getTopic ();
        if ($request->getFrom () !== null)
            $params ['from'] = $request->getFrom ();
        if ($request->getTo () !== null)
            $params ['to'] = $request->getTo ();
        if ($request->getQuery () !== null)
            $params ['query'] = $request->getQuery ();
        $params ['type'] = 'log';
        if ($request->getLine () !== null)
            $params ['line'] = $request->getLine ();
        if ($request->getOffset () !== null)
            $params ['offset'] = $request->getOffset ();
        if ($request->getOffset () !== null)
            $params ['reverse'] = $request->getReverse () ? 'true' : 'false';
        $logstore = $request->getLogstore () !== null ? $request->getLogstore () : '';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $resource = "/logstores/$logstore";
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return array($resp, $header);
        //return new AliyunLogModelsGetLogsResponse ( $resp, $header );
    }
    
    /**
     * Get logs from Log service.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsGetLogsRequest $request the GetLogs request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsGetLogsResponse
     */
    public function getLogs(AliyunLogModelsGetLogsRequest $request) {
        $ret = $this->getLogsJson($request);
        $resp = $ret[0];
        $header = $ret[1];
        return new AliyunLogModelsGetLogsResponse ( $resp, $header );
    }

    /**
     * Get logs from Log service.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsGetProjectLogsRequest $request the GetLogs request parameters class.
     * @throws AliyunLogException
     * @return array(json body, http header)
     */
    public function getProjectLogsJson(AliyunLogModelsGetProjectLogsRequest $request) {
        $headers = array ();
        $params = array ();
        if ($request->getQuery () !== null)
            $params ['query'] = $request->getQuery ();
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $resource = "/logs";
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return array($resp, $header);
        //return new AliyunLogModelsGetLogsResponse ( $resp, $header );
    }
     /**
     * Get logs from Log service.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsGetProjectLogsRequest $request the GetLogs request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsGetLogsResponse
     */
    public function getProjectLogs(AliyunLogModelsGetProjectLogsRequest $request) {
        $ret = $this->getProjectLogsJson($request);
        $resp = $ret[0];
        $header = $ret[1];
        return new AliyunLogModelsGetLogsResponse ( $resp, $header );
    }
    
    /**
     * Get logs from Log service with shardid conditions.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsBatchGetLogsRequest $request the BatchGetLogs request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsBatchGetLogsResponse
     */
    public function batchGetLogs(AliyunLogModelsBatchGetLogsRequest $request) {
      $params = array();
      $headers = array();
      $project = $request->getProject()!==null?$request->getProject():'';
      $logstore = $request->getLogstore()!==null?$request->getLogstore():'';
      $shardId = $request->getShardId()!==null?$request->getShardId():'';
      if($request->getCount()!==null)
          $params['count']=$request->getCount();
      if($request->getCursor()!==null)
          $params['cursor']=$request->getCursor();
      $params['type']='log';
      $headers['Accept-Encoding']='gzip';
      $headers['accept']='application/x-protobuf';

      $resource = "/logstores/$logstore/shards/$shardId";
      list($resp,$header) = $this->send("GET",$project,NULL,$resource,$params,$headers);
      //$resp is a byteArray
      $resp =  gzuncompress($resp);
      if($resp===false)$resp = new LogGroupList();
      
      else {
          $resp = new LogGroupList($resp);
      }
      return new AliyunLogModelsBatchGetLogsResponse ( $resp, $header );
    }

    /**
     * List Shards from Log service with Project and logstore conditions.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsListShardsRequest $request the ListShards request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsListShardsResponse
     */
    public function listShards(AliyunLogModelsListShardsRequest $request) {
        $params = array();
        $headers = array();
        $project = $request->getProject()!==null?$request->getProject():'';
        $logstore = $request->getLogstore()!==null?$request->getLogstore():'';

        $resource='/logstores/'.$logstore.'/shards';
        list($resp,$header) = $this->send("GET",$project,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsListShardsResponse ( $resp, $header );
    }

    /**
     * split a shard into two shards  with Project and logstore and shardId and midHash conditions.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsSplitShardRequest $request the SplitShard request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsListShardsResponse
     */
    public function splitShard(AliyunLogModelsSplitShardRequest $request) {
        $params = array();
        $headers = array();
        $project = $request->getProject()!==null?$request->getProject():'';
        $logstore = $request->getLogstore()!==null?$request->getLogstore():'';
        $shardId = $request -> getShardId()!== null ? $request -> getShardId():-1;
        $midHash = $request -> getMidHash()!= null?$request -> getMidHash():"";

        $resource='/logstores/'.$logstore.'/shards/'.$shardId;
        $params["action"] = "split";
        $params["key"] = $midHash;
        list($resp,$header) = $this->send("POST",$project,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsListShardsResponse ( $resp, $header );
    }
    /**
     * merge two shards into one shard with Project and logstore and shardId and conditions.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsMergeShardsRequest $request the MergeShards request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsListShardsResponse
     */
    public function MergeShards(AliyunLogModelsMergeShardsRequest $request) {
        $params = array();
        $headers = array();
        $project = $request->getProject()!==null?$request->getProject():'';
        $logstore = $request->getLogstore()!==null?$request->getLogstore():'';
        $shardId = $request -> getShardId()!= null ? $request -> getShardId():-1;

        $resource='/logstores/'.$logstore.'/shards/'.$shardId;
        $params["action"] = "merge";
        list($resp,$header) = $this->send("POST",$project,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsListShardsResponse ( $resp, $header );
    }
    /**
     * delete a read only shard with Project and logstore and shardId conditions.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsDeleteShardRequest $request the DeleteShard request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsListShardsResponse
     */
    public function DeleteShard(AliyunLogModelsDeleteShardRequest $request) {
        $params = array();
        $headers = array();
        $project = $request->getProject()!==null?$request->getProject():'';
        $logstore = $request->getLogstore()!==null?$request->getLogstore():'';
        $shardId = $request -> getShardId()!= null ? $request -> getShardId():-1;

        $resource='/logstores/'.$logstore.'/shards/'.$shardId;
        list($resp,$header) = $this->send("DELETE",$project,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        return new AliyunLogModelsDeleteShardResponse ( $header );
    }

    /**
     * Get cursor from Log service.
     * Unsuccessful opertaion will cause an AliyunLogException.
     *
     * @param AliyunLogModelsGetCursorRequest $request the GetCursor request parameters class.
     * @throws AliyunLogException
     * @return AliyunLogModelsGetCursorResponse
     */
    public function getCursor(AliyunLogModelsGetCursorRequest $request){
      $params = array();
      $headers = array();
      $project = $request->getProject()!==null?$request->getProject():'';
      $logstore = $request->getLogstore()!==null?$request->getLogstore():'';
      $shardId = $request->getShardId()!==null?$request->getShardId():'';
      $mode = $request->getMode()!==null?$request->getMode():'';
      $fromTime = $request->getFromTime()!==null?$request->getFromTime():-1;

      if((empty($mode) xor $fromTime==-1)==false){
        if(!empty($mode))
          throw new AliyunLogException ( 'RequestError',"Request is failed. Mode and fromTime can not be not empty simultaneously");
        else
          throw new AliyunLogException ( 'RequestError',"Request is failed. Mode and fromTime can not be empty simultaneously");
      }
      if(!empty($mode) && strcmp($mode,'begin')!==0 && strcmp($mode,'end')!==0)
        throw new AliyunLogException ( 'RequestError',"Request is failed. Mode value invalid:$mode");
      if($fromTime!==-1 && (isinteger($fromTime)==false || $fromTime<0))
        throw new AliyunLogException ( 'RequestError',"Request is failed. FromTime value invalid:$fromTime");
      $params['type']='cursor';
      if($fromTime!==-1)$params['from']=$fromTime;
      else $params['mode'] = $mode;
      $resource='/logstores/'.$logstore.'/shards/'.$shardId;
      list($resp,$header) = $this->send("GET",$project,NULL,$resource,$params,$headers); 
      $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
      $resp = $this->parseToJson ( $resp, $requestId );
      return new AliyunLogModelsGetCursorResponse($resp,$header);
    }

    public function createConfig(AliyunLogModelsCreateConfigRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        if($request->getConfig()!==null){
          $body = jsonencode($request->getConfig()->toArray());
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/configs';
        list($resp,$header) = $this->send("POST",NULL,$body,$resource,$params,$headers); 
        return new AliyunLogModelsCreateConfigResponse($header);
    }

    public function updateConfig(AliyunLogModelsUpdateConfigRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        $configName='';
        if($request->getConfig()!==null){
          $body = jsonencode($request->getConfig()->toArray());
          $configName=($request->getConfig()->getConfigName()!==null)?$request->getConfig()->getConfigName():'';
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/configs/'.$configName;
        list($resp,$header) = $this->send("PUT",NULL,$body,$resource,$params,$headers);  
        return new AliyunLogModelsUpdateConfigResponse($header);
    }

    public function getConfig(AliyunLogModelsGetConfigRequest $request){
        $params = array();
        $headers = array();

        $configName = ($request->getConfigName()!==null)?$request->getConfigName():'';
        
        $resource = '/configs/'.$configName;
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsGetConfigResponse($resp,$header);
    }

    public function deleteConfig(AliyunLogModelsDeleteConfigRequest $request){
        $params = array();
        $headers = array();
        $configName = ($request->getConfigName()!==null)?$request->getConfigName():'';
        $resource = '/configs/'.$configName;
        list($resp,$header) = $this->send("DELETE",NULL,NULL,$resource,$params,$headers); 
        return new AliyunLogModelsDeleteConfigResponse($header);
    }

    public function listConfigs(AliyunLogModelsListConfigsRequest $request){
        $params = array();
        $headers = array();

        if($request->getConfigName()!==null)$params['configName'] = $request->getConfigName();
        if($request->getOffset()!==null)$params['offset'] = $request->getOffset();
        if($request->getSize()!==null)$params['size'] = $request->getSize();

        $resource = '/configs';
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsListConfigsResponse($resp,$header);
    }
    
    public function createMachineGroup(AliyunLogModelsCreateMachineGroupRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        if($request->getMachineGroup()!==null){
          $body = jsonencode($request->getMachineGroup()->toArray());
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/machinegroups';
        list($resp,$header) = $this->send("POST",NULL,$body,$resource,$params,$headers); 

        return new AliyunLogModelsCreateMachineGroupResponse($header);
    }

    public function updateMachineGroup(AliyunLogModelsUpdateMachineGroupRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        $groupName='';
        if($request->getMachineGroup()!==null){
          $body = jsonencode($request->getMachineGroup()->toArray());
          $groupName=($request->getMachineGroup()->getGroupName()!==null)?$request->getMachineGroup()->getGroupName():'';
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/machinegroups/'.$groupName;
        list($resp,$header) = $this->send("PUT",NULL,$body,$resource,$params,$headers);  
        return new AliyunLogModelsUpdateMachineGroupResponse($header);
    }

    public function getMachineGroup(AliyunLogModelsGetMachineGroupRequest $request){
        $params = array();
        $headers = array();

        $groupName = ($request->getGroupName()!==null)?$request->getGroupName():'';
        
        $resource = '/machinegroups/'.$groupName;
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsGetMachineGroupResponse($resp,$header);
    }

    public function deleteMachineGroup(AliyunLogModelsDeleteMachineGroupRequest $request){
        $params = array();
        $headers = array();

        $groupName = ($request->getGroupName()!==null)?$request->getGroupName():'';
        $resource = '/machinegroups/'.$groupName;
        list($resp,$header) = $this->send("DELETE",NULL,NULL,$resource,$params,$headers); 
        return new AliyunLogModelsDeleteMachineGroupResponse($header);
    }

    public function listMachineGroups(AliyunLogModelsListMachineGroupsRequest $request){
        $params = array();
        $headers = array();

        if($request->getGroupName()!==null)$params['groupName'] = $request->getGroupName();
        if($request->getOffset()!==null)$params['offset'] = $request->getOffset();
        if($request->getSize()!==null)$params['size'] = $request->getSize();

        $resource = '/machinegroups';
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );

        return new AliyunLogModelsListMachineGroupsResponse($resp,$header);
    }

    public function applyConfigToMachineGroup(AliyunLogModelsApplyConfigToMachineGroupRequest $request){
        $params = array();
        $headers = array();
        $configName=$request->getConfigName();
        $groupName=$request->getGroupName();
        $headers ['Content-Type'] = 'application/json';
        $resource = '/machinegroups/'.$groupName.'/configs/'.$configName;
        list($resp,$header) = $this->send("PUT",NULL,NULL,$resource,$params,$headers);  
        return new AliyunLogModelsApplyConfigToMachineGroupResponse($header);
    }

    public function removeConfigFromMachineGroup(AliyunLogModelsRemoveConfigFromMachineGroupRequest $request){
        $params = array();
        $headers = array();
        $configName=$request->getConfigName();
        $groupName=$request->getGroupName();
        $headers ['Content-Type'] = 'application/json';
        $resource = '/machinegroups/'.$groupName.'/configs/'.$configName;
        list($resp,$header) = $this->send("DELETE",NULL,NULL,$resource,$params,$headers);  
        return new AliyunLogModelsRemoveConfigFromMachineGroupResponse($header);
    }

    public function getMachine(AliyunLogModelsGetMachineRequest $request){
        $params = array();
        $headers = array();

        $uuid = ($request->getUuid()!==null)?$request->getUuid():'';

        $resource = '/machines/'.$uuid;
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers);
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsGetMachineResponse($resp,$header);
    }

    public function createACL(AliyunLogModelsCreateACLRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        if($request->getAcl()!==null){
          $body = jsonencode($request->getAcl()->toArray());
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/acls';
        list($resp,$header) = $this->send("POST",NULL,$body,$resource,$params,$headers);
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsCreateACLResponse($resp,$header);
    }

    public function updateACL(AliyunLogModelsUpdateACLRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        $aclId='';
        if($request->getAcl()!==null){
          $body = jsonencode($request->getAcl()->toArray());
          $aclId=($request->getAcl()->getAclId()!==null)?$request->getAcl()->getAclId():'';
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/acls/'.$aclId;
        list($resp,$header) = $this->send("PUT",NULL,$body,$resource,$params,$headers);  
        return new AliyunLogModelsUpdateACLResponse($header);
    }
    
    public function getACL(AliyunLogModelsGetACLRequest $request){
        $params = array();
        $headers = array();

        $aclId = ($request->getAclId()!==null)?$request->getAclId():'';
        
        $resource = '/acls/'.$aclId;
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );

        return new AliyunLogModelsGetACLResponse($resp,$header);
    }
    
    public function deleteACL(AliyunLogModelsDeleteACLRequest $request){
        $params = array();
        $headers = array();
        $aclId = ($request->getAclId()!==null)?$request->getAclId():'';
        $resource = '/acls/'.$aclId;
        list($resp,$header) = $this->send("DELETE",NULL,NULL,$resource,$params,$headers); 
        return new AliyunLogModelsDeleteACLResponse($header);
    }
    
    public function listACLs(AliyunLogModelsListACLsRequest $request){
        $params = array();
        $headers = array();
        if($request->getPrincipleId()!==null)$params['principleId'] = $request->getPrincipleId();
        if($request->getOffset()!==null)$params['offset'] = $request->getOffset();
        if($request->getSize()!==null)$params['size'] = $request->getSize();

        $resource = '/acls';
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new AliyunLogModelsListACLsResponse($resp,$header);
    }

}

