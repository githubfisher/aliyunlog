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

class SplitShardRequest extends Request
{

	private $logstore;

	/**
	 * Aliyun_Log_Models_SplitShardRequest Constructor
	 *
	 */
	public function __construct($project, $logstore, $shardId, $midHash)
	{
		parent::__construct($project);
		$this->logstore = $logstore;
		$this->shardId  = $shardId;
		$this->midHash  = $midHash;
	}

	public function getLogstore()
	{
		return $this->logstore;
	}

	public function setLogstore($logstore)
	{
		$this->logstore = $logstore;
	}

	public function getShardId()
	{
		return $this->shardId;
	}

	public function getMidHash()
	{
		return $this->midHash;
	}

}
