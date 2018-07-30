<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace Aliyun\Log\Models;

use Aliyun\Log\Models\OssShipperStorage;

class OssShipperJsonStorage extends OssShipperStorage{

    public function to_json_object(){
        return array();
    }
}