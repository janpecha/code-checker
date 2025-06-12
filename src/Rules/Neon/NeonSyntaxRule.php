<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Neon;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Rules\FileContentRule;


	class NeonSyntaxRule implements FileContentRule
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


		public function processContent(
			FileContent $fileContent,
			Reporter $reporter
		): void
		{
			if ($this->acceptMasks !== NULL && !$fileContent->matchName($this->acceptMasks)) {
				return;
			}

			try {
				\Nette\Neon\Neon::decode($fileContent->contents);

			} catch (\Nette\Neon\Exception $e) {
				$line = preg_match('# on line (\d+)#', $e->getMessage(), $m) ? (int) $m[1] : null;
				$reporter->reportErrorInFile($e->getMessage(), $fileContent->getFile(), $line);
			}
		}
	}
