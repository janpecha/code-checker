<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Utils;


	class Php
	{
		public static function configure(CheckerConfig $config): void
		{
			$phpVersion = $config->getPhpVersion();
			$params = $config->getParameters();

			$tasks = \Nette\CodeChecker\Tasks::class;
			$config->addTask([$tasks, 'phpSyntaxChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'invalidPhpDocChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'invalidDoubleQuotedStringChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'trailingPhpTagRemover'], '*.php,*.phpt');
			$config->addTask([$tasks, 'tabIndentationPhpChecker'], '*.php,*.phpt');
			$config->addTask([$tasks, 'docSyntaxtHinter'], '*.php,*.phpt');

			if ($phpVersion->isEqualOrGreater('5.6.0')) {
				$config->addTask([$tasks, 'shortArraySyntaxFixer'], '*.php,*.phpt');
			}

			if ($phpVersion->isEqualOrGreater('7.2.0') && $params->toBool('php.strictTypes', TRUE)) {
				$config->addTask([__CLASS__, 'strictTypesDeclarationFixer'], '*.php,*.phpt');
			}
		}


		public static function strictTypesDeclarationFixer(string &$contents, \Nette\CodeChecker\Result $result): void
		{
			$declarations = Utils\PhpCode::getDeclarations($contents);

			if (!preg_match('#\bstrict_types\s*=\s*1\b#', implode("\n", $declarations))) {
				if (str_starts_with($contents, '<?php')) {
					$result->fix('Added missing declare(strict_types=1)');
					$indent = Utils\FileContent::detectIndentation($contents);
					$contents = "<?php\n\n" . $indent . "declare(strict_types=1);" . substr($contents, 5);

				} else {
					$result->error('Missing declare(strict_types=1)');
				}
			}
		}
	}
