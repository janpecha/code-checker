<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Neon;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Rules\FileContentRule;


	class NeonKeywordsRule implements FileContentRule
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
			return new CommitMessage(
				subject: 'Neon: fixed deprecated keywords on/off'
			);
		}


		public function processContent(
			FileContent $fileContent,
			Reporter $reporter
		): void
		{
			if ($this->acceptMasks !== NULL && !$fileContent->matchName($this->acceptMasks)) {
				return;
			}

			$oldContents = $fileContent->contents;

			$fileContent->findAndReplace('#(:\\s)on$#m', '$1yes');
			$fileContent->findAndReplace('#(:\\s)off$#m', '$1no');

			$newContents = $fileContent->contents;

			if ($oldContents !== $newContents) {
				$reporter->reportFixInFile('Neon: keywords on/off changed to yes/no (deprecated in v3.1)', $fileContent->getFile());
			}
		}
	}
