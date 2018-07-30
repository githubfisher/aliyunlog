<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace Aliyun\Log\Models;

use Aliyun\Log\Models\OssShipperStorage;

class OssShipperCsvStorage extends OssShipperStorage{
    private $columns;
    private $delimiter = ',';
    private $quote = '';
    private $nullIdentifier = '';
    private $header = false;

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

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter(string $delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @return string
     */
    public function getQuote(): string
    {
        return $this->quote;
    }

    /**
     * @param string $quote
     */
    public function setQuote(string $quote)
    {
        $this->quote = $quote;
    }

    /**
     * @return string
     */
    public function getNullIdentifier(): string
    {
        return $this->nullIdentifier;
    }

    /**
     * @param string $nullIdentifier
     */
    public function setNullIdentifier(string $nullIdentifier)
    {
        $this->nullIdentifier = $nullIdentifier;
    }

    /**
     * @return bool
     */
    public function isHeader(): bool
    {
        return $this->header;
    }

    /**
     * @param bool $header
     */
    public function setHeader(bool $header)
    {
        $this->header = $header;
    }

    public function to_json_object(){
        $detail =  array(
            'columns' => $this->columns,
            'delimiter' => $this->delimiter,
            'quote' => $this->quote,
            'nullIdentifier' => $this->nullIdentifier,
            'header' => $this->header
        );
        return array(
            'detail' => $detail,
            'format' => parent::getFormat()
        );
    }
}