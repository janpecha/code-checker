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


		public function processTokens(
			PhpTokens $tokens,
			Reporter $reporter
		): void
		{
		}


		public function processReflection(
			PhpSimpleAst\Reflection\FilesReflection $filesReflection,
			Reporter $reporter
		): void
		{
		}
	}
