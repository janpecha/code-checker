<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\Reporter;


	class JanpechaNetteExtension implements CodeChecker\Extension
	{
		public function run(CodeChecker\Engine $engine): void
		{
			$this->processErrorPresenters($engine);
		}


		private function processErrorPresenters(CodeChecker\Engine $engine): void
		{
			$files = $engine->findFiles('*ErrorPresenter.php');

			$engine->processFiles(
				$files,
				function (FileContent $contents, Reporter $reporter) {
					$contents->findAndReplace(
						'/(if\\s*\\(\\$this\\-\\>isAjax\\(\\)\\)\\s*{\\s*\\$this->payload->.+;\\s*\\$this->)terminate(\\(\\);\\s*})/m',
						'$1sendPayload$2',
						$reporter,
						'deprecated sending of payload by terminate()'
					);
				},
				'Presenters: Error - fixed deprecated sending of payload by terminate()'
			);
		}


		public static function configure(CodeChecker\CheckerConfig $config): void
		{
			$config->addExtension(new self);
		}
	}
