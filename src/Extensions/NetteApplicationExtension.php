<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Engine;
	use JP\CodeChecker\Extension;
	use JP\CodeChecker\Version;
	use JP\CodeChecker\Utils\PhpDoc;
	use JP\CodeChecker\Utils\PhpReflection;
	use Nette\Utils\Strings;


	class NetteApplicationExtension implements Extension
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

			$this->fixHttpMethodsInPresenters($engine, $files);
			$this->fixPresenterMethodsVisibility($engine, $files);
			$this->fixPresenterMethodsPhpDocReturnType($engine, $files);
		}


		/**
		 * @param  iterable<string|\SplFileInfo> $files
		 */
		private function fixHttpMethodsInPresenters(Engine $engine, iterable $files): void
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
					'#->isPost\\(\\)#m',
					'->isMethod(\'POST\')'
				);

				if ($newContent !== $content) {
					$engine->reportFixInFile('Nette: HTTP - method isPost() is deprecated, use isMethod(\'POST\') (deprecated in v2.4.0)', $file);
					$engine->writeFile($file, $newContent);
					$wasChanged = TRUE;
				}
			}

			if ($wasChanged) {
				$engine->commit('Nette: replaced deprecated method isPost() by isMethod(\'POST\')');
			}
		}


		/**
		 * @param  iterable<string|\SplFileInfo> $files
		 */
		private function fixPresenterMethodsVisibility(Engine $engine, iterable $files): void
		{
			$wasChanged = FALSE;
			$filesReflection = PhpReflection::scanFiles($files, $engine->progressHandler());

			foreach ($filesReflection->getClasses() as $phpClass) {
				$engine->progress();

				if (!$filesReflection->isSubclassOf($phpClass, \Nette\Application\UI\Presenter::class)) {
					continue;
				}

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
						$wasChanged = TRUE;
						$engine->reportFixInFile('Nette: fixed visibility of ' . $classMethod->getFullName() . '()', $phpClass->getFileName());
					}
				}
			}

			if ($wasChanged) {
				PhpReflection::saveFiles($engine, $filesReflection);
				$engine->commit('Nette: fixed visibilities of presenters methods');
			}
		}


		/**
		 * @param  iterable<string|\SplFileInfo> $files
		 */
		private function fixPresenterMethodsPhpDocReturnType(Engine $engine, iterable $files): void
		{
			$wasChanged = FALSE;
			$filesReflection = PhpReflection::scanFiles($files, $engine->progressHandler());
			$phpDocParser = NULL;

			foreach ($filesReflection->getClasses() as $phpClass) {
				$engine->progress();

				if (!$filesReflection->isSubclassOf($phpClass, \Nette\Application\UI\Presenter::class)) {
					continue;
				}

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
								$engine->reportFixInFile('Nette: added return type for ' . $classMethod->getFullName() . '()', $phpClass->getFileName());
							}

						} elseif ($actionType === 'remove-void') {
							if ($docComment === NULL) {
								continue;
							}

							$phpDoc = $phpDocParser->parse($docComment);

							foreach ($phpDoc->getTagsByName('@return') as $returnTag) {
								if ($returnTag->value->type === 'void') {
									PhpDoc::removeTag($phpDoc, $returnTag);
									$classMethod->setDocComment((string) $phpDoc);
									$methodFixed = TRUE;
									$engine->reportFixInFile('Nette: removed return type from ' . $classMethod->getFullName() . '()', $phpClass->getFileName());
								}
							}

						} else {
							throw new \RuntimeException("Unknow actionType '$actionType'.");
						}
					}

					if ($methodFixed) {
						$wasChanged = TRUE;
					}
				}
			}

			if ($wasChanged) {
				PhpReflection::saveFiles($engine, $filesReflection);
				$engine->commit('Nette: fixed return types of presenters methods');
			}
		}


		/**
		 * @param  string|string[] $fileMask
		 * @return void
		 */
		public static function configure(
			CheckerConfig $config,
			Version $version,
			$fileMask = '*Presenter.php'
		)
		{
			$config->addExtension(new self($version, $fileMask));
		}
	}
