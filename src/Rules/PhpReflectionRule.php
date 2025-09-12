<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules;

	use CzProject\PhpSimpleAst\Reflection;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rule;


	interface PhpReflectionRule extends Rule
	{
		function processPhpReflection(
			File $file,
			Reflection\Reflection $phpReflection
		): void;
	}
