<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Nette;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\PhpRule;


	class NetteRules
	{
		/**
		 * @return PhpRule[]
		 */
		public static function create(CheckerConfig $config): array
		{
			return array_merge(
				NetteUtilsRules::create($config)
			);
		}
	}
