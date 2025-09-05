<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	enum ResultType: string
	{
		case Error = 'error';
		case Fix = 'fix';
		case Warning = 'warning';
	}
