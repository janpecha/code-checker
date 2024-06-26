<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use CzProject\GitPhp;


	class Checker
	{
		/** @var string */
		private $projectDirectory;

		/** @var string[] */
		private $paths = [];

		/** @var string[] */
		private $scannedPaths = [];

		/** @var string[] */
		private $ignore = [];

		/** @var Extension[] */
		private $extensions = [];

		/** @var GitPhp\Git|NULL */
		private $git;


		/**
		 * @param string[] $paths
		 * @param string[] $scannedPaths
		 * @param string[] $ignore
		 * @param Extension[] $extensions
		 */
		public function __construct(
			string $projectDirectory,
			array $paths,
			array $scannedPaths,
			array $ignore,
			array $extensions,
			?GitPhp\Git $git = NULL
		)
		{
			$this->projectDirectory = $projectDirectory;
			$this->paths = $paths;
			$this->scannedPaths = $scannedPaths;
			$this->ignore = $ignore;
			$this->extensions = $extensions;
			$this->git = $git;
		}


		public function run(
			bool $readOnly,
			bool $stepByStep,
			bool $showProgress,
			bool $gitSupport
		): bool
		{
			$console = new \Nette\CommandLine\Console;
			$progressBar = new ProgressBar($showProgress);
			$gitRepository = $gitSupport ? $this->createGitRepository() : NULL;
			$engine = new Engine(
				$this->projectDirectory,
				$this->paths,
				$this->scannedPaths,
				$this->ignore,
				$readOnly,
				$stepByStep,
				$progressBar,
				$console,
				$gitRepository
			);

			if ($readOnly) {
				echo "Running in read-only mode\n";
			}

			if ($gitSupport) {
				echo "Enabled GIT support\n";
			}

			echo "Project directory: {$console->color('white', $this->projectDirectory)}\n";
			echo "Scanning {$console->color('white', implode(', ', $this->paths))}\n";
			$success = TRUE;
			$startTime = microtime(TRUE);

			foreach ($this->extensions as $extension) {
				$extension->run($engine);
				$success = $engine->isSuccess() && $success;
				$progressBar->reset();

				if ($gitRepository !== NULL && $gitRepository->hasChanges()) {
					$engine->commit('CodeChecker fixes');
				}

				if ($stepByStep && !$success) {
					return FALSE;
				}
			}

			echo "Done ";
			echo '(';
			echo 'finished in ', round(microtime(TRUE) - $startTime, 2) . ' secs';
			echo ', ';
			echo 'used ', round(memory_get_peak_usage() / 1000 / 1000, 2), ' MB of memory';
			echo ')';
			echo "\n";
			return $success;
		}


		private function createGitRepository(): GitPhp\GitRepository
		{
			if ($this->git === NULL) {
				throw new \RuntimeException('Missing Git factory.');
			}

			return $this->git->open($this->projectDirectory);
		}
	}
