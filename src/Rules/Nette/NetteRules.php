<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Nette;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Rule;


	class NetteRules
	{
		/**
		 * @return Rule[]
		 */
		public static function create(CheckerConfig $config): array
		{
			return array_merge(
				NetteUtilsRules::create($config)
			);
		}
	}
