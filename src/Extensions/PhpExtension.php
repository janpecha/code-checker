<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker\Engine;
	use JP\CodeChecker\Extension;
	use JP\CodeChecker\Processors\PhpProcessor;
	use JP\CodeChecker\Rules\PhpTokensRule;


	class PhpExtension implements Extension
	{
		/** @var non-empty-string[] */
		private $acceptMasks;


		/**
		 * @param  non-empty-string[] $acceptMasks
		 */
		public function __construct(
			array $acceptMasks,
		)
		{
			$this->acceptMasks = $acceptMasks;
		}


		public function run(Engine $engine): void
		{
		}


		public function createRules(): array
		{
			return [];
		}


		public function createProcessors(array $rules): array
		{
			$tokensRules = [];

			foreach ($rules as $rule) {
				if ($rule instanceof PhpTokensRule) {
					$tokensRules[] = $rule;
				}
			}

			if (count($tokensRules) > 0) {
				return [
					new PhpProcessor(
						$this->acceptMasks,
						$tokensRules,
					),
				];
			}

			return [];
		}
	}
