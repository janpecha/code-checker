<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	interface Processor
	{
		function getCommitMessage(): ?CommitMessage;


		function processContent(
			FileContent $fileContent,
			Reporter $reporter
		): void;
	}
