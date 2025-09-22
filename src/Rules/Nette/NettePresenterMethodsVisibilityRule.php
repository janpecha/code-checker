<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Nette;

	use CzProject\PhpSimpleAst;
	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\PhpReflectionRule;
	use Nette\Utils\Strings;


	class NettePresenterMethodsVisibilityRule implements PhpReflectionRule
	{
		/** @var list<non-empty-string> */
		private array $acceptMasks;


		/**
		 * @param list<non-empty-string> $acceptMasks
		 */
		public function __construct(array $acceptMasks = ['*Presenter.php'])
		{
			$this->acceptMasks = $acceptMasks;
		}


		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage(
				subject: 'Nette: fixed visibility of presenters methods'
			);
		}


		public function processPhpReflection(File $file, PhpSimpleAst\Reflection\Reflection $phpReflection): void
		{
			if (!$file->matchName($this->acceptMasks)) {
				return;
			}

			foreach ($phpReflection->getClasses() as $phpClass) {
				foreach ($phpClass->getMethods() as $classMethod) {
					$methodFixed = FALSE;
					$methodName = Strings::lower($classMethod->getName());

					if ($methodName !== 'action' && Strings::startsWith($methodName, 'action') && !$classMethod->isPublic()) {
						$classMethod->setVisibilityToPublic();
						$methodFixed = TRUE;

					} elseif ($methodName !== 'render' && Strings::startsWith($methodName, 'render') && !$classMethod->isPublic()) {
						$classMethod->setVisibilityToPublic();
						$methodFixed = TRUE;

					} elseif ($methodName !== 'handle' && Strings::startsWith($methodName, 'handle') && !$classMethod->isPublic()) {
						$classMethod->setVisibilityToPublic();
						$methodFixed = TRUE;

					} elseif (($methodName === 'startup'
						|| $methodName === 'beforerender'
						|| $methodName === 'afterrender'
						|| $methodName === 'shutdown'
						) && !$classMethod->isProtected()
					) {
						$classMethod->setVisibilityToProtected();
						$methodFixed = TRUE;

					} elseif ($methodName !== 'createcomponent' && Strings::startsWith($methodName, 'createcomponent') && !$classMethod->isProtected()) {
						$classMethod->setVisibilityToProtected();
						$methodFixed = TRUE;
					}

					if ($methodFixed) {
						$file->reportFix('Nette: fixed visibility of ' . $classMethod->getFullName() . '()');
					}
				}
			}
		}


		public static function configure(CheckerConfig $config): void
		{
			$composerVersions = $config->getComposerVersions();

			if ($composerVersions->hasPackage('nette/application')) {
				$config->addRule(new self);
			}
		}
	}
