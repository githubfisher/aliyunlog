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

class CreateShipperRequest extends Request
{
	private $shipperName;

	private $targetType;

	private $targetConfigration;

	private $logStore;

	/**
	 * @return mixed
	 */
	public function getLogStore()
	{
		return $this->logStore;
	}

	/**
	 * @param mixed $logStore
	 */
	public function setLogStore($logStore)
	{
		$this->logStore = $logStore;
	}

	/**
	 * Aliyun_Log_Models_CreateShipperRequest Constructor
	 *
	 */
	public function __construct($project)
	{
		parent::__construct($project);
	}

	/**
	 * @return mixed
	 */
	public function getShipperName()
	{
		return $this->shipperName;
	}

	/**
	 * @param mixed $shipperName
	 */
	public function setShipperName($shipperName)
	{
		$this->shipperName = $shipperName;
	}

	/**
	 * @return mixed
	 */
	public function getTargetType()
	{
		return $this->targetType;
	}

	/**
	 * @param mixed $targetType
	 */
	public function setTargetType($targetType)
	{
		$this->targetType = $targetType;
	}

	/**
	 * @return mixed
	 */
	public function getTargetConfigration()
	{
		return $this->targetConfigration;
	}

	/**
	 * @param mixed $targetConfigration
	 */
	public function setTargetConfigration($targetConfigration)
	{
		$this->targetConfigration = $targetConfigration;
	}
}