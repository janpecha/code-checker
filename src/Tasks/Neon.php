<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Rules\Neon\NeonKeywordsRule;
	use JP\CodeChecker\Rules\Neon\NeonSyntaxRule;


	class Neon
	{
		public static function configure(CheckerConfig $config): void
		{
			$config->addRule(new NeonKeywordsRule);
			$config->addRule(new NeonSyntaxRule);
		}
	}
