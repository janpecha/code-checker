<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	interface Rule
	{
		function getCommitMessage(): ?CommitMessage;
	}
