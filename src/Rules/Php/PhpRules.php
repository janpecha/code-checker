<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Php;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Rule;


	class PhpRules
	{
		/**
		 * @return Rule[]
		 */
		public static function create(CheckerConfig $config): array
		{
			$phpVersion = $config->getPhpVersion();
			$params = $config->getParameters();
			$rules = [];

			if ($phpVersion->isEqualOrGreater('7.2.0') && $params->toBool('php.strictTypes', TRUE)) {
				$rules[] = new DeclareStrictTypesRule;
			}

			$rules[] = new PhpDocParamFixerRule;

			return $rules;
		}
	}
