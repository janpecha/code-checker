<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Nette;

	use JP\CodeChecker;
	use JP\CodeChecker\File;
	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\Rules\FileRule;


	class JanpechaErrorPresenterTerminateRule implements FileRule
	{
		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage('Presenters: Error - fixed deprecated sending of payload by terminate()');
		}


		public function processFile(File $file): void
		{
			if (!$file->matchName(['*ErrorPresenter.php'])) {
				return;
			}

			$file->findAndReplace(
				'/(if\\s*\\(\\$this\\-\\>isAjax\\(\\)\\)\\s*{\\s*\\$this->payload->.+;\\s*\\$this->)terminate(\\(\\);\\s*})/m',
				'$1sendPayload$2',
				'deprecated sending of payload by terminate()'
			);
		}


		public static function configure(CodeChecker\CheckerConfig $config): void
		{
			if ($config->getComposerVersions()->hasPackage('nette/application')) {
				$config->addRule(new self);
			}
		}
	}
