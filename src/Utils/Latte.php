<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Utils;

	use JP\CodeChecker\File;


	class Latte
	{
		private function __construct()
		{
		}


		public static function containsTag(File $file, string $tag): bool
		{
			return $file->matchContent('#{\\/?' . preg_quote($tag, '#') . '(}|\\s|\\$|\\|)#m');
		}


		public static function containsFilter(File $file, string $filter): bool
		{
			return $file->matchContent('#\\|' . preg_quote($filter, '#') . '(}|:|\\)|\\|)#m');
		}
	}
