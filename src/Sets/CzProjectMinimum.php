<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Sets;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Extensions;


	class CzProjectMinimum
	{
		public static function configure(CheckerConfig $config): void
		{
			$composerVersions = $config->getComposerVersions();

			\JP\CodeChecker\AutoConfig::configure($config);
			Extensions\JanpechaActionsExtension::configure($config);

			if ($composerVersions->hasPackage('nette/application')) {
				Extensions\JanpechaNetteExtension::configure($config);
			}
		}
	}
