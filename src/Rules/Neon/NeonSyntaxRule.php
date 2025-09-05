<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Neon;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\FileRule;


	class NeonSyntaxRule implements FileRule
	{
		/** @var list<non-empty-string>|NULL */
		private ?array $acceptMasks;


		/**
		 * @param list<non-empty-string>|NULL $acceptMasks
		 */
		public function __construct(?array $acceptMasks = ['*.neon'])
		{
			$this->acceptMasks = $acceptMasks;
		}


		public function getCommitMessage(): ?CommitMessage
		{
			return NULL;
		}


		public function processFile(
			File $file
		): void
		{
			if ($this->acceptMasks !== NULL && !$file->matchName($this->acceptMasks)) {
				return;
			}

			try {
				\Nette\Neon\Neon::decode($file->contents);

			} catch (\Nette\Neon\Exception $e) {
				$line = preg_match('# on line (\d+)#', $e->getMessage(), $m) ? (int) $m[1] : null;
				$file->reportError($e->getMessage(), $line);
			}
		}
	}
