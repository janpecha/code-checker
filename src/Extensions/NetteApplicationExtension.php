<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Engine;
	use JP\CodeChecker\Extension;
	use JP\CodeChecker\Version;


	class NetteApplicationExtension implements Extension
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
			$files = $engine->findFiles($this->fileMask);

			$this->fixHttpMethodsInPresenters($engine, $files);
		}


		/**
		 * @param  iterable<string|\SplFileInfo> $files
		 */
		private function fixHttpMethodsInPresenters(Engine $engine, iterable $files): void
		{
			if (!$this->version->isEqualOrGreater('2.4.0')) {
				return;
			}

			$wasChanged = FALSE;

			foreach ($files as $file) {
				$engine->progress();
				$content = $engine->readFile($file);
				$newContent = \Nette\Utils\Strings::replace(
					$content,
					'#->isPost\\(\\)#m',
					'->isMethod(\'POST\')'
				);

				if ($newContent !== $content) {
					$engine->reportFixInFile('Nette: HTTP - method isPost() is deprecated, use isMethod(\'POST\') (deprecated in v2.4.0)', $file);
					$engine->writeFile($file, $newContent);
					$wasChanged = TRUE;
				}
			}

			if ($wasChanged) {
				$engine->commit('Nette: replaced deprecated method isPost() by isMethod(\'POST\')');
			}
		}


		/**
		 * @param  string|string[] $fileMask
		 * @return void
		 */
		public static function configure(
			CheckerConfig $config,
			Version $version,
			$fileMask = '*Presenter.php'
		)
		{
			$config->addExtension(new self($version, $fileMask));
		}
	}
