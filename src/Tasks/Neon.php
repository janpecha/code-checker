<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Rules\Neon\NeonKeywordsRule;


	class Neon
	{
		public static function configure(CheckerConfig $config): void
		{
			$tasks = \Nette\CodeChecker\Tasks::class;
			$config->addRule(new NeonKeywordsRule);
			$config->addTask([$tasks, 'neonSyntaxChecker'], '*.neon');
		}
	}
