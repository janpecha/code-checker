<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Files;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Rules\FileContentRule;
	use Nette\Utils\Strings;


	class ControlCharactersRule implements FileContentRule
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


		public function processContent(
			FileContent $fileContent,
			Reporter $reporter
		): void
		{
			if ($this->acceptMasks !== NULL && !$fileContent->matchName($this->acceptMasks)) {
				return;
			}

			if ($m = Strings::match($fileContent->contents, '#[\x00-\x08\x0B\x0C\x0E-\x1F]#', PREG_OFFSET_CAPTURE)) {
				$reporter->reportErrorInFile('Contains control characters', $fileContent->convertOffsetToLine($m[0][1]));
			}
		}
	}
