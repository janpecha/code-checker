<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use Nette\Utils\Strings;


	class CheckerConfig
	{
		/** @var string|NULL */
		private $baseDirectory;

		/** @var string|NULL */
		private $configFile;

		/** @var string|NULL */
		private $projectDirectory;

		/** @var ComposerFile|NULL */
		private $composerFile;

		/** @var string|NULL */
		private $composerFilePath;

		/** @var string[] */
		private $paths = [];

		/** @var string[] */
		private $ignore = [];

		/** @var Extension[] */
		private $extensions = [];

		/** @var Rule[] */
		private $rules = [];

		/** @var Task[] */
		private $tasks = [];

		/** @var Parameters|NULL */
		private $parameters;

		/** @var Version|NULL */
		private $phpVersion;

		/** @var Version|NULL */
		private $maxPhpVersion;

		/** @var ComposerVersions|NULL */
		private $composerVersions;


		public function __construct(
			?string $baseDirectory,
			?string $configFile = NULL
		)
		{
			$this->baseDirectory = $baseDirectory;
			$this->configFile = $configFile;
		}


		public function getConfigFile(): ?string
		{
			return $this->configFile;
		}


		public function getProjectDirectory(): string
		{
			if ($this->projectDirectory === NULL && $this->baseDirectory !== NULL) {
				$this->projectDirectory = $this->baseDirectory;
			}

			if ($this->projectDirectory === NULL) {
				throw new \RuntimeException('ProjectDirectory is not set, use $config->setProjectDirectory().');
			}

			return $this->projectDirectory;
		}


		public function setProjectDirectory(string $projectDirectory): self
		{
			if ($this->projectDirectory !== NULL) {
				throw new \RuntimeException('ProjectDirectory is already set.');
			}

			$this->projectDirectory = \CzProject\PathHelper::absolutizePath($projectDirectory);
			return $this;
		}


		public function getComposerFile(): ComposerFile
		{
			if ($this->composerFile === NULL) {
				$path = $this->composerFilePath !== NULL
					? $this->composerFilePath
					: $this->processPath('./composer.json');

				if (is_file($path)) {
					$this->composerFile = ComposerFile::open($path);

				} else {
					$this->composerFile = new ComposerFile($path, []);
				}
			}

			return $this->composerFile;
		}


		public function setComposerFile(string $composerFile): self
		{
			if ($this->composerFile !== NULL || $this->composerFilePath !== NULL) {
				throw new \RuntimeException('ComposerFile is already set.');
			}

			$this->composerFilePath = $this->processPath($composerFile);
			return $this;
		}


		public function getComposerVersions(): ComposerVersions
		{
			if ($this->composerVersions === NULL) {
				$this->composerVersions = ComposerVersions::create($this->getComposerFile()->getPath());
			}

			return $this->composerVersions;
		}


		public function getPhpVersion(): Version
		{
			if ($this->phpVersion === NULL) { // autodetect from composer.json => config.platform.php
				$this->phpVersion = $this->getComposerFile()->getPhpVersion();
			}

			if ($this->phpVersion === NULL) {
				$this->phpVersion = new Version(phpversion());
			}

			if ($this->phpVersion === NULL) {
				throw new \RuntimeException('PhpVersion is missing, use setPhpVersion().');
			}

			return $this->phpVersion;
		}


		public function setPhpVersion(Version $phpVersion, bool $override = FALSE): self
		{
			if (!$override && $this->phpVersion !== NULL) {
				throw new \RuntimeException('PhpVersion is already set.');
			}

			$this->phpVersion = $phpVersion;
			return $this;
		}


		public function getMaxPhpVersion(): Version
		{
			if ($this->maxPhpVersion === NULL) { // autodetect from composer.json => config.platform.php
				$this->maxPhpVersion = $this->getComposerFile()->getMaxPhpVersion();
			}

			if ($this->maxPhpVersion === NULL) {
				$this->maxPhpVersion = Version::fromString('8.4', TRUE);
			}

			return $this->maxPhpVersion;
		}


		/**
		 * @param array<string, mixed> $parameters
		 */
		public function setParameters(array $parameters): self
		{
			if ($this->parameters !== NULL) {
				throw new \RuntimeException('Parameters are already set.');
			}

			$this->parameters = new Parameters($parameters);
			return $this;
		}


		public function getParameters(): Parameters
		{
			if ($this->parameters === NULL) {
				$this->parameters = new Parameters([]);
			}

			return $this->parameters;
		}


		/**
		 * @return string[]
		 */
		public function getPaths(): array
		{
			return $this->paths;
		}


		public function addPath(string $path): self
		{
			$this->paths[] = $this->processPath($path);
			return $this;
		}


		/**
		 * @return string[]
		 */
		public function getIgnore(): array
		{
			return $this->ignore;
		}


		public function addIgnore(string $ignore): self
		{
			if (Strings::startsWith($ignore, '/')) {
				$ignore = '.' . $ignore; // /path => ./path
			}

			$this->ignore[] = rtrim($ignore, '/');
			return $this;
		}


		public function addExtension(Extension $extension): void
		{
			$this->extensions[] = $extension;
		}


		/**
		 * @return Extension[]
		 */
		public function getExtensions(): array
		{
			return $this->extensions;
		}


		public function addRule(Rule $rule): void
		{
			$this->rules[] = $rule;
		}


		/**
		 * @param  Rule[] $rules
		 */
		public function addRules(array $rules): void
		{
			$this->rules = array_merge($this->rules, $rules);
		}


		/**
		 * @return Rule[]
		 */
		public function getRules(): array
		{
			return $this->rules;
		}


		/**
		 * @return Task[]
		 */
		public function getTasks(): array
		{
			return $this->tasks;
		}


		public function addTask(callable $task, ?string $pattern = NULL): void
		{
			$this->tasks[] = new Task($task, $pattern);
		}


		private function processPath(string $path): string
		{
			if (\Nette\Utils\FileSystem::isAbsolute($path)) {
				return \CzProject\PathHelper::absolutizePath($path);
			}

			return \CzProject\PathHelper::absolutizePath($this->getProjectDirectory() . '/' . $path);
		}
	}
