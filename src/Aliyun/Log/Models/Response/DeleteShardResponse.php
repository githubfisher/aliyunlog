<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

/**
 * The response of the GetLog API from log service.
 *
 * @author log service dev
 */

namespace Aliyun\Log\Models\Response;

class DeleteShardResponse extends Response
{
	/**
	 * DeleteShardResponse constructor
	 *
	 * @param array $header
	 * DeleteShard HTTP response header
	 */
	public function __construct($headers)
	{
		parent::__construct($headers);
	}
}
