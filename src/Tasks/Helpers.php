<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use Nette\CodeChecker\Result;
	use Nette\Utils\Strings;


	class Helpers
	{
		public static function findAndReplaces(
			string &$contents,
			Result $result,
			array $replacements,
			string $messageToUser
		): void
		{
			$fixed = FALSE;

			foreach ($replacements as $pattern => $replacement) {
				$fixed = $fixed || self::findAndReplace(
					$contents,
					$result,
					$pattern,
					$replacement,
					NULL
				);
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
	}
