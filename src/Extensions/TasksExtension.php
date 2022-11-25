<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker\Engine;
	use JP\CodeChecker\Extension;
	use JP\CodeChecker\Task;


	class TasksExtension implements Extension
	{
		/** @var string[] */
		private $acceptMasks;

		/** @var Task[] */
		private $tasks;


		/**
		 * @param  string[] $acceptMasks
		 * @param  Task[] $tasks
		 */
		public function __construct(
			array $acceptMasks,
			array $tasks
		)
		{
			$this->acceptMasks = $acceptMasks;
			$this->tasks = $tasks;
		}


		public function run(Engine $engine): bool
		{
			$success = TRUE;

			if ($engine->isStepByStep()) {
				foreach ($this->tasks as $task) {
					foreach ($engine->findFiles($this->acceptMasks) as $file) {
						$engine->progress();
						$file = (string) $file;
						$success = $this->processFile([$task], $file, $engine) && $success;
					}

					if (!$success) {
						break;
					}
				}

			} else {
				foreach ($engine->findFiles($this->acceptMasks) as $file) {
					$engine->progress();

					$file = (string) $file;
					$success = $this->processFile($this->tasks, $file, $engine) && $success;
				}
			}

			return $success;
		}


		/**
		 * @param  Task[] $tasks
		 */
		private function processFile(
			array $tasks,
			string $file,
			Engine $engine
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

				$result = new \JP\CodeChecker\Result;
				$contents = $lastContents;
				$handler($contents, $result);

				foreach ($result->getMessages() as $result) {
					[$type, $message, $line] = $result;
					if ($type === \JP\CodeChecker\Result::ERROR) {
						$engine->reportErrorInFile($message, $file, $line);
						$error = TRUE;

					} elseif ($type === \JP\CodeChecker\Result::WARNING) {
						$engine->reportWarningInFile($message, $file, $line);

					} elseif ($type === \JP\CodeChecker\Result::FIX) {
						$engine->reportFixInFile($message, $file, $line);
						$error = $error || $engine->isReadOnly();
						$stepByStepFix = $stepByStepFix || $engine->isStepByStep();
					}
				}

				if (!$error) {
					$lastContents = $contents;
				}
			}

			if ($lastContents !== $origContents && !$engine->isReadOnly()) {
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
	}
