<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Version;


	class JanpechaReadmeExtension implements CodeChecker\Extension
	{
		/** @var Version */
		private $phpVersion;


		public function __construct(
			Version $phpVersion
		)
		{
			$this->phpVersion = $phpVersion;
		}


		public function run(CodeChecker\Engine $engine): void
		{
			$this->processReadmes($engine);
		}


		public function createRules(): array
		{
			return [];
		}


		public function createProcessors(array $rules): array
		{
			return [];
		}


		private function processReadmes(CodeChecker\Engine $engine): void
		{
			$files = $engine->findFiles('readme.md');
			$phpVersion = $this->phpVersion->toMinorString();

			$engine->processFiles(
				$files,
				function (FileContent $contents, Reporter $reporter) use ($phpVersion) {
					$contents->findAndReplace(
						'/(^.+requires\\s+PHP\\s+)[\\d.]+(\\s+.+$)/m',
						'${1}' . $phpVersion . '$2',
						$reporter,
						'updated required PHP version to ' . $phpVersion
					);
				},
				'Readme: updated required PHP version'
			);
		}


		public static function configure(CodeChecker\CheckerConfig $config): void
		{
			$config->addExtension(new self(
				$config->getPhpVersion()
			));
		}
	}
