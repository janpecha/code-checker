<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use CzProject\Assert\Assert;
	use Nette\Utils\Strings;


	/**
	 * @todo move to inteve/types
	 */
	class Version
	{
		/** @var string */
		private $version;


		/**
		 * @param string $version
		 */
		public function __construct($version)
		{
			Assert::true(self::isValid($version), 'Invalid version string.');
			$this->version = $version;
		}


		/**
		 * @param  string $version
		 * @param  string $operator
		 * @return bool
		 */
		public function compare($version, $operator)
		{
			Assert::true(self::isValid($version), 'Invalid version string.');
			Assert::true(self::isOperatorValid($operator), 'Invalid operator.');
			return version_compare($this->version, $version, $operator);
		}


		/**
		 * @param  string $version
		 * @return bool
		 */
		public function isEqual($version)
		{
			return $this->compare($version, '=');
		}


		/**
		 * @param  string $version
		 * @return bool
		 */
		public function isEqualOrGreater($version)
		{
			return $this->compare($version, '>=');
		}


		public function toMinorString(): string
		{
			return (string) Strings::before($this->version, '.', 2);
		}


		public static function fromString(string $version): self
		{
			Assert::true((bool) Strings::match($version, '#^(?:0|[1-9]\\d*)(?:\\.(?:0|[1-9]\\d*))*\\z#'), 'Version string is not valid.');
			$count = substr_count($version, '.');

			if ($count === 0) {
				return new self($version . '.0.0');

			} elseif ($count === 1) {
				return new self($version . '.0');

			} elseif ($count === 2) {
				return new self($version);
			}

			$version = \Nette\Utils\Strings::before($version, '.', 3);
			assert($version !== NULL);
			return new self($version);
		}


		/**
		 * @param  string $version
		 * @return bool
		 */
		private static function isValid($version)
		{
			return (bool) Strings::match($version, '#^(?:0|[1-9]\\d*)\\.(?:0|[1-9]\\d*)\\.(?:0|[1-9]\\d*)\\z#');
		}


		/**
		 * @param  string $operator
		 * @return bool
		 */
		private static function isOperatorValid($operator)
		{
			return $operator === '<'
				|| $operator === '>'
				|| $operator === '='
				|| $operator === '<='
				|| $operator === '>='
				|| $operator === '=='
				|| $operator === '!=';
		}
	}
