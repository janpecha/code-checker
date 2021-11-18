<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class CheckerRunner
	{
		/** @var \Nette\CodeChecker\Checker */
		private $checker;

		/** @var string[] */
		private $paths = [];


		/**
		 * @param string[] $paths
		 */
		public function __construct(\Nette\CodeChecker\Checker $checker, array $paths)
		{
			$this->checker = $checker;
			$this->paths = $paths;
		}


		public function run(bool $readOnly, bool $showProgress): bool
		{
			$this->checker->readOnly = $readOnly;
			$this->checker->showProgress = $showProgress;
			return $this->checker->run($this->paths);
		}
	}
