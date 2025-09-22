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
