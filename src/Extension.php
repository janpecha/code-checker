<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	interface Extension
	{
		function run(Engine $engine): void;


		/**
		 * @return Rule[]
		 */
		function createRules(): array;


		/**
		 * @param  array<Rule> $rules
		 * @return Processor[]
		 */
		function createProcessors(array $rules): array;
	}
