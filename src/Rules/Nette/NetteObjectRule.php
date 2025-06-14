<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Nette;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Rules\FileContentRule;


	class NetteObjectRule implements FileContentRule
	{
		/** @var list<non-empty-string>|NULL */
		private ?array $acceptMasks;


		/**
		 * @param list<non-empty-string>|NULL $acceptMasks
		 */
		public function __construct(?array $acceptMasks = ['*.php', '*.phpt'])
		{
			$this->acceptMasks = $acceptMasks;
		}


		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage(
				subject: 'Nette: replaced deprecated Nette\\Object by Nette\\SmartObject'
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

			$fileContent->findAndReplace(
				'#(class [A-Z][a-zA-Z0-9_]+)\\sextends\\s(\\\\?Nette\\\\)Object((?:\\simplements [a-zA-Z0-9_\\\\]+){0,1}\\n(\\s*){)#m',
				"$1$3\n$4\tuse $2SmartObject;\n",
				$reporter,
				'Nette: Nette\\Object replaced by Nette\\SmartObject (deprecated in v2.4.0)'
			);
		}
	}
