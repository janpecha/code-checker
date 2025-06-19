<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use Nette\Neon\Neon;


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
			'.claude',
		];


		public static function create(
			?string $configFile,
			?string $currentWorkingDirectory = NULL
		): Checker
		{
			if ($configFile !== NULL) {
				$configurator = self::loadConfigFile($configFile);
				$config = new CheckerConfig(dirname($configFile), $configFile);
				$configurator($config);

			} else {
				$config = new CheckerConfig($currentWorkingDirectory);
			}

			if ($currentWorkingDirectory !== NULL && count($config->getPaths()) === 0) {
				$config->addPath($currentWorkingDirectory);
			}

			if (count($config->getRules()) === 0 && count($config->getTasks()) === 0 && count($config->getExtensions()) === 0) {
				AutoConfig::configure($config);
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
				$extensions[] = new Extensions\TasksExtension($tasks);
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
				new \CzProject\GitPhp\Git,
				self::$accept,
				$config->getRules()
			);

			return $checker;
		}


		private static function loadConfigFile(string $configFile): \Closure
		{
			if (!is_file($configFile)) {
				throw new \RuntimeException('Config file ' . $configFile . ' not found.');
			}

			$config = [];

			if (str_ends_with(strtolower($configFile), '.php')) {
				$config = require $configFile;

			} elseif (str_ends_with(strtolower($configFile), '.neon')) {
				$config = Neon::decodeFile($configFile);
			}

			if ($config instanceof \Closure) {
				return $config;
			}

			if ($config === NULL) {
				$config = [];
			}

			if (!is_array($config)) {
				throw new \RuntimeException('Config file ' . $configFile . ' is invalid.');
			}

			return function (CheckerConfig $checkerConfig) use ($config, $configFile) {
				if (isset($config['projectDirectory'])) {
					if (!is_string($config['projectDirectory'])) {
						throw new \RuntimeException("Option 'projectDirectory' must be string (config file '$configFile').");
					}

					$checkerConfig->setProjectDirectory($config['projectDirectory']);
				}

				if (isset($config['composerFile'])) {
					if (!is_string($config['composerFile'])) {
						throw new \RuntimeException("Option 'composerFile' must be string (config file '$configFile').");
					}

					$checkerConfig->setComposerFile($config['composerFile']);
				}

				if (isset($config['phpVersion'])) {
					if (!is_string($config['phpVersion'])) {
						throw new \RuntimeException("Option 'phpVersion' must be string (config file '$configFile').");
					}

					$checkerConfig->setPhpVersion(Version::fromString($config['phpVersion']));
				}

				if (isset($config['parameters'])) {
					if (!is_array($config['parameters'])) {
						throw new \RuntimeException("Option 'parameters' must be array (config file '$configFile').");
					}

					$checkerConfig->setParameters($config['parameters']);
				}

				if (isset($config['paths'])) {
					if (!is_array($config['paths'])) {
						throw new \RuntimeException("Option 'paths' must be array (config file '$configFile').");
					}

					foreach ($config['paths'] as $k => $path) {
						if (!is_string($path)) {
							throw new \RuntimeException("Option 'paths' > '$k' must be string (config file '$configFile').");
						}

						$checkerConfig->addPath($path);
					}
				}

				if (isset($config['scannedPaths'])) {
					if (!is_array($config['scannedPaths'])) {
						throw new \RuntimeException("Option 'scannedPaths' must be array (config file '$configFile').");
					}

					foreach ($config['scannedPaths'] as $k => $scannedPaths) {
						if (!is_string($scannedPaths)) {
							throw new \RuntimeException("Option 'scannedPaths' > '$k' must be string (config file '$configFile').");
						}

						$checkerConfig->addScannedPath($scannedPaths);
					}
				}

				if (isset($config['ignore'])) {
					if (!is_array($config['ignore'])) {
						throw new \RuntimeException("Option 'ignore' must be array (config file '$configFile').");
					}

					foreach ($config['ignore'] as $k => $ignore) {
						if (!is_string($ignore)) {
							throw new \RuntimeException("Option 'ignore' > '$k' must be string (config file '$configFile').");
						}

						$checkerConfig->addIgnore($ignore);
					}
				}

				if (isset($config['extensions'])) {
					if (!is_array($config['extensions'])) {
						throw new \RuntimeException("Option 'extensions' must be array (config file '$configFile').");
					}

					foreach ($config['extensions'] as $k => $extension) {
						if (!($extension instanceof Extension)) {
							throw new \RuntimeException("Option 'ignore' > '$k' must be instance of " . Extension::class . " (config file '$configFile').");
						}

						$checkerConfig->addExtension($extension);
					}
				}

				if (isset($config['sets'])) {
					if (!is_array($config['sets'])) {
						throw new \RuntimeException("Option 'sets' must be array (config file '$configFile').");
					}

					foreach ($config['sets'] as $k => $setName) {
						if (!is_string($setName)) {
							throw new \RuntimeException("Option 'sets' > '$k' must be string (config file '$configFile').");
						}

						if (!class_exists($setName)) {
							throw new \RuntimeException("Set '$setName' in option 'sets' > '$k' not found (config file '$configFile').");
						}

						if (!method_exists($setName, 'configure')) {
							throw new \RuntimeException("Set '$setName' in option 'sets' > '$k' has not method 'configure()' (config file '$configFile').");
						}

						$methodReflection = new \ReflectionMethod($setName, 'configure');

						if (!$methodReflection->isStatic()) {
							throw new \RuntimeException("Set '$setName' in option 'sets' > '$k' has not static method 'configure()' (config file '$configFile').");
						}

						$setFactory = [$setName, 'configure'];

						if (!is_callable($setFactory)) {
							throw new \RuntimeException("Set '$setName' in option 'sets' > '$k' has non-callable method 'configure()' (config file '$configFile').");
						}

						call_user_func($setFactory, $checkerConfig);
					}
				}
			};
		}
	}
