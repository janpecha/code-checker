<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Version;
	use Nette\CodeChecker\Result;


	class NetteApplication
	{
		public static function configure(CheckerConfig $config, Version $version): void
		{
			if ($version->isEqualOrGreater('2.4.0')) {
				$config->addTask([self::class, 'presenterMethods'], '*.php');
			}
		}


		public static function presenterMethods(string &$contents, Result $result): void
		{
			Helpers::findAndReplace(
				$contents,
				$result,
				'#->isPost\\(\\)#m',
				'->isMethod(\'POST\')',
				'Nette: HTTP - method isPost() is deprecated, use isMethod(\'POST\') (deprecated in v2.4.0)'
			);
		}
	}
