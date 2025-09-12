<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Processors;

	use CzProject\PhpSimpleAst;
	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\PhpTokens;
	use JP\CodeChecker\Processor;
	use JP\CodeChecker\Rules\PhpReflectionRule;
	use JP\CodeChecker\Rules\PhpTokensRule;


	class PhpProcessor implements Processor
	{
		/** @var non-empty-string[] */
		private array $acceptMasks;

		/** @var PhpTokensRule[] */
		private array $tokensRules;

		/** @var PhpReflectionRule[] */
		private array $reflectionRules;

		private ?PhpSimpleAst\AstParser $astParser = NULL;


		/**
		 * @param non-empty-string[] $acceptMasks
		 * @param PhpTokensRule[] $tokensRules
		 * @param PhpReflectionRule[] $reflectionRules
		 */
		public function __construct(
			array $acceptMasks,
			array $tokensRules,
			array $reflectionRules
		)
		{
			$this->acceptMasks = $acceptMasks;
			$this->tokensRules = $tokensRules;
			$this->reflectionRules = $reflectionRules;
		}


		public function getCommitMessage(): ?CommitMessage
		{
			if ((count($this->tokensRules) + count($this->reflectionRules)) === 1) {
				foreach ($this->tokensRules as $tokensRule) {
					return $tokensRule->getCommitMessage();
				}

				foreach ($this->reflectionRules as $reflectionsRule) {
					return $reflectionsRule->getCommitMessage();
				}
			}

			return NULL;
		}


		public function processFile(File $file): void
		{
			if (!$file->matchName($this->acceptMasks)) {
				return;
			}

			if (count($this->tokensRules) > 0) {
				$tokens = PhpTokens::fromString($file->contents);

				foreach ($this->tokensRules as $tokensRule) {
					$tokensRule->processPhpTokens(
						$file,
						$tokens
					);
				}

				$file->contents = (string) $tokens;
			}

			if (count($this->reflectionRules) > 0) {
				$source = $this->createPhpSource($file->contents);
				$reflection = new PhpSimpleAst\Reflection\Reflection([$source]);

				foreach ($this->reflectionRules as $reflectionRule) {
					$reflectionRule->processPhpReflection(
						$file,
						$reflection
					);
				}

				$file->contents = $source->toString();
			}
		}


		private function createPhpSource(string $code): PhpSimpleAst\Ast\PhpString
		{
			if ($this->astParser === NULL) {
				$this->astParser = new PhpSimpleAst\AstParser;
			}

			return $this->astParser->parseString($code);
		}
	}
