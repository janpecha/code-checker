<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use Nette\Utils\FileSystem;


	class Engine
	{
		/** @var string */
		private $projectDirectory;

		/** @var string[] */
		private $paths;

		/** @var string[] */
		private $ignore;

		/** @var bool */
		private $readOnly;

		/** @var bool */
		private $stepByStep;

		/** @var ProgressBar */
		private $progressBar;

		/** @var \Nette\CommandLine\Console */
		private $console;


		/**
		 * @param  string[] $paths
		 * @param  string[] $ignore
		 */
		public function __construct(
			string $projectDirectory,
			array $paths,
			array $ignore,
			bool $readOnly,
			bool $stepByStep,
			ProgressBar $progressBar,
			\Nette\CommandLine\Console $console
		)
		{
			$this->projectDirectory = $projectDirectory;
			$this->paths = $paths;
			$this->ignore = $ignore;
			$this->readOnly = $readOnly;
			$this->stepByStep = $stepByStep;
			$this->progressBar = $progressBar;
			$this->console = $console;
		}


		public function isReadOnly(): bool
		{
			return $this->readOnly;
		}


		public function isStepByStep(): bool
		{
			return $this->stepByStep;
		}


		public function progress(): void
		{
			$this->progressBar->progress();
		}


		public function reportErrorInFile(string $message, string $file, ?int $line = NULL): void
		{
			$this->write($file, 'ERROR', $message, $line, 'red');
		}


		public function reportWarningInFile(string $message, string $file, ?int $line = NULL): void
		{
			$this->write($file, 'WARNING', $message, $line, 'yellow');
		}


		public function reportFixInFile(string $message, string $file, ?int $line = NULL): void
		{
			$this->write($file, $this->readOnly ? 'FOUND' : 'FIX', $message, $line, 'aqua');
		}


		public function existsFile(string $path): bool
		{
			return is_file($this->path($path));
		}


		public function readFile(string $path): string
		{
			return FileSystem::read($this->path($path));
		}


		public function writeFile(string $path, string $content): void
		{
			if (!$this->readOnly) {
				FileSystem::write($this->path($path), $content);
			}
		}


		/**
		 * @param  string|string[] $masks
		 * @return \AppendIterator<\SplFileInfo>
		 */
		public function findFiles($masks): \AppendIterator
		{
			if (!is_array($masks)) {
				$masks = [$masks];
			}

			$iterator = new \AppendIterator;

			foreach ($this->paths as $path) {
				$iterator->append(
					is_file($path)
					? new \ArrayIterator([$path])
					: \Nette\Utils\Finder::findFiles(...$masks)
						->exclude(...$this->ignore)
						->from($path)
						->exclude(...$this->ignore)
						->getIterator()
				);
			}

			return $iterator;
		}


		private function path(string $path): string
		{
			return $this->projectDirectory . '/' . $path;
		}


		private function write(
			string $relativePath,
			string $type,
			string $message,
			?int $line,
			string $color
		): void
		{
			$base = basename($relativePath);
			echo $this->console->color($color, str_pad("[$type]", 10)),
				$base === $relativePath ? '' : $this->console->color('silver', dirname($relativePath) . DIRECTORY_SEPARATOR),
				$this->console->color('white', $base . ($line ? ':' . $line : '')), '    ',
				$this->console->color($color, $message), "\n";
		}
	}
