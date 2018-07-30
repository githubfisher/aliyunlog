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

class GetMachineGroupRequest extends Request
{

	private $groupName;

	/**
	 * Aliyun_Log_Models_GetMachineGroupRequest Constructor
	 *
	 */
	public function __construct($project = null, $groupName = null)
	{
		parent::__construct($project);
		$this->groupName = $groupName;
	}

	public function getGroupName()
	{
		return $this->groupName;
	}

	public function setGroupName($groupName)
	{
		$this->groupName = $groupName;
	}

}
