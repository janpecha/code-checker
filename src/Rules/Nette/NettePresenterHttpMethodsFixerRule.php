<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Nette;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\FileRule;


	class NettePresenterHttpMethodsFixerRule implements FileRule
	{
		/** @var list<non-empty-string>|NULL */
		private ?array $acceptMasks;


		/**
		 * @param list<non-empty-string>|NULL $acceptMasks
		 */
		public function __construct(?array $acceptMasks = ['*Presenter.php'])
		{
			$this->acceptMasks = $acceptMasks;
		}


		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage(
				subject: 'Nette: replaced deprecated method isPost() by isMethod(\'POST\')'
			);
		}


		public function processFile(File $file): void
		{
			if ($this->acceptMasks !== NULL && !$file->matchName($this->acceptMasks)) {
				return;
			}

			$file->findAndReplace(
				'#->isPost\\(\\)#m',
				'->isMethod(\'POST\')',
				'Nette: HTTP - method isPost() is deprecated, use isMethod(\'POST\') (deprecated in v2.4.0)'
			);
		}


		public static function configure(CheckerConfig $config): void
		{
			$composerVersions = $config->getComposerVersions();

			if ($composerVersions->hasPackage('nette/application') && $composerVersions->getVersion('nette/application')->isEqualOrGreater('2.4.0')) {
				$config->addRule(new self);
			}
		}
	}
