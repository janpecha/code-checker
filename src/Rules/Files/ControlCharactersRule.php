<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Files;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\FileRule;
	use Nette\Utils\Strings;


	class ControlCharactersRule implements FileRule
	{
		/** @var list<non-empty-string>|NULL */
		private ?array $acceptMasks;


		/**
		 * @param list<non-empty-string>|NULL $acceptMasks
		 */
		public function __construct(?array $acceptMasks = NULL)
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

			if ($m = Strings::match($file->contents, '#[\x00-\x08\x0B\x0C\x0E-\x1F]#', PREG_OFFSET_CAPTURE)) {
				$file->reportError('Contains control characters', $file->convertOffsetToLine($m[0][1]));
			}
		}
	}
