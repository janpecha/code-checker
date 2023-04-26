<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	interface Reporter
	{
		/**
		 * @param  string|\SplFileInfo $file
		 */
		function reportErrorInFile(
			string $message,
			$file,
			?int $line = NULL
		): void;


		/**
		 * @param  string|\SplFileInfo $file
		 */
		function reportWarningInFile(
			string $message,
			$file,
			?int $line = NULL
		): void;


		/**
		 * @param  string|\SplFileInfo $file
		 */
		function reportFixInFile(
			string $message,
			$file,
			?int $line = NULL
		): void;
	}
