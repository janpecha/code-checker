<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Utils;


	class FileContent
	{
		private function __construct()
		{
		}


		public static function detectIndentation(string $content): string
		{
			preg_match("~\\n+([\\t\\f ]*)~", $content, $m);
			return isset($m[1]) ? $m[1] : '';
		}
	}
