<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Processors;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\Processor;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Rules\FileContentRule;


	class FileContentProcessor implements Processor
	{
		/** @var FileContentRule[] */
		private array $rules;


		/**
		 * @param FileContentRule[] $rules
		 */
		public function __construct(array $rules)
		{
			$this->rules = $rules;
		}


		public function getCommitMessage(): ?CommitMessage
		{
			if (count($this->rules) === 1) {
				foreach ($this->rules as $rule) {
					return $rule->getCommitMessage();
				}
			}

			return NULL;
		}


		public function processContent(FileContent $fileContent, Reporter $reporter): void
		{
			foreach ($this->rules as $rule) {
				$rule->processContent(
					$fileContent,
					$reporter
				);
			}
		}
	}
