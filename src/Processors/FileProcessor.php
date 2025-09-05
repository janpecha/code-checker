<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Processors;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Processor;
	use JP\CodeChecker\Rules\FileRule;


	class FileProcessor implements Processor
	{
		/** @var FileRule[] */
		private array $rules;


		/**
		 * @param FileRule[] $rules
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


		public function processFile(File $file): void
		{
			foreach ($this->rules as $rule) {
				$rule->processFile($file);
			}
		}
	}
