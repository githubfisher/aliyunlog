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

class RemoveConfigFromMachineGroupRequest extends Request
{
	private $groupName;
	private $configName;

	/**
	 * Aliyun_Log_Models_RemoveConfigFromMachineGroupRequest Constructor
	 *
	 */
	public function __construct($project = null, $groupName = null, $configName = null)
	{
		parent::__construct($project);
		$this->groupName  = $groupName;
		$this->configName = $configName;
	}

	public function getGroupName()
	{
		return $this->groupName;
	}

	public function setGroupName($groupName)
	{
		$this->groupName = $groupName;
	}

	public function getConfigName()
	{
		return $this->configName;
	}

	public function setConfigName($configName)
	{
		$this->configName = $configName;
	}

}
