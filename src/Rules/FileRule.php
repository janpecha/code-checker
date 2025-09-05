<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules;

	use JP\CodeChecker\File;
	use JP\CodeChecker\Rule;


	interface FileRule extends Rule
	{
		function processFile(
			File $file
		): void;
	}
