<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Sets;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Extensions;
	use JP\CodeChecker\Version;


	class CzProjectMinimum
	{
		public static function configure(CheckerConfig $config): void
		{
			if ($config->getComposerFile()->isLibrary()) {
				$currentMinimalVersion = $config->getPhpVersion();
				$minimalRequiredVersion = new Version('8.0.0');

				if (!$currentMinimalVersion->isEqualOrGreater($minimalRequiredVersion)) {
					$config->setPhpVersion($minimalRequiredVersion, override: TRUE);
				}
			}

			$composerVersions = $config->getComposerVersions();

			\JP\CodeChecker\AutoConfig::configure($config);
			Extensions\JanpechaActionsExtension::configure($config);
			Extensions\JanpechaReadmeExtension::configure($config);
			Extensions\ComposerExtension::configure($config);

			if ($composerVersions->hasPackage('nette/application')) {
				Extensions\JanpechaNetteExtension::configure($config);
			}
		}
	}
