<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Neon;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\FileRule;


	class NeonKeywordsRule implements FileRule
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


		public function processFile(
			File $file
		): void
		{
			if ($this->acceptMasks !== NULL && !$file->matchName($this->acceptMasks)) {
				return;
			}

			$oldContents = $file->contents;

			$file->findAndReplace('#(:\\s)on$#m', '$1yes');
			$file->findAndReplace('#(:\\s)off$#m', '$1no');

			$newContents = $file->contents;

			if ($oldContents !== $newContents) {
				$file->reportFix('Neon: keywords on/off changed to yes/no (deprecated in v3.1)');
			}
		}
	}
