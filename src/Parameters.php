<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use Nette\Utils\Arrays;


	class Parameters
	{
		/** @var array<string, mixed> */
		private $data;


		/**
		 * @param array<string, mixed> $data
		 */
		public function __construct(array $data)
		{
			$this->data = $data;
		}


		/**
		 * @param  string|string[] $key
		 * @param  mixed $defaultValue
		 * @return mixed
		 */
		public function get($key, $defaultValue = NULL)
		{
			if ($defaultValue !== NULL && !$this->has($key)) {
				return $defaultValue;
			}

			return Arrays::get($this->data, $this->processKey($key));
		}


		/**
		 * @param  string|string[] $key
		 * @return bool
		 */
		public function has($key)
		{
			$arr = $this->data;

			foreach ($this->processKey($key) as $k) {
				if (is_array($arr) && array_key_exists($k, $arr)) {
					$arr = $arr[$k];

				} else {
					return FALSE;
				}
			}

			return TRUE;
		}


		/**
		 * @param  string|string[] $key
		 * @param  bool $defaultValue
		 */
		public function toBool($key, bool $defaultValue = NULL): bool
		{
			$value = $this->get($key, $defaultValue);

			if (!is_bool($value)) {
				throw new \RuntimeException("Value of parameter '{$this->formatKey($key)}' must be bool.");
			}

			return $value;
		}


		/**
		 * @param  string|string[] $key
		 * @param  int $defaultValue
		 */
		public function toInt($key, int $defaultValue = NULL): int
		{
			$value = $this->get($key, $defaultValue);

			if (!is_int($value)) {
				throw new \RuntimeException("Value of parameter '{$this->formatKey($key)}' must be int.");
			}

			return $value;
		}


		/**
		 * @param  string|string[] $key
		 * @param  string $defaultValue
		 */
		public function toString($key, string $defaultValue = NULL): string
		{
			$value = $this->get($key, $defaultValue);

			if (!is_string($value)) {
				throw new \RuntimeException("Value of parameter '{$this->formatKey($key)}' must be string.");
			}

			return $value;
		}


		/**
		 * @param  string|string[] $key
		 * @return string[]
		 */
		private function processKey($key)
		{
			return is_array($key) ? $key : explode('.', $key);
		}


		/**
		 * @param  string|string[] $key
		 * @return string
		 */
		private function formatKey($key)
		{
			return is_array($key) ? implode('.', $key) : $key;
		}
	}
