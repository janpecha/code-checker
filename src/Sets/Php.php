<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Sets;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Extensions;
	use JP\CodeChecker\Rules;


	class Php
	{
		public static function configure(CheckerConfig $config): void
		{
			$rules = array_merge(
				Rules\Nette\NetteRules::create($config)
			);

			$config->addExtension(new Extensions\PhpExtension(
				['*.php', '*.phpt'],
				$rules
			));
		}
	}
