<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class Checker
	{
		/** @var string */
		private $projectDirectory;

		/** @var string[] */
		private $paths = [];

		/** @var string[] */
		private $ignore = [];

		/** @var Extension[] */
		private $extensions = [];


		/**
		 * @param string[] $paths
		 * @param string[] $ignore
		 * @param Extension[] $extensions
		 */
		public function __construct(
			string $projectDirectory,
			array $paths,
			array $ignore,
			array $extensions
		)
		{
			$this->projectDirectory = $projectDirectory;
			$this->paths = $paths;
			$this->ignore = $ignore;
			$this->extensions = $extensions;
		}


		public function run(
			bool $readOnly,
			bool $stepByStep,
			bool $showProgress
		): bool
		{
			$console = new \Nette\CommandLine\Console;
			$progressBar = new ProgressBar($showProgress);
			$engine = new Engine(
				$this->projectDirectory,
				$this->paths,
				$this->ignore,
				$readOnly,
				$stepByStep,
				$progressBar,
				$console
			);

			if ($readOnly) {
				echo "Running in read-only mode\n";
			}

			echo "Project directory: {$console->color('white', $this->projectDirectory)}\n";
			echo "Scanning {$console->color('white', implode(', ', $this->paths))}\n";
			$success = TRUE;

			foreach ($this->extensions as $extension) {
				$success = $extension->run($engine) && $success;
				$progressBar->reset();

				if ($stepByStep && !$success) {
					return FALSE;
				}
			}

			echo "Done.\n";
			return $success;
		}
	}
