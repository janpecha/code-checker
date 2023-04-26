<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Engine;
	use JP\CodeChecker\Extension;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Version;


	class LatteExtension implements Extension
	{
		/** @var Version */
		private $version;

		/** @var string|string[] */
		private $fileMask;


		/**
		 * @param string|string[] $fileMask
		 */
		public function __construct(
			Version $version,
			$fileMask
		)
		{
			$this->version = $version;
			$this->fileMask = $fileMask;
		}


		public function run(Engine $engine): void
		{
			if (!$this->version->isEqualOrGreater('2.4.0')) {
				return;
			}

			$files = $engine->findFiles($this->fileMask);
			$wasChanged = FALSE;

			foreach ($files as $file) {
				$engine->progress();
				$content = new FileContent($file, $engine->readFile($file));

				$this->fixDeprecated($content, $engine);
				$this->checkDeprecated($content, $engine);

				if ($content->wasChanged()) {
					$engine->writeFile($file, (string) $content);
					$wasChanged = TRUE;
				}
			}

			if ($wasChanged) {
				$engine->commit('Latte: replaced deprecated stuff');
			}
		}


		private function fixDeprecated(FileContent $contents, Reporter $reporter): void
		{
			if ($this->version->isEqualOrGreater('2.4.0')) {
				$contents->findAndReplace(
					'#\\|escape\\|nl2br\\|noescape#m',
					'|breaklines',
					$reporter,
					'Latte: filter |nl2br replaced by filter |breaklines (deprecated in v2.4)'
				);

				$contents->findAndReplace(
					'#{\\?\\s*#m',
					'{php ',
					$reporter,
					'Latte: tag {? expr} replaced by tag {php} (deprecated in v2.4)'
				);

				$contents->findAndReplace(
					'#{php\\s+break}#m',
					'{breakIf TRUE}',
					$reporter,
					'Latte: keyword \'break\' is not supported in {php} (removed in v3.0)'
				);

				$contents->findAndReplace(
					'#({var\\s+)(\\$[a-zA-Z0-9]+)(\\s+)\\+=(\\s+)#m',
					'$1$2$3=$4$2 + ',
					$reporter,
					'Latte: operation += is not supported in {var} (removed in v3.0)'
				);
			}
		}


		private function checkDeprecated(FileContent $contents, Reporter $reporter): void
		{
			if ($contents->contains('<?php')) {
				$reporter->reportErrorInFile('Latte: template contains <?php open tag (deprecated in v2.4)', $contents->getFile());
			}

			if ($contents->contains('$template->') || $contents->contains('$template::') || $contents->contains('$template[')) {
				$reporter->reportWarningInFile('Latte: uses deprecated variable $template (deprecated in v2.4)', $contents->getFile());
			}

			if ($contents->contains('$_l')) {
				$reporter->reportWarningInFile('Latte: uses deprecated variable $_l (deprecated in v2.4)', $contents->getFile());
			}

			if ($contents->contains('$_g')) {
				$reporter->reportWarningInFile('Latte: uses deprecated variable $_g (deprecated in v2.4)', $contents->getFile());
			}

			if ($contents->contains('$__')) {
				$reporter->reportErrorInFile('Latte: uses internal variable $__* (disabled in v2.9)', $contents->getFile());
			}

			if ($contents->contains('$ʟ_')) {
				$reporter->reportErrorInFile('Latte: uses internal variable $ʟ_* (added in v2.8)', $contents->getFile());
			}

			if (self::containsTag($contents, 'includeblock')) {
				$reporter->reportWarningInFile('Latte: uses deprecated tag {includeblock} (deprecated in v2.4)', $contents->getFile());
			}

			if (self::containsTag($contents, 'use')) {
				$reporter->reportWarningInFile('Latte: uses deprecated tag {use} (deprecated in v2.4)', $contents->getFile());
			}

			if (self::containsTag($contents, 'status')) {
				$reporter->reportWarningInFile('Latte: uses deprecated tag {status} (deprecated in v2.4)', $contents->getFile());
			}

			if (self::containsTag($contents, '!')) {
				$reporter->reportErrorInFile('Latte: uses deprecated tag {!expr}, use filter |noescape instead (deprecated in v2.4)', $contents->getFile());
			}

			if (self::containsFilter($contents, 'nl2br')) {
				$reporter->reportErrorInFile('Latte: uses deprecated filter |nl2br (deprecated in v2.4)', $contents->getFile());
			}

			if (self::containsFilter($contents, 'safeurl')) {
				$reporter->reportErrorInFile('Latte: uses deprecated filter |safeurl (deprecated in v2.4)', $contents->getFile());
			}

			// TODO {extends} musi byt v hlavicce
		}


		/**
		 * @param  string|string[] $fileMask
		 * @return void
		 */
		public static function configure(
			CheckerConfig $config,
			Version $version,
			$fileMask = '*.latte'
		)
		{
			$config->addExtension(new self($version, $fileMask));
			$config->addTask([\Nette\CodeChecker\Tasks::class, 'latteSyntaxChecker'], '*.latte');
		}


		private static function containsTag(FileContent $contents, string $tag): bool
		{
			return $contents->match('#{\\/?' . preg_quote($tag, '#') . '(}|\\s|\\$|\\|)#m');
		}


		private static function containsFilter(FileContent $contents, string $filter): bool
		{
			return $contents->match('#\\|' . preg_quote($filter, '#') . '(}|:|\\)|\\|)#m');
		}
	}
