<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Utils;

	use CzProject\PhpSimpleAst;


	class PhpReflection
	{
		public function __construct()
		{
			throw new \RuntimeException('This is static class.');
		}


		/**
		 * @param  iterable<string|\SplFileInfo> $files
		 */
		public static function scanFiles(iterable $files): PhpSimpleAst\Reflection\FilesReflection
		{
			$astParser = new PhpSimpleAst\AstParser;
			$phpFiles = [];

			foreach ($files as $file) {
				if ($file instanceof \SplFileInfo) {
					$file = $file->getRealPath();
				}

				$phpFiles[] = $astParser->parseFile($file);
			}

			return new PhpSimpleAst\Reflection\FilesReflection($phpFiles);
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
