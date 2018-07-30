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

class DeleteConfigRequest extends Request
{

	private $configName;

	/**
	 * Aliyun_Log_Models_DeleteConfigRequest Constructor
	 *
	 */
	public function __construct($project = null, $configName = null)
	{
		parent::__construct($project);
		$this->configName = $configName;
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
