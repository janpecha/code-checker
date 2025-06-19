<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Rules;


	class Files
	{
		public static function configure(CheckerConfig $config): void
		{
			$tasks = Tasks::class;
			$config->addRule(new Rules\Files\ControlCharactersRule);
			$config->addTask([$tasks, 'bomFixer']);
			$config->addTask([$tasks, 'utf8Checker']);
			$config->addTask([$tasks, 'newlineNormalizer'], '!*.sh');
			$config->addTask([$tasks, 'yamlIndentationChecker'], '*.yml');
			$config->addTask([$tasks, 'trailingWhiteSpaceFixer']);
			$config->addTask([$tasks, 'tabIndentationChecker'], '*.css,*.less,*.js,*.json,*.neon');
			$config->addTask([$tasks, 'unexpectedTabsChecker'], '*.yml');

			$config->addTask([$tasks, 'jsonSyntaxChecker'], '*.json');
		}
	}
