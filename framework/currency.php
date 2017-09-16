<?php
namespace Framework;

class Currency extends Base {
	/**
	 * @readwrite
	 * @var string
	 */
	protected $_base = 'usd';

	/**
	 * @readwrite
	 * x Base = 1 USD i.e 1 USD = x Base
	 * So this map shows how much of the base value would it take to make 1 USD
	 * @var array
	 */
	protected $_defaultMap = [
		'inr' => 66,
		'pkr' => 104,
		'aud' => 1.3,
		'eur' => 0.9,
		'gbp' => 0.8,
		'usd' => 1
	];

	/**
	 * @readwrite
	 * @var array
	 */
	protected $_symbolMap = [
		'inr' => "<i class='fa fa-inr'></i>",
		'pkr' => 'Rs.',
		'aud' => "<i class='fa fa-usd'></i>",
		'eur' => "<i class='fa fa-eur'></i>",
		'gbp' => "<i class='fa fa-gbp'></i>",
		'usd' => "<i class='fa fa-usd'></i>"
	];

	public function &getDefaultMap() {
		return $this->_defaultMap;
	}

	public function &getSymbolMap() {
		return $this->_symbolMap;
	}

	public function setBase($value) {
		$value = strtolower($value);
		$keys = array_keys($this->_defaultMap);
		if (!in_array($value, $keys)) {
			throw new Core\Exception\Implementation("Base should be one of the " . implode(",", $keys));
		}
		$this->_base = $value;
	}

	/**
	 * @param float $value Set 1 Base = x USD
	 * @throws Core\Exception\Argument Throws exception if value is not provided
	 */
	public function setBaseMap($value = null) {
		if (!is_numeric($value)) {
			throw new Core\Exception\Argument('Argument $value should be numeric!!');
		}
		$this->_defaultMap[$this->base] = $value;
	}

	public function getRate() {
		return $this->defaultMap[$this->base] ?? 1;
	}

	public function getSymbol() {
		return $this->_symbolMap[$this->base];
	}

	public function toUsd($amountInBase) {
		$amount = (float) $amountInBase;
		$rate = $this->getRate();

		$v = $amount / $rate;
		return $v;
	}

	public function toRegional($amount, $amountCurrency = 'usd') {
		$amountCurrency = strtolower($amountCurrency);
		
		if ($amountCurrency !== 'usd') {
			$obj = new static(['base' => $amountCurrency]);
			$usdAmount = $obj->toUsd($amount);
		} else {
			$usdAmount = (float) $amount;
		}

		$rate = $this->getRate();
		$v = $usdAmount * $rate;
		return $v;
	}
}
