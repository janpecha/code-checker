<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Version;
	use Nette\CodeChecker\Result;


	class NetteUtils
	{
		public static function configure(CheckerConfig $config, Version $version): void
		{
			if ($version->isEqualOrGreater('2.4.0')) {
				$config->addTask([self::class, 'netteObjectFixer'], '*.php');
			}
		}


		public static function netteObjectFixer(string &$contents, Result $result): void
		{
			Helpers::findAndReplace(
				$contents,
				$result,
				'#(class [A-Z][a-zA-Z0-9_]+)\\sextends\\s(\\\\?Nette\\\\)Object((?:\\simplements [a-zA-Z0-9_\\\\]+){0,1}\\n(\\s*){)#m',
				"$1$3\n$4\tuse $2SmartObject;\n",
				'Nette: Nette\\Object replaced by Nette\\SmartObject (deprecated in v2.4.0)'
			);
		}
	}
