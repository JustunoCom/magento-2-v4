<?php
namespace Justuno\Core\Qa;
use Justuno\Core\Qa\Trace\Frame as F;
use ArrayIterator as AI;
# 2020-06-16 "Port the `Df\Qa\Trace` class": https://github.com/justuno-com/core/issues/29
final class Trace implements \IteratorAggregate, \Countable {
	/**
	 * 2020-02-27
	 * @used-by ju_bt_s()
	 * @used-by \Justuno\Core\Qa\Message\Failure::postface()
	 * @param array(string => int|string) $frames
	 */
	function __construct(array $frames) {
		$this->_frames = [];
		$prev = null; /** @var F|null $prev */
		foreach ($frames as $frameA) { /** @var array(string => string|int) $frameA */
			/**
			 * 2017-07-01
			 * Сегодня при обработке исключительной ситуации при запуске теста из PHPUnit
			 * столкнулся с проблемой, что стек вызовов внутри файла PHPUnit в формате Phar
			 * в моём случае содержал какие-то бинарные символы, из-за которых падала моя функция @see df_trim()
			 * @see \Df\Zf\Filter\StringTrim::_splitUtf8()
			 * Я эту проблему решил тем, что теперь df_trim() по-умолчанию
			 * в случае исключительной ситуации просто возвращет исходную строку,
			 * а не возбуждает исключительную ситуацию.
			 * Однако мне в стеке вызовов в любом случае не нужна бинарная каша,
			 * поэтому я отсекаю ту часть стека, которая находится внутри Phar.
			 */
			if (ju_starts_with(jua($frameA, 'file'), 'phar://')) {
				break;
			}
			$this->_frames[]= $prev = F::i($frameA, $prev);
		}
	}

	/**
	 * 2020-02-27
	 * @override
	 * @see \Countable::count() https://www.php.net/manual/countable.count.php
	 * @used-by \Df\Qa\Trace\Formatter::p()
	 * @return int
	 */
	function count() {return count($this->_frames);}

	/**
	 * 2020-02-27
	 * @override
	 * @see \IteratorAggregate::getIterator() https://www.php.net/manual/iteratoraggregate.getiterator.php
	 * @used-by \Df\Qa\Trace\Formatter::p()
	 * @return AI
	 */
	function getIterator() {return new AI($this->_frames);}

	/**
	 * 2020-02-27
	 * @used-by __construct()
	 * @used-by count()
	 * @used-by getIterator()
	 * @var F[]
	 */
	private $_frames;
}