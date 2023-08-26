<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class CheckerConfig
	{
		/** @var string */
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
		private $scannedPaths = [];

		/** @var string[] */
		private $ignore = [];

		/** @var Extension[] */
		private $extensions = [];

		/** @var Task[] */
		private $tasks = [];

		/** @var Parameters|NULL */
		private $parameters;

		/** @var Version|NULL */
		private $phpVersion;

		/** @var ComposerVersions|NULL */
		private $composerVersions;


		public function __construct(string $configFile)
		{
			$this->configFile = $configFile;
		}


		public function getProjectDirectory(): string
		{
			if ($this->projectDirectory === NULL) {
				$this->projectDirectory = dirname($this->configFile);
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

				$this->composerFile = ComposerFile::open($path);
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
				throw new \RuntimeException('PhpVersion is missing, use setPhpVersion().');
			}

			return $this->phpVersion;
		}


		public function setPhpVersion(Version $phpVersion): self
		{
			if ($this->phpVersion !== NULL) {
				throw new \RuntimeException('PhpVersion is already set.');
			}

			$this->phpVersion = $phpVersion;
			return $this;
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
		public function getScannedPaths(): array
		{
			if (count($this->scannedPaths) === 0) {
				$vendorDir = dirname($this->getComposerFile()->getPath()) . '/vendor';

				if (is_dir($vendorDir)) {
					return [$vendorDir];
				}
			}

			return $this->scannedPaths;
		}


		public function addScannedPath(string $path): self
		{
			$this->scannedPaths[] = $this->processPath($path);
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
			$this->ignore[] = $ignore;
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


		/**
		 * @return Task[]
		 */
		public function getTasks(): array
		{
			return $this->tasks;
		}


		public function addTask(callable $task, string $pattern = NULL): void
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
