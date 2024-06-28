<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker\Engine;
	use JP\CodeChecker\Extension;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\PhpRule;
	use JP\CodeChecker\PhpTokens;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Utils;


	class PhpExtension implements Extension
	{
		/** @var string[] */
		private $acceptMasks;

		/** @var PhpRule[] */
		private $rules;


		/**
		 * @param  string[] $acceptMasks
		 * @param  PhpRule[] $rules
		 */
		public function __construct(
			array $acceptMasks,
			array $rules
		)
		{
			$this->acceptMasks = $acceptMasks;
			$this->rules = $rules;
		}


		public function run(Engine $engine): void
		{
			if (count($this->rules) === 0) {
				return;
			}

			$files = $engine->findFiles($this->acceptMasks);
			$filesReflection = NULL;

			if ($engine->isStepByStep()) {
				foreach ($this->rules as $rule) {
					foreach ($files as $file) {
						$engine->processFiles(
							[$file],
							function (FileContent $fileContent, Reporter $reporter) use ($rule) {
								$this->processFileContent($rule, $fileContent, $reporter);
							}
						);
					}

					if (!$engine->isSuccess()) {
						return;
					}
				}

				$filesReflection = Utils\PhpReflection::scanFiles($files, [$engine, 'progress']);

			} else {
				$astParser = new \CzProject\PhpSimpleAst\AstParser;
				$phpFiles = [];

				$engine->processFiles(
					$files,
					function (FileContent $fileContent, Reporter $reporter) use ($astParser, &$phpFiles) {
						foreach ($this->rules as $rule) {
							$this->processFileContent($rule, $fileContent, $reporter);
						}

						$phpFiles[] = Utils\PhpReflection::createFromFileContent($astParser, $fileContent);
					}
				);

				$filesReflection = new \CzProject\PhpSimpleAst\Reflection\FilesReflection($phpFiles);
			}

			foreach ($this->rules as $rule) {
				$rule->processReflection($filesReflection, $engine);
			}

			Utils\PhpReflection::saveFiles($engine, $filesReflection);

			$engine->commit('PHP: code fixes');
		}


		private function processFileContent(
			PhpRule $rule,
			FileContent $fileContent,
			Reporter $reporter
		): void
		{
			$rule->processContent($fileContent, $reporter);

			$tokens = PhpTokens::fromString($fileContent->contents);
			$rule->processTokens($tokens, $reporter);
			$fileContent->contents = (string) $tokens;
		}
	}
