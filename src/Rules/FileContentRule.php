<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules;

	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Rule;


	interface FileContentRule extends Rule
	{
		function processContent(
			FileContent $fileContent,
			Reporter $reporter
		): void;
	}
