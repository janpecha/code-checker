<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Utils;


	class PhpCode
	{
		private function __construct()
		{
		}


		/**
		 * @return string[]
		 */
		public static function getDeclarations(string $code): array
		{
			$declarations = [];
			$tokens = @token_get_all($code); // @ can trigger error

			for ($i = 0; $i < count($tokens); $i++) {
				if ($tokens[$i][0] === T_DECLARE) {
					$declaration = '';

					while (isset($tokens[++$i]) && $tokens[$i] !== '(') {
						continue;
					}

					while (isset($tokens[++$i]) && $tokens[$i] !== ';') {
						$declaration .= is_array($tokens[$i])
							? $tokens[$i][1]
							: $tokens[$i];
					}

					$declarations[] = substr(rtrim($declaration), 0, -1);

				} elseif (!in_array($tokens[$i][0], [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
					break;
				}
			}

			return $declarations;
		}
	}
