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

class CreateConfigRequest extends Request
{

	private $config;

	/**
	 * Aliyun_Log_Models_CreateConfigRequest Constructor
	 *
	 */
	public function __construct($project = null, $config)
	{
		parent::__construct($project);
        $this->config = $config;
    }

	public function getConfig()
	{
		return $this->config;

	}

	public function setConfig($config)
	{
		$this->config = $config;
	}

}
