<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Utils;

	use Nette\Utils\Strings;


	class Paths
	{
		public static function shortPath(string $path, string $root): string
		{
			$path = rtrim($path, '/') . '/';
			$root = rtrim($root, '/') . '/';

			if (!Strings::startsWith($path, $root)) {
				throw new \RuntimeException('Path cannot be shorted, prefix (' . $root . ') doesnt match path (' . $path . ').');
			}

			return ltrim(Strings::substring($path, Strings::length($root)), '/');
		}
	}
