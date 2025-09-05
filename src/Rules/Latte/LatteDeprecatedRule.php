<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Latte;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\FileRule;
	use JP\CodeChecker\Utils;
	use JP\CodeChecker\Version;


	class LatteDeprecatedRule implements FileRule
	{
		/** @var Version */
		private $version;

		/** @var list<non-empty-string>|NULL */
		private ?array $acceptMasks;


		/**
		 * @param list<non-empty-string>|NULL $acceptMasks
		 */
		public function __construct(
			Version $version,
			?array $acceptMasks = NULL
		)
		{
			$this->version = $version;
			$this->acceptMasks = $acceptMasks;
		}


		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage('Latte: fixed deprecated stuff');
		}


		public function processFile(File $file): void
		{
			if ($this->acceptMasks !== NULL && !$file->matchName($this->acceptMasks)) {
				return;
			}

			$this->fixDeprecated($file);
			$this->checkDeprecated($file);
		}


		public function fixDeprecated(File $file): void
		{
			if ($this->version->isEqualOrGreater('2.4.0')) {
				$file->findAndReplace(
					'#\\|escape\\|nl2br\\|noescape#m',
					'|breaklines',
					'Latte: filter |nl2br replaced by filter |breaklines (deprecated in v2.4)'
				);

				$file->findAndReplace(
					'#{\\?\\s*#m',
					'{php ',
					'Latte: tag {? expr} replaced by tag {php} (deprecated in v2.4)'
				);

				$file->findAndReplace(
					'#{php\\s+break}#m',
					'{breakIf TRUE}',
					'Latte: keyword \'break\' is not supported in {php} (removed in v3.0)'
				);

				$file->findAndReplace(
					'#({var\\s+)(\\$[a-zA-Z0-9]+)(\\s+)\\+=(\\s+)#m',
					'$1$2$3=$4$2 + ',
					'Latte: operation += is not supported in {var} (removed in v3.0)'
				);
			}
		}


		public function checkDeprecated(File $file): void
		{
			if ($file->contains('<?php')) {
				$file->reportError('Latte: template contains <?php open tag (deprecated in v2.4)');
			}

			if ($file->contains('$template->') || $file->contains('$template::') || $file->contains('$template[')) {
				$file->reportWarning('Latte: uses deprecated variable $template (deprecated in v2.4)');
			}

			if ($file->contains('$_l')) {
				$file->reportWarning('Latte: uses deprecated variable $_l (deprecated in v2.4)');
			}

			if ($file->contains('$_g')) {
				$file->reportWarning('Latte: uses deprecated variable $_g (deprecated in v2.4)');
			}

			if ($file->contains('$__')) {
				$file->reportError('Latte: uses internal variable $__* (disabled in v2.9)');
			}

			if ($file->contains('$ʟ_')) {
				$file->reportError('Latte: uses internal variable $ʟ_* (added in v2.8)');
			}

			if (Utils\Latte::containsTag($file, 'includeblock')) {
				$file->reportWarning('Latte: uses deprecated tag {includeblock} (deprecated in v2.4)');
			}

			if (Utils\Latte::containsTag($file, 'use')) {
				$file->reportWarning('Latte: uses deprecated tag {use} (deprecated in v2.4)');
			}

			if (Utils\Latte::containsTag($file, 'status')) {
				$file->reportWarning('Latte: uses deprecated tag {status} (deprecated in v2.4)');
			}

			if (Utils\Latte::containsTag($file, '!')) {
				$file->reportError('Latte: uses deprecated tag {!expr}, use filter |noescape instead (deprecated in v2.4)');
			}

			if (Utils\Latte::containsFilter($file, 'nl2br')) {
				$file->reportError('Latte: uses deprecated filter |nl2br (deprecated in v2.4)');
			}

			if (Utils\Latte::containsFilter($file, 'safeurl')) {
				$file->reportError('Latte: uses deprecated filter |safeurl (deprecated in v2.4)');
			}

			// TODO {extends} musi byt v hlavicce
		}


		/**
		 * @param list<non-empty-string>|NULL $acceptMasks
		 */
		public static function configure(
			CheckerConfig $config,
			Version $version,
			?array $acceptMasks = ['*.latte']
		): void
		{
			if (!$version->isEqualOrGreater('2.4.0')) {
				return;
			}

			$config->addRule(new self($version, $acceptMasks));
		}
	}
