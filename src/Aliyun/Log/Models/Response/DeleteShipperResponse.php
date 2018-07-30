<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace Aliyun\Log\Models\Response;

class DeleteShipperResponse extends Response
{

	/**
	 * DeleteShipperResponse constructor
	 *
	 * @param array $resp
	 *            GetLogs HTTP response body
	 * @param array $header
	 *            GetLogs HTTP response header
	 */
	public function __construct($resp, $header)
	{
		parent::__construct($header);
	}
}