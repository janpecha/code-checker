<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;
	use Nette\CodeChecker\Result;
	use Nette\Utils\Strings;


	class Latte
	{
		public static function configure(CheckerConfig $config): void
		{
			$tasks = \Nette\CodeChecker\Tasks::class;
			$config->addTask([$tasks, 'latteSyntaxChecker'], '*.latte');
			$config->addTask([self::class, 'deprecatedFixer'], '*.latte');
		}


		public static function deprecatedFixer(string &$contents, Result $result): void
		{
			Helpers::findAndReplace(
				$contents,
				$result,
				'#\\|escape\\|nl2br\\|noescape#m',
				'|breaklines',
				'Latte: filter |nl2br replaced by filter |breaklines  (deprecated in v2.4)'
			);

			Helpers::findAndReplace(
				$contents,
				$result,
				'#{\\?\\s*#m',
				'{php ',
				'Latte: tag {? expr} replaced by tag {php}  (deprecated in v2.4)'
			);

			if (Strings::contains($contents, '<?php')) {
				$result->error('Latte: template contains <?php open tag (deprecated in v2.4)');
			}

			if (Strings::contains($contents, '$template')) {
				$result->warning('Latte: uses deprecated variable $template (deprecated in v2.4)');
			}

			if (Strings::contains($contents, '$_l')) {
				$result->warning('Latte: uses deprecated variable $_l (deprecated in v2.4)');
			}

			if (Strings::contains($contents, '$_g')) {
				$result->warning('Latte: uses deprecated variable $_g (deprecated in v2.4)');
			}

			if (self::containsTag($contents, 'includeblock')) {
				$result->warning('Latte: uses deprecated tag {includeblock} (deprecated in v2.4)');
			}

			if (self::containsTag($contents, 'use')) {
				$result->warning('Latte: uses deprecated tag {use} (deprecated in v2.4)');
			}

			if (self::containsTag($contents, 'status')) {
				$result->warning('Latte: uses deprecated tag {status} (deprecated in v2.4)');
			}

			if (self::containsTag($contents, '!')) {
				$result->error('Latte: uses deprecated tag {!expr}, use filter |noescape instead (deprecated in v2.4)');
			}

			if (self::containsFilter($contents, 'nl2br')) {
				$result->error('Latte: uses deprecated filter |nl2br (deprecated in v2.4)');
			}
		}


		private static function containsTag(string $contents, string $tag): bool
		{
			return (bool) Strings::match($contents, '#{\\/?' . preg_quote($tag, '#') . '(}|\\s|\\$|\\|)#m');
		}


		private static function containsFilter(string $contents, string $filter): bool
		{
			return (bool) Strings::match($contents, '#\\|' . preg_quote($filter, '#') . '(}|:|\\|)#m');
		}
	}
