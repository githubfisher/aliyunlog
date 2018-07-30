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

class GetMachineRequest extends Request
{

	private $uuid;

	/**
	 * Aliyun_Log_Models_GetMachineRequest Constructor
	 *
	 */
	public function __construct($project = null, $uuid = null)
	{
		parent::__construct($project);
		$this->uuid = $uuid;
	}

	public function getUuid()
	{
		return $this->uuid;
	}

	public function setUuid($uuid)
	{
		$this->uuid = $uuid;
	}

}
