<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Version;
	use Nette\CodeChecker\Result;
	use Nette\Utils\Strings;


	class Latte
	{
		/** @var Version */
		private $version;


		public function __construct(Version $version)
		{
			$this->version = $version;
		}


		public static function configure(CheckerConfig $config, Version $version): void
		{
			$config->addTask([\Nette\CodeChecker\Tasks::class, 'latteSyntaxChecker'], '*.latte');

			$me = new self($version);
			$config->addTask([$me, 'deprecatedFixer'], '*.latte');
			$config->addTask([$me, 'deprecatedChecker'], '*.latte');
		}


		public function deprecatedFixer(string &$contents, Result $result): void
		{
			if ($this->version->isEqualOrGreater('2.4.0')) {
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
			}
		}


		public function deprecatedChecker(string &$contents, Result $result): void
		{
			if (Strings::contains($contents, '<?php')) {
				$result->error('Latte: template contains <?php open tag (deprecated in v2.4)');
			}

			if (Strings::contains($contents, '$template->') || Strings::contains($contents, '$template::') || Strings::contains($contents, '$template[')) {
				$result->warning('Latte: uses deprecated variable $template (deprecated in v2.4)');
			}

			if (Strings::contains($contents, '$_l')) {
				$result->warning('Latte: uses deprecated variable $_l (deprecated in v2.4)');
			}

			if (Strings::contains($contents, '$_g')) {
				$result->warning('Latte: uses deprecated variable $_g (deprecated in v2.4)');
			}

			if (Strings::contains($contents, '$__')) {
				$result->error('Latte: uses internal variable $__* (disabled in v2.9)');
			}

			if (Strings::contains($contents, '$ʟ_')) {
				$result->error('Latte: uses internal variable $ʟ_* (added in v2.8)');
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

			if (self::containsFilter($contents, 'safeurl')) {
				$result->error('Latte: uses deprecated filter |safeurl (deprecated in v2.4)');
			}

			// TODO {extends} musi byt v hlavicce
		}


		private static function containsTag(string $contents, string $tag): bool
		{
			return (bool) Strings::match($contents, '#{\\/?' . preg_quote($tag, '#') . '(}|\\s|\\$|\\|)#m');
		}


		private static function containsFilter(string $contents, string $filter): bool
		{
			return (bool) Strings::match($contents, '#\\|' . preg_quote($filter, '#') . '(}|:|\\)|\\|)#m');
		}
	}
