<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	interface Processor
	{
		function getCommitMessage(): ?CommitMessage;


		function processFile(File $file): void;
	}
