<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Composer;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\File;
	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\Rules\FileRule;
	use JP\CodeChecker\Version;


	class ComposerPhpVersionRule implements FileRule
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


		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage('Composer: updated required PHP version');
		}


		public function processFile(File $file): void
		{
			if ($file->getPath() !== $this->composerFile) {
				return;
			}

			$minimalPhpVersion = $this->minimalPhpVersion->toMinorString();
			$maximalPhpVersion = $this->maximalPhpVersion->toMinorString();

			$file->findAndReplace(
				'/^(\\s*"php"\\s*:\\s*")[^"]+(",?)$/m',
				'${1}' . $minimalPhpVersion . ' - ' . $maximalPhpVersion . '$2',
				'Updated required PHP version range'
			);
		}


		public static function configure(CheckerConfig $config): void
		{
			if ($config->getComposerFile()->isLibrary()) {
				$config->addRule(new self(
					$config->getComposerFile()->getPath(),
					$config->getPhpVersion(),
					$config->getMaxPhpVersion()
				));
			}
		}
	}
