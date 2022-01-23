<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class CheckerFactory
	{
		public static $accept = [
			'*.php', '*.phpt', '*.inc',
			'*.txt', '*.texy', '*.md',
			'*.css', '*.less', '*.sass', '*.scss', '*.js', '*.json', '*.latte', '*.htm', '*.html', '*.phtml', '*.xml',
			'*.ini', '*.neon', '*.yml',
			'*.sh', '*.bat',
			'*.sql',
			'.htaccess', '.gitignore',
		];

		public static $ignore = [
			'.git', '.svn', '.idea', '*.tmp', 'tmp', 'temp', 'log', 'vendor', 'node_modules', 'bower_components',
			'*.min.js', 'package.json', 'package-lock.json',
		];


		public static function create(string $configFile): Checker
		{
			$configurator = self::loadFile($configFile);
			$config = new CheckerConfig;
			$configurator($config);

			return self::createChecker($config);
		}


		private static function createChecker(CheckerConfig $config): Checker
		{
			$paths = $config->getPaths();
			$tasks = $config->getTasks();

			if (count($paths) === 0) {
				throw new \RuntimeException('Missing paths, use method ' . CheckerConfig::class . '::addPath().');
			}

			if (count($tasks) === 0) {
				throw new \RuntimeException('Missing tasks, use method ' . CheckerConfig::class . '::addTask().');
			}

			$checker = new Checker(
				$paths,
				self::$accept,
				array_merge(self::$ignore, $config->getIgnore()),
				$tasks
			);

			return $checker;
		}


		private static function loadFile(string $configFile): \Closure
		{
			return require $configFile;
		}
	}
