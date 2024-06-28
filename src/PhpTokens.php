<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class PhpTokens
	{
		/** @var array<string|array{0: int, 1: string, 2: int}> */
		public $tokens;


		/**
		 * @param array<string|array{0: int, 1: string, 2: int}> $tokens
		 */
		public function __construct(array $tokens)
		{
			$this->tokens = $tokens;
		}


		public function __toString(): string
		{
			$s = '';

			foreach ($this->tokens as $token) {
				if (is_string($token)) {
					$s .= $token;

				} else {
					$s .= $token[1];
				}
			}

			return $s;
		}


		public static function fromString(string $s): self
		{
			return new self(@token_get_all($s)); // @ can trigger error
		}
	}
