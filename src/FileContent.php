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
			?Reporter $reporter = NULL,
			?string $reportMessage = NULL
		): bool
		{
			$newContents = Strings::replace($this->contents, $pattern, $replacement);
			$wasChanged = $this->contents !== $newContents;
			$this->contents = $newContents;

			if ($wasChanged && $reporter !== NULL && $reportMessage !== NULL) {
				$reporter->reportFixInFile($reportMessage, $this->file);
			}

			return $wasChanged;
		}


		/**
		 * @param  non-empty-string[] $acceptMasks
		 */
		public function matchName(array $acceptMasks): bool
		{
			$name = basename($this->file);
			$res = FALSE;

			foreach ($acceptMasks as $pattern) {
				$neg = substr($pattern, 0, 1) === '!';

				if (fnmatch(ltrim($pattern, '!'), $name, FNM_CASEFOLD)) {
					$res = !$neg;
				}
			}

			return $res;
		}


		public function match(string $pattern, int $flags = 0, int $offset = 0): bool
		{
			return (bool) Strings::match($this->contents, $pattern, $flags, $offset);
		}


		public function convertOffsetToLine(int $offset): int
		{
			return $offset ? substr_count($this->contents, "\n", 0, $offset) + 1 : 1;
		}


		public function __toString()
		{
			return $this->contents;
		}


		public static function fromFile(string $file): self
		{
			$s = file_get_contents($file);

			if (!is_string($s)) {
				throw new \RuntimeException('Reading of file ' . $file . ' failed.');
			}

			return new self($file, $s);
		}
	}
