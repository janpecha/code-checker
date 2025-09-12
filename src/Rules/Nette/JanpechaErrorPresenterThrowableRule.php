<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Nette;

	use JP\CodeChecker;
	use JP\CodeChecker\File;
	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\Rules\FileRule;


	class JanpechaErrorPresenterThrowableRule implements FileRule
	{
		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage('Presenters: Error - accepts Throwable');
		}


		public function processFile(File $file): void
		{
			if (!$file->matchName(['*ErrorPresenter.php'])) {
				return;
			}

			$file->findAndReplace(
				'/(function\s+renderDefault\\(\\\\)Exception(\s+)/m',
				'$1Throwable$2',
				'renderDefault() accepts Throwable'
			);
		}


		public static function configure(CodeChecker\CheckerConfig $config): void
		{
			if ($config->getComposerVersions()->hasPackage('nette/application')) {
				$config->addRule(new self);
			}
		}
	}
