<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Php;

	use CzProject\PhpSimpleAst;
	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\PhpReflectionRule;


	class PhpDocParamFixerRule implements PhpReflectionRule
	{
		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage(
				subject: 'Fixed PHPDoc @param'
			);
		}


		public function processPhpReflection(File $file, PhpSimpleAst\Reflection\Reflection $phpReflection): void
		{
			PhpSimpleAst\Refactor\PhpDocParamFixer::processClasses($phpReflection->getClasses()); // TODO reportFix
		}
	}
