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

class CreateACLRequest extends Request
{

	private $acl;

	/**
	 * Aliyun_Log_Models_CreateACLRequest Constructor
	 *
	 */
	public function __construct($project = null, $acl = null)
	{
		parent::__construct($project);
		$this->acl = $acl;
	}

	public function getAcl()
	{
		return $this->acl;
	}

	public function setAcl($acl)
	{
		$this->acl = $acl;
	}

}
