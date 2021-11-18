<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;


	class Files
	{
		public static function configure(CheckerConfig $config): void
		{
			$tasks = \Nette\CodeChecker\Tasks::class;
			$config->addTask([$tasks, 'controlCharactersChecker']);
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
