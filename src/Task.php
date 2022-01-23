<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class Task
	{
		/** @var callable */
		private $handler;

		/** @var string|NULL */
		private $pattern;


		public function __construct(callable $handler, ?string $pattern)
		{
			$this->handler = $handler;
			$this->pattern = $pattern;
		}


		public function getHandler(): callable
		{
			return $this->handler;
		}


		public function getPattern(): ?string
		{
			return $this->pattern;
		}
	}
