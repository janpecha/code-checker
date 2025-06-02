<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\Version;


	class ComposerExtension implements CodeChecker\Extension
	{
		private string $composerFile;
		private Version $minimalPhpVersion;
		private Version $maximalPhpVersion;


		public function __construct(
			string $composerFile,
			Version $minimalPhpVersion,
			Version $maximalPhpVersion,
		)
		{
			$this->composerFile = $composerFile;
			$this->minimalPhpVersion = $minimalPhpVersion;
			$this->maximalPhpVersion = $maximalPhpVersion;
		}


		public function run(CodeChecker\Engine $engine): void
		{
			$this->processComposerFile($engine);
		}


		public function createRules(): array
		{
			return [];
		}


		public function createProcessors(array $rules): array
		{
			return [];
		}


		private function processComposerFile(CodeChecker\Engine $engine): void
		{
			$minimalPhpVersion = $this->minimalPhpVersion->toMinorString();
			$maximalPhpVersion = $this->maximalPhpVersion->toMinorString();

			$contents = FileContent::fromFile($this->composerFile);
			$contents->findAndReplace(
				'/^(\\s*"php"\\s*:\\s*")[^"]+(",?)$/m',
				'${1}' . $minimalPhpVersion . ' - ' . $maximalPhpVersion . '$2',
				$engine,
				'Updated required PHP version range'
			);

			$engine->writeFile(new \SplFileInfo($this->composerFile), (string) $contents);

			if ($contents->wasChanged()) {
				$engine->commit('Composer: updated required PHP version');
			}
		}


		public static function configure(CodeChecker\CheckerConfig $config): void
		{
			if ($config->getComposerFile()->isLibrary()) {
				$config->addExtension(new self(
					$config->getComposerFile()->getPath(),
					$config->getPhpVersion(),
					$config->getMaxPhpVersion()
				));
			}
		}
	}
