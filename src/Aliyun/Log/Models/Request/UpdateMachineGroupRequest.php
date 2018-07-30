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

class UpdateMachineGroupRequest extends Request
{

	private $machineGroup;

	/**
	 * Aliyun_Log_Models_UpdateMachineGroupRequest Constructor
	 *
	 */
	public function __construct($project = null, $machineGroup)
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
