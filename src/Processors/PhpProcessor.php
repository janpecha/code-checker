<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Processors;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\PhpTokens;
	use JP\CodeChecker\Processor;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Rules\PhpTokensRule;


	class PhpProcessor implements Processor
	{
		/** @var non-empty-string[] */
		private array $acceptMasks;

		/** @var PhpTokensRule[] */
		private array $tokensRules;


		/**
		 * @param non-empty-string[] $acceptMasks
		 * @param PhpTokensRule[] $tokensRules
		 */
		public function __construct(
			array $acceptMasks,
			array $tokensRules
		)
		{
			$this->acceptMasks = $acceptMasks;
			$this->tokensRules = $tokensRules;
		}


		public function getCommitMessage(): ?CommitMessage
		{
			if (count($this->tokensRules) === 1) {
				foreach ($this->tokensRules as $tokensRule) {
					return $tokensRule->getCommitMessage();
				}
			}

			return NULL;
		}


		public function processContent(FileContent $fileContent, Reporter $reporter): void
		{
			if (!$fileContent->matchName($this->acceptMasks)) {
				return;
			}

			$tokens = PhpTokens::fromString($fileContent->contents);

			foreach ($this->tokensRules as $tokensRule) {
				$tokensRule->processPhpTokens(
					$tokens,
					$reporter
				);
			}

			$fileContent->contents = (string) $tokens;
		}
	}
