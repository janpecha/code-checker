<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;


	class Php
	{
		const VERSION_5_6 = 50600;
		const VERSION_7_2 = 70200;


		public static function configure(CheckerConfig $config, int $phpVersion): void
		{
			$tasks = \Nette\CodeChecker\Tasks::class;
			$config->addTask([$tasks, 'phpSyntaxChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'invalidPhpDocChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'invalidDoubleQuotedStringChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'trailingPhpTagRemover'], '*.php,*.phpt');
			$config->addTask([$tasks, 'tabIndentationPhpChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'docSyntaxtHinter'], '*.php,*.phpt');

			if ($phpVersion >= self::VERSION_5_6) {
				$config->addTask([$tasks, 'shortArraySyntaxFixer'], '*.php,*.phpt');
			}

			if ($phpVersion >= self::VERSION_7_2) {
				$config->addTask([$tasks, 'strictTypesDeclarationChecker'], '*.php,*.phpt');
			}
		}
	}
