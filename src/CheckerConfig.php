<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class CheckerConfig
	{
		/** @var string */
		private $configFile;

		/** @var string|NULL */
		private $projectDirectory;

		/** @var string|NULL */
		private $composerFile;

		/** @var string[] */
		private $paths = [];

		/** @var string[] */
		private $ignore = [];

		/** @var Task[] */
		private $tasks = [];

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

			$this->projectDirectory = $projectDirectory;
			return $this;
		}


		public function getComposerFile(): string
		{
			if ($this->composerFile === NULL) {
				$this->composerFile = $this->setComposerFile('./composer.json');
			}

			return $this->composerFile;
		}


		public function setComposerFile(string $composerFile): self
		{
			if ($this->composerFile !== NULL) {
				throw new \RuntimeException('ComposerFile is already set.');
			}

			$this->composerFile = $this->processPath($composerFile);
			return $this;
		}


		public function getComposerVersions(): ComposerVersions
		{
			if ($this->composerVersions === NULL) {
				$this->composerVersions = ComposerVersions::create($this->getComposerFile());
			}

			return $this->composerVersions;
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
			$this->paths[] = $path;
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
