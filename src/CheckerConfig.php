<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class CheckerConfig
	{
		/** @var string[] */
		private $paths = [];

		/** @var string[] */
		private $ignore = [];

		/** @var array<array{0: callable, 1: string|NULL}> */
		private $tasks = [];


		/**
		 * @return string[]
		 */
		public function getPaths(): array
		{
			return $this->paths;
		}


		public function addPath(string $path): self
		{
			$this->paths[] = $path;
			return $this;
		}


		/**
		 * @return string[]
		 */
		public function getIgnore(): array
		{
			return $this->ignore;
		}


		public function addIgnore(string $ignore): self
		{
			$this->ignore[] = $ignore;
			return $this;
		}


		/**
		 * @return array<array{0: callable, 1: string|NULL}>
		 */
		public function getTasks(): array
		{
			return $this->tasks;
		}


		public function addTask(callable $task, string $pattern = NULL): void
		{
			$this->tasks[] = [$task, $pattern];
		}
	}
