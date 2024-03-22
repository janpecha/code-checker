<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Utils;

	use Nette\Utils\Strings;
	use Symfony\Component\Yaml\Yaml as SymfonyYaml;


	class Yaml
	{
		private function __construct()
		{
		}


		/**
		 * @return mixed
		 */
		public static function decode(string $s)
		{
			return SymfonyYaml::parse($s);
		}


		/**
		 * @param  mixed $value
		 */
		public static function encode($value): string
		{
			$res = SymfonyYaml::dump($value, 10, 4, SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
			$res = Strings::replace($res, '#^\'on\':$#m', 'on:');
			$res = Strings::replace($res, '#: {  }$#m', ':');
			$res = Strings::replace($res, '#: null$#m', ':');
			$res = self::addEmptyLinesBetweenItems($res);
			return $res;
		}


		/**
		 * https://github.com/symfony/symfony/issues/22421#issuecomment-348731143
		 */
		private static function addEmptyLinesBetweenItems(string $result): string
		{
			$i = 0;

			return preg_replace_callback('#^([\s]{4})?[a-zA-Z_\\\'-]+:#m', function ($match) use (&$i) {
				$i++;

				if ($i === 1) {
					return $match[0];
				}

				if (!isset($match[1])) {
					$i = 0;
				}

				return PHP_EOL . $match[0];
			}, $result);
		}
	}
