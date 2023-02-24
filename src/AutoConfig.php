<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use JP\CodeChecker\CheckerConfig;


	class AutoConfig
	{
		public static function configure(CheckerConfig $config): void
		{
			Tasks\Files::configure($config);
			Tasks\Php::configure($config);

			$composerVersions = $config->getComposerVersions();

			if ($composerVersions->hasPackage('nette/application')) {
				Tasks\NetteApplication::configure($config, $composerVersions->getVersion('nette/application'));
			}

			if ($composerVersions->hasPackage('nette/utils')) {
				Tasks\NetteUtils::configure($config, $composerVersions->getVersion('nette/utils'));
			}

			if ($composerVersions->hasPackage('nette/neon')) {
				Tasks\Neon::configure($config);
			}

			if ($composerVersions->hasPackage('latte/latte')) {
				Tasks\Latte::configure($config, $composerVersions->getVersion('latte/latte'));
			}
		}
	}
