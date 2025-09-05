<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use Nette\Utils\Strings;


	class File
	{
		public string $contents;

		private readonly string $path;
		private readonly string $originalContents;
		private readonly string $originalPath;

		/** @var ResultMessage[] */
		private array $result = [];


		public function __construct(
			string $path,
			string $contents
		)
		{
			$this->path = $path;
			$this->originalPath = $path;
			$this->contents = $contents;
			$this->originalContents = $contents;
		}


		public function getPath(): string
		{
			return $this->path;
		}


		/**
		 * @return ResultMessage[]
		 */
		public function getResult(): array
		{
			return $this->result;
		}


		public function wasChanged(): bool
		{
			return $this->contents !== $this->originalContents;
		}


		public function wasRenamed(): bool
		{
			return $this->path !== $this->originalPath;
		}


		public function contains(string $needle): bool
		{
			return Strings::contains($this->contents, $needle);
		}


		public function reportFix(string $message, ?int $line = null): void
		{
			$this->result[] = new ResultMessage(
				type: ResultType::Fix,
				message: $message,
				line: $line
			);
		}


		public function reportWarning(string $message, ?int $line = null): void
		{
			$this->result[] = new ResultMessage(
				type: ResultType::Warning,
				message: $message,
				line: $line
			);
		}


		public function reportError(string $message, ?int $line = null): void
		{
			$this->result[] = new ResultMessage(
				type: ResultType::Error,
				message: $message,
				line: $line
			);
		}


		public function findAndReplace(
			string $pattern,
			string $replacement,
			?string $reportMessage = NULL
		): bool
		{
			$newContents = Strings::replace($this->contents, $pattern, $replacement);
			$wasChanged = $this->contents !== $newContents;
			$this->contents = $newContents;

			if ($wasChanged && $reportMessage !== NULL) {
				$this->reportFix($reportMessage);
			}

			return $wasChanged;
		}


		/**
		 * @param  non-empty-string[] $acceptMasks
		 */
		public function matchName(array $acceptMasks): bool
		{
			$name = basename($this->path);
			$res = FALSE;

			foreach ($acceptMasks as $pattern) {
				$neg = substr($pattern, 0, 1) === '!';

				if (fnmatch(ltrim($pattern, '!'), $name, FNM_CASEFOLD)) {
					$res = !$neg;
				}
			}

			return $res;
		}


		public function matchContent(string $pattern, int $flags = 0, int $offset = 0): bool
		{
			return (bool) Strings::match($this->contents, $pattern, $flags, $offset);
		}


		public function convertOffsetToLine(int $offset): int
		{
			return $offset ? substr_count($this->contents, "\n", 0, $offset) + 1 : 1;
		}


		public function __toString(): string
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
