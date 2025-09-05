<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker;
	use JP\CodeChecker\Processors\FileProcessor;
	use JP\CodeChecker\Rules\FileRule;


	class CoreExtension implements CodeChecker\Extension
	{
		public function run(CodeChecker\Engine $engine): void
		{
		}


		public function createRules(): array
		{
			return [];
		}


		public function createProcessors(array $rules): array
		{
			$filteredRules = [];

			foreach ($rules as $rule) {
				if ($rule instanceof FileRule) {
					$filteredRules[] = $rule;
				}
			}

			if (count($filteredRules) > 0) {
				return [
					new FileProcessor($filteredRules),
				];
			}

			return [];
		}
	}
