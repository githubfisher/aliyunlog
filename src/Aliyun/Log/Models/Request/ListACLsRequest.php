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

class ListACLsRequest extends Request
{

	private $offset;
	private $size;
	private $principleId;

	/**
	 * Aliyun_Log_Models_ListACLsRequest Constructor
	 */
	public function __construct($project = null, $principleId = null, $offset = null, $size = null)
	{
		parent::__construct($project);
		$this->offset      = $offset;
		$this->size        = $size;
		$this->principleId = $principleId;
	}

	public function getOffset()
	{
		return $this->offset;
	}

	public function setOffset($offset)
	{
		$this->offset = $offset;
	}

	public function getSize()
	{
		return $this->size;
	}

	public function setSize($size)
	{
		$this->size = $size;
	}

	public function getPrincipleId()
	{
		return $this->principleId;
	}

	public function setPrincipleId($principleId)
	{
		$this->principleId = $principleId;
	}

}
