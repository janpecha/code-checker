<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class CheckerFactory
	{
		public static function create(string $configFile): CheckerRunner
		{
			$configurator = self::loadFile($configFile);
			$config = new CheckerConfig;
			$configurator($config);

			return self::createChecker($config);
		}


		private static function createChecker(CheckerConfig $config): CheckerRunner
		{
			$paths = $config->getPaths();
			$tasks = $config->getTasks();

			if (count($paths) === 0) {
				throw new \RuntimeException('Missing paths, use method ' . CheckerConfig::class . '::addPath().');
			}

			if (count($tasks) === 0) {
				throw new \RuntimeException('Missing tasks, use method ' . CheckerConfig::class . '::addTask().');
			}

			$checker = new \Nette\CodeChecker\Checker;

			foreach ($config->getIgnore() as $ignore) {
				$checker->addIgnore($ignore);
			}

			foreach ($tasks as $task) {
				$checker->addTask($task[0], $task[1]);
			}

			return new CheckerRunner($checker, $paths);
		}


		private static function loadFile(string $configFile): \Closure
		{
			return require $configFile;
		}
	}
