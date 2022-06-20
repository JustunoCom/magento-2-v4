<?php
namespace Justuno\Core\Sentry;
class TransactionStack {
	/**
	 * 2020-06-27
	 * @used-by \Justuno\Core\Sentry\Client::__construct()
	 */
	function __construct() {$this->stack = [];}

	function clear()
	{
		$this->stack = [];
	}

	function peek()
	{
		$len = count($this->stack);
		if ($len === 0) {
			return null;
		}
		return $this->stack[$len - 1];
	}

	function push($context)
	{
		$this->stack[] = $context;
	}

	function pop($context=null)
	{
		if (!$context) {
			return array_pop($this->stack);
		}
		while (!empty($this->stack)) {
			if (array_pop($this->stack) === $context) {
				return $context;
			}
		}
	}
}