<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Sets;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Extensions;


	class Php
	{
		public static function configure(CheckerConfig $config): void
		{
			$rules = [];

			$config->addExtension(new Extensions\PhpExtension(
				['*.php', '*.phpt'],
				$rules
			));
		}
	}
