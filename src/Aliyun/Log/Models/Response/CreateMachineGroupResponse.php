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

class CreateMachineGroupResponse extends Response
{

	/**
	 * CreateMachineGroupResponse constructor
	 *
	 * @param array $header
	 *            GetLogs HTTP response header
	 */
	public function __construct($header)
	{
		parent::__construct($header);
	}


}
