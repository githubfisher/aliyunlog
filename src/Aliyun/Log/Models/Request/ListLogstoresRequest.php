<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

/**
 * The request used to list logstore from log service.
 *
 * @author log service dev
 */

namespace Aliyun\Log\Models\Request;

class ListLogstoresRequest extends Request{
    
    /**
     * Aliyun_Log_Models_ListLogstoresRequest constructor
     * 
     * @param string $project project name
     */
    public function __construct($project=null) {
        parent::__construct($project);
    }
}
