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

class DeleteMachineGroupRequest extends Request
{


	private $groupName;

	/**
	 * Aliyun_Log_Models_DeleteMachineGroupRequest Constructor
	 *
	 */
	public function __construct($project = null, $groupName)
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
