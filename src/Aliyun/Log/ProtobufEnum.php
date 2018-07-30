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

class ProtobufEnum {

	public static function toString($value) {
		if (is_null($value))
			return null;
		if (array_key_exists($value, self::$_values))
			return self::$_values[$value];
		return 'UNKNOWN';
	}
}