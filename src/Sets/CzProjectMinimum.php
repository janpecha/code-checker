<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Sets;

	use JP\CodeChecker\CheckerConfig;


	class CzProjectMinimum
	{
		public static function configure(CheckerConfig $config): void
		{
			\JP\CodeChecker\AutoConfig::configure($config);
		}
	}
