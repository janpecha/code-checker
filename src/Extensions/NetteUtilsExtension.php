<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Engine;
	use JP\CodeChecker\Extension;
	use JP\CodeChecker\Version;


	class NetteUtilsExtension implements Extension
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

			$this->fixNetteObjectUsage($engine, $files);
		}


		/**
		 * @param  iterable<string|\SplFileInfo> $files
		 */
		private function fixNetteObjectUsage(Engine $engine, iterable $files): void
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
					'#(class [A-Z][a-zA-Z0-9_]+)\\sextends\\s(\\\\?Nette\\\\)Object((?:\\simplements [a-zA-Z0-9_\\\\]+){0,1}\\n(\\s*){)#m',
					"$1$3\n$4\tuse $2SmartObject;\n"
				);

				if ($newContent !== $content) {
					$engine->reportFixInFile('Nette: Nette\\Object replaced by Nette\\SmartObject (deprecated in v2.4.0)', $file);
					$engine->writeFile($file, $newContent);
					$wasChanged = TRUE;
				}
			}

			if ($wasChanged) {
				$engine->commit('Nette: replaced deprecated Nette\\Object by Nette\\SmartObject');
			}
		}


		/**
		 * @param  string|string[] $fileMask
		 * @return void
		 */
		public static function configure(
			CheckerConfig $config,
			Version $version,
			$fileMask = '*.php'
		)
		{
			$config->addExtension(new self($version, $fileMask));
		}
	}
