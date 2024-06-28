<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class PhpTokens
	{
		/** @var array<string|array{0: int, 1: string, 2: int}> */
		public $tokens;

		/** @var string|NULL */
		private $file;


		/**
		 * @param array<string|array{0: int, 1: string, 2: int}> $tokens
		 */
		public function __construct(
			array $tokens,
			?string $file = NULL
		)
		{
			$this->tokens = $tokens;
			$this->file = $file;
		}


		public function getFile(): ?string
		{
			return $this->file;
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


		public static function fromString(
			string $s,
			?string $file = NULL
		): self
		{
			return new self(@token_get_all($s), $file); // @ can trigger error
		}
	}
