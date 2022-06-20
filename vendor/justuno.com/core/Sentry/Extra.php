<?php
# 2017-01-03
# https://forum.sentry.io/t/694
# https://docs.sentry.io/learn/quotas/#attributes-limits
# 2020-08-13 "Port the `Df\Sentry\Extra` class" https://github.com/justuno-com/core/issues/172
namespace Justuno\Core\Sentry;
final class Extra {
	/**
	 * 2017-01-03
	 * @used-by adjust() Recursion.
	 * @used-by \Justuno\Core\Sentry\Client::capture()
	 * @param array(string => mixed) $a
	 * @return array(string => string)
	 */
	static function adjust(array $a) {/** @var array(string => string) $r */
		$r = [];
		foreach ($a as $k => $v)  {/** @var string $k */ /** @var mixed $v */
			if (!is_array($v)) {
				$r[$k] = $v;
			}
			else {
				$json = ju_json_encode($v); /** @var string $json */
				# 2017-01-03 We need the length in bytes, not in characters:
				# https://docs.sentry.io/learn/quotas/#attributes-limits
				$l = strlen($json); /** @var int $l */
				if ($l <= 512) {
					$r[$k] = $json;
				}
				else {
					# 2017-01-03
					# The JSON string requires more than 512 bytes,
					# so I transfer the elements of the array $v on a level higher ($a), and add $k prefix to its keys.
					$r = array_merge($r, self::adjust(juak_transform($v, function($vk) use($k) {return "$k/$vk";})));
				}
			}
		}
		return $r;
	}
}