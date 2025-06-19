<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Files;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\FileRule;
	use JP\CodeChecker\Task;


	class FileTaskRule implements FileRule
	{
		private Task $task;


		public function __construct(Task $task)
		{
			$this->task = $task;
		}


		public function getCommitMessage(): ?CommitMessage
		{
			return NULL;
		}


		public function processFile(
			File $file
		): void
		{
			$pattern = $this->task->getPattern();
			$path = $file->getPath();

			if ($pattern && !$this->matchFileName($pattern, basename($path))) {
				return;
			}

			call_user_func($this->task->getHandler(), $file);
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
