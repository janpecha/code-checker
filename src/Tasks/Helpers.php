<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use Nette\CodeChecker\Result;
	use Nette\Utils\Strings;


	class Helpers
	{
		/**
		 * @param  array<string, string> $replacements
		 */
		public static function findAndReplaces(
			string &$contents,
			Result $result,
			array $replacements,
			string $messageToUser
		): void
		{
			$fixed = FALSE;

			foreach ($replacements as $pattern => $replacement) {
				$res = self::findAndReplace(
					$contents,
					$result,
					$pattern,
					$replacement,
					NULL
				);

				$fixed = $fixed || $res;
			}

			if ($fixed) {
				$result->fix($messageToUser);
			}
		}


		public static function findAndReplace(
			string &$contents,
			Result $result,
			string $pattern,
			string $replacement,
			?string $messageToUser
		): bool
		{
			$fixed = FALSE;

			if (Strings::match($contents, $pattern)) {
				$contents = Strings::replace($contents, $pattern, $replacement);
				$fixed = TRUE;
			}

			if ($fixed && is_string($messageToUser)) {
				$result->fix($messageToUser);
			}

			return $fixed;
		}


		public static function findAndWarn(
			string &$contents,
			Result $result,
			string $pattern,
			string $messageToUser
		): bool
		{
			if (Strings::match($contents, $pattern)) {
				$result->warning($messageToUser);
				return TRUE;
			}

			return FALSE;
		}
	}
