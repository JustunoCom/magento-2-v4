<?php
namespace Justuno\Core\Sentry;
/**
 * 2020-06-28
 * @see \Justuno\Core\Sentry\ReprSerializer
 */
class Serializer {
	/**
	 * 2020-06-28
	 * @used-by \Justuno\Core\Sentry\Trace::info()
	 * @param mixed $v
	 * @param int $max_depth
	 * @param int $_depth
	 * @return array|bool|false|float|int|string|string[]|null
	 */
	function serialize($v, $max_depth=3, $_depth=0) {
		if ((is_array($v) || 'stdClass' === (is_object($v) ? get_class($v) : null)) && $_depth < $max_depth) {
			$new = [];
			foreach ($v as $k => $iv) {
				$new[$this->_serialize($k)] = $this->serialize($iv, $max_depth, $_depth + 1);
			}
			return $new;
		}
		return $this->_serialize($v);
	}

	/**
	 * 2020-06-28
	 * @used-by serialize()
	 * @see \Justuno\Core\Sentry\ReprSerializer::_serialize()
	 * @param mixed $v
	 * @return bool|false|float|int|string|string[]|null
	 */
	protected function _serialize($v) { /** @var string $r */
		if (is_null($v) || is_bool($v) || is_float($v) || is_integer($v)) {
			$r = $v;
		}
		elseif (is_object($v) || gettype($v) == 'object') {
			$r = 'Object ' . get_class($v);
		}
		elseif (is_resource($v)) {
			$r = 'Resource '. get_resource_type($v);
		}
		elseif (is_array($v)) {
			$r = 'Array of length ' . count($v);
		}
		else {
			$r = $this->chop($v);
		}
		return $r;
	}

	/**
	 * 2020-06-28
	 * 2021-02-22
	 * 1) «`extra` data is limited to approximately 256k characters, and each item is capped at approximately 16k characters»:
	 * https://docs.sentry.io/product/accounts/quotas#attribute-limits
	 * 2) "How to disable data clipping in the «Additional Data» block?": https://forum.sentry.io/t/694
	 * 3) "Bypass clipped strings and sliced stacktrace variables": https://github.com/getsentry/sentry-php/issues/450
	 * @used-by _serialize()
	 * @used-by \Justuno\Core\Sentry\ReprSerializer::_serialize()
	 * @param string|mixed $r
	 * @return false|string|string[]|null
	 */
	final protected function chop($r) {return ju_chop($r, 16000);}
}