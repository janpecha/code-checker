<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class CommitMessage
	{
		/** @var non-empty-string */
		private $subject;

		/** @var non-empty-string|NULL */
		private $body;


		/**
		 * @param non-empty-string $subject
		 * @param non-empty-string|NULL $body
		 */
		public function __construct(
			string $subject,
			?string $body = NULL
		)
		{
			$this->subject = $subject;
			$this->body = $body;
		}


		public function __toString(): string
		{
			$s = $this->subject;

			if ($this->body !== NULL) {
				$s .= "\n\n" . $this->body;
			}

			return $s;
		}
	}
