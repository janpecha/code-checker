<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use CzProject\PhpSimpleAst\Reflection\ClassReflection;
	use CzProject\PhpSimpleAst\Reflection\FilesReflection;
	use JP\CodeChecker\CheckerConfig;
	use JP\CodeChecker\Engine;
	use JP\CodeChecker\Extension;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\Reporter;
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

			$analyzedReflection = PhpReflection::scanFiles($files, $engine->progressHandler());
			$classesToProcess = $analyzedReflection->getClasses();

			$filesReflection = new FilesReflection(array_merge(
				$analyzedReflection->getFiles(),
				PhpReflection::scanFiles($engine->findScannedFiles($this->fileMask), $engine->progressHandler())->getFiles()
			));

			$this->fixPresenterMethodsPhpDocReturnType($engine, $classesToProcess, $filesReflection);
		}


		public function createRules(): array
		{
			return [];
		}


		public function createProcessors(array $rules): array
		{
			return [];
		}


		/**
		 * @param  ClassReflection[] $classesToProcess
		 */
		private function fixPresenterMethodsPhpDocReturnType(Engine $engine, array $classesToProcess, FilesReflection $filesReflection): void
		{
			$wasChanged = FALSE;
			$phpDocParser = NULL;

			foreach ($classesToProcess as $phpClass) {
				$engine->progress();

				if (!$filesReflection->isSubclassOf($phpClass, \Nette\Application\UI\Presenter::class)) { // @phpstan-ignore class.notFound
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
								$engine->reportFixInFile('Nette: added return type for ' . $classMethod->getFullName() . '()', PhpReflection::getFileName($phpClass));
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
									$engine->reportFixInFile('Nette: removed return type from ' . $classMethod->getFullName() . '()', PhpReflection::getFileName($phpClass));
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
