<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Nette;

	use CzProject\PhpSimpleAst;
	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\PhpReflectionRule;
	use JP\CodeChecker\Utils\PhpDoc;
	use Nette\Utils\Strings;


	class NettePresenterMethodsReturnTypeRule implements PhpReflectionRule
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
				subject: 'Nette: fixed return types of presenters methods'
			);
		}


		public function processPhpReflection(File $file, PhpSimpleAst\Reflection\Reflection $phpReflection): void
		{
			if (!$file->matchName($this->acceptMasks)) {
				return;
			}

			foreach ($phpReflection->getClasses() as $phpClass) {
				foreach ($phpClass->getMethods() as $classMethod) {
					$methodName = Strings::lower($classMethod->getName());
					$actionType = NULL;

					if ($methodName !== 'action' && Strings::startsWith($methodName, 'action')) {
						$actionType = 'add-void';

					} elseif ($methodName !== 'render' && Strings::startsWith($methodName, 'render')) {
						$actionType = 'add-void';

					} elseif ($methodName !== 'handle' && Strings::startsWith($methodName, 'handle')) {
						$actionType = 'add-void';

					} elseif ($methodName === 'startup'
						|| $methodName === 'beforerender'
						|| $methodName === 'afterrender'
						|| $methodName === 'shutdown'
					) {
						$actionType = 'remove-void';
					}

					$methodFixed = FALSE;

					if ($actionType !== NULL && !$classMethod->hasReturnType()) {
						$phpDocParser = $phpDocParser ?? \CzProject\PhpSimpleAst\Utils\PhpDocParser::getInstance();
						$docComment = $classMethod->getDocComment();

						if ($actionType === 'add-void') {
							if ($docComment === NULL) {
								$classMethod->setDocComment("/**\n * @return void\n */");
								$methodFixed = TRUE;

							} else {
								$phpDoc = $phpDocParser->parse($docComment);

								if (!PhpDoc::hasTag($phpDoc, '@return')) {
									PhpDoc::addReturnTag($phpDoc, 'void');
									$methodFixed = TRUE;
									$classMethod->setDocComment((string) $phpDoc);
								}
							}

							if ($methodFixed) {
								$file->reportFix('Nette: added return type for ' . $classMethod->getFullName() . '()');
							}

						} elseif ($actionType === 'remove-void') {
							if ($docComment === NULL) {
								continue;
							}

							$phpDoc = $phpDocParser->parse($docComment);

							foreach ($phpDoc->getTagsByName('@return') as $returnTag) {
								if (!($returnTag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode)) {
									continue;
								}

								if (((string) $returnTag->value->type) === 'void') {
									PhpDoc::removeTag($phpDoc, $returnTag);
									$classMethod->setDocComment((string) $phpDoc);
									$methodFixed = TRUE;
									$file->reportFix('Nette: removed return type from ' . $classMethod->getFullName() . '()');
								}
							}

						} else {
							throw new \RuntimeException("Unknow actionType '$actionType'.");
						}
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
