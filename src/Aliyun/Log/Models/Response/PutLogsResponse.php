<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

/**
 * The response of the PutLogs API from log service.
 *
 * @author log service dev
 */

namespace Aliyun\Log\Models\Response;

class PutLogsResponse extends Response {
    /**
     * PutLogsResponse constructor
     *
     * @param array $header
     *            PutLogs HTTP response header
     */
    public function __construct($headers) {
        parent::__construct ( $headers );
    }
}
