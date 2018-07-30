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

class CreateMachineGroupRequest extends Request
{

	private $machineGroup;

	/**
	 * Aliyun_Log_Models_CreateMachineGroupRequest Constructor
	 *
	 */
	public function __construct($project = null, $machineGroup = null)
	{
		parent::__construct($project);
		$this->machineGroup = $machineGroup;
	}

	public function getMachineGroup()
	{
		return $this->machineGroup;
	}

	public function setMachineGroup($machineGroup)
	{
		$this->machineGroup = $machineGroup;
	}

}
