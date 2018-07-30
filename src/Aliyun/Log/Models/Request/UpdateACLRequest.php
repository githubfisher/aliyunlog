<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace Aliyun\Log\Models\Request;

require_once realpath(dirname(__FILE__) . '/Request.php');

/**
 *
 *
 * @author log service dev
 */
class UpdateACLRequest extends Request
{

	private $acl;

	/**
	 * Aliyun_Log_Models_UpdateACLRequest Constructor
	 *
	 */
	public function __construct($project = null, $acl)
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
