<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use JP\CodeChecker\CheckerConfig;


	class AutoConfig
	{
		public static function configure(CheckerConfig $config): void
		{
			$config->addExtension(new Extensions\CoreExtension);
			Tasks\Files::configure($config);
			Tasks\Php::configure($config);
			Sets\Php::configure($config);

			$composerVersions = $config->getComposerVersions();

			if ($composerVersions->hasPackage('nette/application')) {
				Extensions\NetteApplicationExtension::configure($config, $composerVersions->getVersion('nette/application'));
			}

			Rules\Neon\NeonRules::create($config);

			if ($composerVersions->hasPackage('latte/latte')) {
				Extensions\LatteExtension::configure($config, $composerVersions->getVersion('latte/latte'));
			}
		}
	}
