<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

/**
 * The request used to get logs by a query from log service.
 *
 * @author log service dev
 */

namespace Aliyun\Log\Models\Request;

class GetProjectLogsRequest extends Request {
    
    /**
     * @var string user defined query
     */
    private $query;
    
    
    /**
     * Aliyun_Log_Models_GetProjectLogsRequest Constructor
     * @param string $query
     *            user defined query
     */
    public function __construct($project = null,  $query = null ) {
        parent::__construct ( $project );
        
        $this->query = $query;
    }
    
    
    
    /**
     * Get user defined query
     *
     * @return string user defined query
     */
    public function getQuery() {
        return $this->query;
    }
    
   
}
