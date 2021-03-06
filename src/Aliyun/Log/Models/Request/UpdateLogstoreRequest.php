<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

/**
 * The request used to Update logstore from log service.
 *
 * @author log service dev
 */

namespace Aliyun\Log\Models\Request;

class UpdateLogstoreRequest extends Request
{

	private $logstore;
	private $ttl;
	private $shardCount;

	/**
	 * Aliyun_Log_Models_UpdateLogstoreRequest constructor
	 *
	 * @param string $project project name
	 */
	public function __construct($project = null, $logstore = null, $ttl = null, $shardCount = null)
	{
		parent::__construct($project);
		$this->logstore   = $logstore;
		$this->ttl        = $ttl;
		$this->shardCount = $shardCount;
	}

	public function getLogstore()
	{
		return $this->logstore;
	}

	public function getTtl()
	{
		return $this->ttl;
	}

	public function getShardCount()
	{
		return $this->shardCount;
	}
}
