<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Version;


	class Php
	{
		public static function configure(CheckerConfig $config, Version $phpVersion): void
		{
			$tasks = \Nette\CodeChecker\Tasks::class;
			$config->addTask([$tasks, 'phpSyntaxChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'invalidPhpDocChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'invalidDoubleQuotedStringChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'trailingPhpTagRemover'], '*.php,*.phpt');
			$config->addTask([$tasks, 'tabIndentationPhpChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'docSyntaxtHinter'], '*.php,*.phpt');

			if ($phpVersion->isEqualOrGreater('5.6.0')) {
				$config->addTask([$tasks, 'shortArraySyntaxFixer'], '*.php,*.phpt');
			}

			if ($phpVersion->isEqualOrGreater('7.2.0')) {
				$config->addTask([$tasks, 'strictTypesDeclarationChecker'], '*.php,*.phpt');
			}
		}
	}
