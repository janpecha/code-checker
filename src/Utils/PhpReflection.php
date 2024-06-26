<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Utils;

	use CzProject\PhpSimpleAst;


	class PhpReflection
	{
		private function __construct()
		{
		}


		public static function getFileName(PhpSimpleAst\Reflection\ClassReflection $classReflection): string
		{
			$fileName = $classReflection->getFileName();
			return $fileName !== NULL ? $fileName : '*unknow*';
		}


		/**
		 * @param  iterable<string|\SplFileInfo> $files
		 */
		public static function scanFiles(iterable $files, callable $onProgress = NULL): PhpSimpleAst\Reflection\FilesReflection
		{
			$astParser = new PhpSimpleAst\AstParser;
			$phpFiles = [];

			foreach ($files as $file) {
				if ($onProgress !== NULL) {
					$onProgress();
				}

				if ($file instanceof \SplFileInfo) {
					$file = $file->getRealPath();
				}

				$phpFiles[] = $astParser->parseFile($file);
			}

			return new PhpSimpleAst\Reflection\FilesReflection($phpFiles);
		}


		public static function createFromFileContent(
			PhpSimpleAst\AstParser $astParser,
			\JP\CodeChecker\FileContent $fileContent
		): PhpSimpleAst\Ast\PhpFile
		{
			return new PhpSimpleAst\Ast\PhpFile(
				$fileContent->getFile(),
				$astParser->parseString($fileContent->contents)
			);
		}


		public static function saveFiles(
			\JP\CodeChecker\Engine $engine,
			PhpSimpleAst\Reflection\FilesReflection $filesReflection
		): void
		{
			if ($engine->isReadOnly()) {
				return;
			}

			foreach ($filesReflection->getFiles() as $phpFile) {
				$phpFile->save();
			}
		}
	}
