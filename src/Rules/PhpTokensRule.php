<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules;

	use JP\CodeChecker\PhpTokens;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Rule;


	interface PhpTokensRule extends Rule
	{
		function processPhpTokens(
			PhpTokens $tokens,
			Reporter $reporter
		): void;
	}
