<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace Aliyun\Log\Models;

use Aliyun\Log\Models\OssShipperStorage;

class OssShipperParquetStorage extends OssShipperStorage{
    private  $columns;

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param mixed $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    public function to_json_object(){
        $detail = array(
            'columns' => $this->columns
        );
        return array(
            'detail' => $detail,
            'format' => parent::getFormat()
        );
    }
}
