<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;


	class ProgressBar
	{
		/** @var bool */
		private $showProgress;

		/** @var bool */
		private $wasPrinted = FALSE;

		/** @var int */
		private $counter = 0;


		public function __construct(bool $showProgress)
		{
			$this->showProgress = $showProgress;
		}


		public function progress(): void
		{
			if ($this->showProgress) {
				echo str_pad(str_repeat('.', $this->counter++ % 40), 40), "\x0D";
				$this->wasPrinted = TRUE;
			}
		}

		public function reset(): void
		{
			if ($this->showProgress && $this->wasPrinted) {
				echo str_pad('', 40), "\x0D";
				$this->wasPrinted = FALSE;
			}
		}
	}
