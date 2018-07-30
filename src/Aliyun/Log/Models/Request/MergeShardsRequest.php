<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

/**
 *
 *
 * @author log service dev
 */

namespace Aliyun\Log\Models\Request;

class MergeShardsRequest extends Request {

    private $logstore;

    /**
     * Aliyun_Log_Models_MergeShardsRequest Constructor
     *
     */
    public function __construct($project,$logstore,$shardId) {
        parent::__construct ( $project );
        $this->logstore = $logstore;
        $this->shardId = $shardId;
    }

    public function getLogstore(){
      return $this->logstore;
    }

    public function setLogstore($logstore){
      $this->logstore = $logstore;
    }

    public function getShardId(){
        return $this->shardId;
    }
}
