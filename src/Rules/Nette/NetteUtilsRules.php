<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Nette;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Rule;


	class NetteUtilsRules
	{
		/**
		 * @return Rule[]
		 */
		public static function create(CheckerConfig $config): array
		{
			$composerVersions = $config->getComposerVersions();

			if (!$composerVersions->hasPackage('nette/utils')) {
				return [];
			}

			$version = $composerVersions->getVersion('nette/utils');

			$rules = [];

			if ($version->isEqualOrGreater('2.4.0')) {
				$rules[] = new NetteObjectRule;
			}

			return $rules;
		}
	}
