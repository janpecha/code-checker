<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class Checker
	{
		/** @var string[] */
		private $paths = [];

		/** @var string[] */
		private $accept = [];

		/** @var string[] */
		private $ignore = [];

		/** @var Task[] */
		private $tasks = [];


		/**
		 * @param string[] $paths
		 * @param string[] $accept
		 * @param string[] $ignore
		 * @param Task[] $tasks
		 */
		public function __construct(
			array $paths,
			array $accept,
			array $ignore,
			array $tasks
		)
		{
			$this->paths = $paths;
			$this->accept = $accept;
			$this->ignore = $ignore;
			$this->tasks = $tasks;
		}


		public function run(
			bool $readOnly,
			bool $stepByStep,
			bool $showProgress
		): bool
		{
			$console = new \Nette\CommandLine\Console;

			if ($readOnly) {
				echo "Running in read-only mode\n";
			}

			echo "Scanning {$console->color('white', implode(', ', $this->paths))}\n";

			$counter = 0;
			$success = TRUE;

			if ($stepByStep) {
				foreach ($this->tasks as $task) {
					$iterator = $this->createFileIterator();

					foreach ($iterator as $file) {
						if ($showProgress) {
							echo str_pad(str_repeat('.', $counter++ % 40), 40), "\x0D";
						}

						$file = (string) $file;
						$success = $this->processFile([$task], $file, $readOnly, $stepByStep, $console) && $success;
					}

					if (!$success) {
						break;
					}
				}

			} else {
				$iterator = $this->createFileIterator();

				foreach ($iterator as $file) {
					if ($showProgress) {
						echo str_pad(str_repeat('.', $counter++ % 40), 40), "\x0D";
					}

					$file = (string) $file;
					$success = $this->processFile($this->tasks, $file, $readOnly, $stepByStep, $console) && $success;
				}
			}

			if ($showProgress) {
				echo str_pad('', 40), "\x0D";
			}

			echo "Done.\n";
			return $success;
		}


		private function createFileIterator(): \AppendIterator
		{
			$iterator = new \AppendIterator;

			foreach ($this->paths as $path) {
				$iterator->append(
					is_file($path)
					? new \ArrayIterator([$path])
					: \Nette\Utils\Finder::findFiles($this->accept)
						->exclude($this->ignore)
						->from($path)
						->exclude($this->ignore)
						->getIterator()
				);
			}

			return $iterator;
		}


		/**
		 * @param  Task[] $tasks
		 */
		private function processFile(
			array $tasks,
			string $file,
			bool $readOnly,
			bool $stepByStep,
			\Nette\CommandLine\Console $console
		): bool
		{
			$error = FALSE;
			$stepByStepFix = FALSE;
			$origContents = $lastContents = file_get_contents($file);

			foreach ($tasks as $task) {
				$handler = $task->getHandler();
				$pattern = $task->getPattern();

				if ($pattern && !$this->matchFileName($pattern, basename($file))) {
					continue;
				}

				$result = new \Nette\CodeChecker\Result;
				$contents = $lastContents;
				$handler($contents, $result);

				foreach ($result->getMessages() as $result) {
					[$type, $message, $line] = $result;
					if ($type === \Nette\CodeChecker\Result::ERROR) {
						$this->write($console, $file, 'ERROR', $message, $line, 'red');
						$error = TRUE;

					} elseif ($type === \Nette\CodeChecker\Result::WARNING) {
						$this->write($console, $file, 'WARNING', $message, $line, 'yellow');

					} elseif ($type === \Nette\CodeChecker\Result::FIX) {
						$this->write($console, $file, $readOnly ? 'FOUND' : 'FIX', $message, $line, 'aqua');
						$error = $error || $readOnly;
						$stepByStepFix = $stepByStepFix || $stepByStep;
					}
				}

				if (!$error) {
					$lastContents = $contents;
				}
			}

			if ($lastContents !== $origContents && !$readOnly) {
				file_put_contents($file, $lastContents);
			}

			return !$error && !$stepByStepFix;
		}


		private function matchFileName(string $pattern, string $name): bool
		{
			$neg = substr($pattern, 0, 1) === '!';
			foreach (explode(',', ltrim($pattern, '!')) as $part) {
				if (fnmatch($part, $name, FNM_CASEFOLD)) {
					return !$neg;
				}
			}
			return $neg;
		}


		private function write(
			\Nette\CommandLine\Console $console,
			string $relativePath,
			string $type,
			string $message,
			?int $line,
			string $color
		): void
		{
			$base = basename($relativePath);
			echo $console->color($color, str_pad("[$type]", 10)),
				$base === $relativePath ? '' : $console->color('silver', dirname($relativePath) . DIRECTORY_SEPARATOR),
				$console->color('white', $base . ($line ? ':' . $line : '')), '    ',
				$console->color($color, $message), "\n";
		}
	}
