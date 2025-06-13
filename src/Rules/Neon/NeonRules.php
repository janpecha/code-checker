<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Neon;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Rule;


	class NeonRules
	{
		/**
		 * @return Rule[]
		 */
		public static function create(CheckerConfig $config): array
		{
			$composerVersions = $config->getComposerVersions();

			if (!$composerVersions->hasPackage('nette/neon')) {
				return [];
			}

			return [
				new NeonKeywordsRule,
				new NeonSyntaxRule,
			];
		}
	}
