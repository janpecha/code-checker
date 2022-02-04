<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;


	class AutoConfig
	{
		public static function configure(CheckerConfig $config): void
		{
			Files::configure($config);
			Php::configure($config);

			$composerVersions = $config->getComposerVersions();

			if ($composerVersions->hasPackage('nette/application')) {
				NetteApplication::configure($config, $composerVersions->getVersion('nette/application'));
			}

			if ($composerVersions->hasPackage('nette/utils')) {
				NetteUtils::configure($config, $composerVersions->getVersion('nette/utils'));
			}

			if ($composerVersions->hasPackage('nette/neon')) {
				Neon::configure($config);
			}

			if ($composerVersions->hasPackage('latte/latte')) {
				Latte::configure($config, $composerVersions->getVersion('latte/latte'));
			}
		}
	}
