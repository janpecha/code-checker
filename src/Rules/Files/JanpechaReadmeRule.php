<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Files;

	use JP\CodeChecker;
	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\FileRule;
	use JP\CodeChecker\Version;


	class JanpechaReadmeRule implements FileRule
	{
		/** @var Version */
		private $phpVersion;


		public function __construct(
			Version $phpVersion
		)
		{
			$this->phpVersion = $phpVersion;
		}


		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage('Readme: updated required PHP version');
		}


		public function processFile(File $file): void
		{
			if (!$file->matchName(['readme.md'])) {
				return;
			}

			$phpVersion = $this->phpVersion->toMinorString();

			$file->findAndReplace(
				'/(^.+requires\\s+PHP\\s+)[\\d.]+(\\s+.+$)/m',
				'${1}' . $phpVersion . '$2',
				'updated required PHP version to ' . $phpVersion
			);
		}


		public static function configure(CodeChecker\CheckerConfig $config): void
		{
			$config->addRule(new self(
				$config->getPhpVersion()
			));
		}
	}
