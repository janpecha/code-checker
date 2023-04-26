<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use Nette\Utils\Strings;


	class FileContent
	{
		/** @var string */
		public $contents;

		/** @var string */
		private $file;

		/** @var string */
		private $originalContents;


		/**
		 * @param string|\SplFileInfo $file
		 */
		public function __construct(
			$file,
			string $contents
		)
		{
			$this->file = (string) $file;
			$this->originalContents = $contents;
			$this->contents = $contents;
		}


		public function getFile(): string
		{
			return $this->file;
		}


		public function wasChanged(): bool
		{
			return $this->contents !== $this->originalContents;
		}


		public function contains(string $needle): bool
		{
			return Strings::contains($this->contents, $needle);
		}


		public function findAndReplace(
			string $pattern,
			string $replacement,
			Engine $engine = NULL,
			string $reportMessage = NULL
		): bool
		{
			$this->contents = Strings::replace($this->contents, $pattern, $replacement);
			$wasChanged = $this->contents !== $this->originalContents;

			if ($wasChanged && $engine !== NULL && $reportMessage !== NULL) {
				$engine->reportFixInFile($reportMessage, $this->file);
			}

			return $wasChanged;
		}


		public function match(string $pattern, int $flags = 0, int $offset = 0): bool
		{
			return (bool) Strings::match($this->contents, $pattern, $flags, $offset);
		}


		public function __toString()
		{
			return $this->contents;
		}
	}
