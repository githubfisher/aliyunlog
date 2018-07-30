<?php
/**
 * Class to aid in the parsing and creating of Protocol Buffer Messages
 * This class should be included by the developer before they use a
 * generated protobuf class.
 *
 * @author Andrew Brampton
 *
 */

namespace Aliyun\Log;

class ProtobufMessage
{

	function __construct($fp = null, &$limit = PHP_INT_MAX)
	{
		if ($fp !== null) {
			if (is_string($fp)) {
				// If the input is a string, turn it into a stream and decode it
				$str = $fp;
				$fp  = fopen('php://memory', 'r+b');
				fwrite($fp, $str);
				rewind($fp);
			}
			$this->read($fp, $limit);
			if (isset($str)) {
				fclose($fp);
			}
		}
	}
}