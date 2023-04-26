<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use Nette\Utils\Strings;


	class MemoryReporter implements Reporter
	{
		/** @var string */
		private $basePath;

		/** @var int */
		private $basePathLength;

		/** @var string[] */
		private $messages = [];


		public function __construct(string $basePath = '')
		{
			$this->basePath = $basePath !== '' ? (\CzProject\PathHelper::absolutizePath($basePath)) : $basePath;
			$this->basePathLength = Strings::length($this->basePath);
		}


		/**
		 * @return string[]
		 */
		public function getMessages(): array
		{
			return $this->messages;
		}


		public function reportErrorInFile(
			string $message,
			$file,
			?int $line = NULL
		): void
		{
			$this->addMessage('ERROR', $message, $file, $line);
		}


		public function reportWarningInFile(
			string $message,
			$file,
			?int $line = NULL
		): void
		{
			$this->addMessage('WARN', $message, $file, $line);
		}


		public function reportFixInFile(
			string $message,
			$file,
			?int $line = NULL
		): void
		{
			$this->addMessage('FIX', $message, $file, $line);
		}


		/**
		 * @param string|\SplFileInfo $file
		 */
		private function addMessage(
			string $type,
			string $message,
			$file,
			?int $line
		): void
		{
			$file = (string) $file;
			$relativePath = \CzProject\PathHelper::absolutizePath($file);

			if ($this->basePath !== '' && Strings::startsWith($relativePath, $this->basePath)) {
				$relativePath = Strings::substring($relativePath, $this->basePathLength);
			}

			if ($relativePath === '' && $line) {
				$relativePath = basename($file);
			}

			$this->messages[] = str_pad($type, 5) . ' | '
				. $relativePath
				. ($line ? ':' . $line : '')
				. ($relativePath !== '' ? ' | ' : '') . $message;
		}
	}
