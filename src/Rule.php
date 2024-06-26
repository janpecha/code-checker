<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	interface Rule
	{
		function processContent(
			FileContent $fileContent,
			Reporter $reporter
		): void;
	}
