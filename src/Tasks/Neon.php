<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;
	use Nette\CodeChecker\Result;


	class Neon
	{
		public static function configure(CheckerConfig $config): void
		{
			$tasks = \Nette\CodeChecker\Tasks::class;
			$config->addTask([self::class, 'keywordsFixer'], '*.neon');
			$config->addTask([$tasks, 'neonSyntaxChecker'], '*.neon');
		}


		public static function keywordsFixer(string &$contents, Result $result): void
		{
			Helpers::findAndReplaces(
				$contents,
				$result,
				[
					'#(:\\s)on$#m' => '$1yes',
					'#(:\\s)off$#m' => '$1no',
				],
				'Neon: keywords on/off changed to yes/no (deprecated in v3.1)'
			);
		}
	}
