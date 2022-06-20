<?php
namespace Justuno\Core\Sentry;
/**
 * Serializes a value into a representation that should reasonably suggest
 * both the type and value, and be serializable into JSON.
 * @package raven
 */
final class ReprSerializer extends Serializer {
	/**
	 * 2020-06-28
	 * @override
	 * @see \Justuno\Core\Sentry\Serializer::_serialize()
	 * @used-by \Justuno\Core\Sentry\Serializer::serialize()
	 * @param mixed $v
	 * @return bool|false|float|int|string|string[]|null
	 */
	protected function _serialize($v) { /** @var string $r */
		if ($v === null) {
			$r = 'null';
		}
		elseif ($v === false) {
			$r = 'false';
		}
		elseif ($v === true) {
			$r = 'true';
		}
		elseif (is_float($v) && (int) $v == $v) {
			$r = $v.'.0';
		}
		elseif (is_integer($v) || is_float($v)) {
			$r = (string) $v;
		}
		elseif (is_object($v) || gettype($v) == 'object') {
			$r = 'Object '.get_class($v);
		}
		elseif (is_resource($v)) {
			$r = 'Resource '.get_resource_type($v);
		}
		elseif (is_array($v)) {
			$r = 'Array of length ' . count($v);
		}
		else {
			$r = $this->chop($v);
		}
		return $r;
	}
}