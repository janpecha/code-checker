<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class ResultMessage
	{
		public function __construct(
			public readonly ResultType $type,
			public readonly string $message,
			public readonly ?int $line = NULL
		)
		{
		}
	}
