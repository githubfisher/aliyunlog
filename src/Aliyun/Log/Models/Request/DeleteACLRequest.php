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

class DeleteACLRequest extends Request
{

	private $aclId;

	/**
	 * Aliyun_Log_Models_DeleteACLRequest Constructor
	 *
	 */
	public function __construct($project = null, $aclId = null)
	{
		parent::__construct($project);
		$this->aclId = $aclId;
	}

	public function getAclId()
	{
		return $this->aclId;
	}

	public function setAclId($aclId)
	{
		$this->aclId = $aclId;
	}

}
