<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use CzProject\PhpSimpleAst;


	abstract class PhpRule implements Rule
	{
		public function processContent(
			FileContent $fileContent,
			Reporter $reporter
		): void
		{
		}


		/**
		 * @param  array<string|array{0: int, 1: string, 2: int}> $tokens
		 * @return array<string|array{0: int, 1: string, 2: int}>
		 */
		public function processTokens(
			array $tokens,
			Reporter $reporter
		): array
		{
			return $tokens;
		}


		public function processReflection(
			PhpSimpleAst\Reflection\FilesReflection $filesReflection,
			Reporter $reporter
		): void
		{
		}
	}
