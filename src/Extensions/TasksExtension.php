<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker\Engine;
	use JP\CodeChecker\Extension;
	use JP\CodeChecker\Rules\Files\FileTaskRule;
	use JP\CodeChecker\Task;


	class TasksExtension implements Extension
	{
		/** @var Task[] */
		private $tasks;


		/**
		 * @param  Task[] $tasks
		 */
		public function __construct(
			array $tasks
		)
		{
			$this->tasks = $tasks;
		}


		public function run(Engine $engine): void
		{
		}


		public function createRules(): array
		{
			$rules = [];

			foreach ($this->tasks as $task) {
				$rules[] = new FileTaskRule($task);
			}

			return $rules;
		}


		public function createProcessors(array $rules): array
		{
			return [];
		}
	}
