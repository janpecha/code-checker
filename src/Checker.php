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
		private $accept = [];

		/** @var string[] */
		private $ignore = [];

		/** @var Extension[] */
		private $extensions = [];

		/** @var Rule[] */
		private $rules = [];

		/** @var GitPhp\Git|NULL */
		private $git;


		/**
		 * @param string[] $paths
		 * @param string[] $scannedPaths
		 * @param string[] $accept
		 * @param string[] $ignore
		 * @param Extension[] $extensions
		 * @param Rule[] $rules
		 */
		public function __construct(
			string $projectDirectory,
			array $paths,
			array $scannedPaths,
			array $ignore,
			array $extensions,
			?GitPhp\Git $git = NULL,
			array $accept = [],
			array $rules = [],
		)
		{
			$this->projectDirectory = $projectDirectory;
			$this->paths = $paths;
			$this->scannedPaths = $scannedPaths;
			$this->accept = $accept;
			$this->ignore = $ignore;
			$this->extensions = $extensions;
			$this->rules = $rules;
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

			$rules = $this->rules;

			foreach ($this->extensions as $extension) {
				$progressBar->progress();
				$rules = array_merge(
					$rules,
					$extension->createRules()
				);
			}

			$processors = [];

			foreach ($this->extensions as $extension) {
				if ($stepByStep || $gitSupport) {
					foreach ($rules as $rule) {
						$processors = array_merge(
							$processors,
							$extension->createProcessors([$rule])
						);
					}

				} else {
					$processors = array_merge(
						$processors,
						$extension->createProcessors($rules)
					);
				}
			}

			$progressBar->reset();
			$files = $engine->findFiles($this->accept);

			if ($stepByStep || $gitSupport) {
				foreach ($processors as $processor) {
					$progressBar->reset();
					$commitMessage = $processor->getCommitMessage();

					if ($commitMessage !== NULL && $gitRepository !== NULL && $gitRepository->hasChanges()) {
						$engine->commit('CodeChecker fixes');
					}

					foreach ($files as $file) {
						$progressBar->progress();
						$this->processFile(
							$engine,
							(string) $file,
							[$processor]
						);
					}

					$success = $engine->isSuccess() && $success;

					if ($engine->isStepByStep() && !$success) {
						return FALSE;
					}

					if ($commitMessage !== NULL) {
						$engine->commit((string) $commitMessage);
					}
				}

			} else {
				foreach ($files as $file) {
					$progressBar->progress();
					$this->processFile(
						$engine,
						(string) $file,
						$processors
					);
				}

				$success = $engine->isSuccess() && $success;
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


		/**
		 * @param  Processor[] $processors
		 */
		private function processFile(
			Engine $engine,
			string $path,
			array $processors
		): void
		{
			$file = File::fromFile($path);

			foreach ($processors as $processor) {
				$processor->processFile($file);
			}

			foreach ($file->getResult() as $resultMessage) {
				if ($resultMessage->type === ResultType::Fix) {
					$engine->reportFixInFile(
						$resultMessage->message,
						$path,
						$resultMessage->line
					);

				} elseif ($resultMessage->type === ResultType::Error) {
					$engine->reportErrorInFile(
						$resultMessage->message,
						$path,
						$resultMessage->line
					);

				} elseif ($resultMessage->type === ResultType::Warning) {
					$engine->reportWarningInFile(
						$resultMessage->message,
						$path,
						$resultMessage->line
					);

				} else {
					throw new \RuntimeException("Unknow message type.");
				}
			}

			if ($file->wasChanged()) {
				$engine->writeFile($file, (string) $file);
			}
		}


		private function createGitRepository(): GitPhp\GitRepository
		{
			if ($this->git === NULL) {
				throw new \RuntimeException('Missing Git factory.');
			}

			return $this->git->open($this->projectDirectory);
		}
	}
