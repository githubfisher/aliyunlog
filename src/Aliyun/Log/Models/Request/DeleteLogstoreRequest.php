<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

/**
 * The request used to delete logstore from log service.
 *
 * @author log service dev
 */

namespace Aliyun\Log\Models\Request;

class DeleteLogstoreRequest extends Request{

    private  $logstore;
    /**
     * Aliyun_Log_Models_DeleteLogstoreRequest constructor
     * 
     * @param string $project project name
     */
    public function __construct($project=null,$logstore = null) {
        parent::__construct($project);
        $this -> logstore = $logstore;
    }
    public function getLogstore()
    {
        return $this -> logstore;
    }
}
