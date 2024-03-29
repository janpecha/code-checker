<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class CheckerFactory
	{
		/** @var string[] */
		public static $accept = [
			'*.php', '*.phpt', '*.inc',
			'*.txt', '*.texy', '*.md',
			'*.css', '*.less', '*.sass', '*.scss', '*.js', '*.json', '*.latte', '*.htm', '*.html', '*.phtml', '*.xml',
			'*.ini', '*.neon', '*.yml',
			'*.sh', '*.bat',
			'*.sql',
			'.htaccess', '.gitignore',
		];

		/** @var string[] */
		public static $ignore = [
			'.git', '.svn', '.idea', '*.tmp', 'tmp', 'temp', 'log', 'vendor', 'node_modules', 'bower_components',
			'*.min.js', 'package.json', 'package-lock.json',
		];


		public static function create(
			?string $configFile,
			?string $currentWorkingDirectory = NULL
		): Checker
		{
			if ($configFile !== NULL) {
				$configurator = self::loadFile($configFile);
				$config = new CheckerConfig(dirname($configFile));
				$configurator($config);

			} else {
				$config = new CheckerConfig($currentWorkingDirectory);
				AutoConfig::configure($config);
			}

			if ($currentWorkingDirectory !== NULL && count($config->getPaths()) === 0) {
				$config->addPath($currentWorkingDirectory);
			}

			return self::createChecker($config);
		}


		private static function createChecker(CheckerConfig $config): Checker
		{
			$extensions = $config->getExtensions();
			$paths = $config->getPaths();
			$scannedPaths = $config->getScannedPaths();
			$tasks = $config->getTasks();

			if (count($paths) === 0) {
				throw new \RuntimeException('Missing paths, use method ' . CheckerConfig::class . '::addPath().');
			}

			if (count($tasks) > 0) {
				$extensions[] = new Extensions\TasksExtension(self::$accept, $tasks);
			}

			if (count($extensions) === 0) {
				throw new \RuntimeException('Missing extensions/tasks, use method ' . CheckerConfig::class . '::addExtension() or' . CheckerConfig::class . '::addTask().');
			}

			$checker = new Checker(
				$config->getProjectDirectory(),
				$paths,
				$scannedPaths,
				array_merge(self::$ignore, $config->getIgnore()),
				$extensions,
				new \CzProject\GitPhp\Git
			);

			return $checker;
		}


		private static function loadFile(string $configFile): \Closure
		{
			if (!is_file($configFile)) {
				throw new \RuntimeException('Config file ' . $configFile . ' not found.');
			}

			return require $configFile;
		}
	}
