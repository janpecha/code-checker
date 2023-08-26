<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use Nette\Utils\FileSystem;
	use Nette\Utils\Strings;


	class Engine implements Reporter
	{
		/** @var string */
		private $projectDirectory;

		/** @var string[] */
		private $paths;

		/** @var string[] */
		private $scannedPaths;

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

		/** @var \CzProject\GitPhp\GitRepository|NULL */
		private $gitRepository;

		/** @var bool */
		private $error = FALSE;

		/** @var bool */
		private $stepByStepFix = FALSE;


		/**
		 * @param  string[] $paths
		 * @param  string[] $scannedPaths
		 * @param  string[] $ignore
		 */
		public function __construct(
			string $projectDirectory,
			array $paths,
			array $scannedPaths,
			array $ignore,
			bool $readOnly,
			bool $stepByStep,
			ProgressBar $progressBar,
			\Nette\CommandLine\Console $console,
			?\CzProject\GitPhp\GitRepository $gitRepository = NULL
		)
		{
			$this->projectDirectory = $projectDirectory;
			$this->paths = $paths;
			$this->scannedPaths = $scannedPaths;
			$this->ignore = $ignore;
			$this->readOnly = $readOnly;
			$this->stepByStep = $stepByStep;
			$this->progressBar = $progressBar;
			$this->console = $console;
			$this->gitRepository = $gitRepository;
		}


		public function isSuccess(): bool
		{
			return !$this->error && !$this->stepByStepFix;
		}


		public function isError(): bool
		{
			return $this->error;
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


		public function progressHandler(): callable
		{
			return $this->progressBar->progressHandler();
		}


		public function reportErrorInFile(string $message, $file, ?int $line = NULL): void
		{
			$this->write($file, 'ERROR', $message, $line, 'red');
			$this->error = TRUE;
		}


		public function reportWarningInFile(string $message, $file, ?int $line = NULL): void
		{
			$this->write($file, 'WARNING', $message, $line, 'yellow');
		}


		public function reportFixInFile(string $message, $file, ?int $line = NULL): void
		{
			$this->write($file, $this->readOnly ? 'FOUND' : 'FIX', $message, $line, 'aqua');
			$this->error = $this->error || $this->readOnly; // error or FOUND
			$this->stepByStepFix = $this->stepByStepFix || $this->stepByStep;
		}


		/**
		 * @param  string|\SplFileInfo $path
		 */
		public function existsFile($path): bool
		{
			return is_file($this->path($path));
		}


		/**
		 * @param  string|\SplFileInfo $path
		 */
		public function readFile($path): string
		{
			return FileSystem::read($this->path($path));
		}


		/**
		 * @param  string|\SplFileInfo $path
		 */
		public function writeFile($path, string $content): void
		{
			if (!$this->readOnly) {
				$fullPath = $this->path($path);
				FileSystem::write($fullPath, $content);

				if ($this->gitRepository !== NULL) {
					$this->gitRepository->addFile($fullPath);
				}
			}
		}


		/**
		 * @param  string|\SplFileInfo $old
		 * @param  string|\SplFileInfo $new
		 */
		public function renameFile($old, $new): void
		{
			if (!$this->readOnly) {
				$oldPath = $this->path($old);
				$newPath = $this->path($new);
				FileSystem::rename($oldPath, $newPath);

				if ($this->gitRepository !== NULL) {
					$this->gitRepository->removeFile($oldPath);
					$this->gitRepository->addFile($newPath);
				}
			}
		}


		/**
		 * @param  string|\SplFileInfo $path
		 */
		public function deleteFile($path): void
		{
			if (!$this->readOnly) {
				$fullPath = $this->path($path);
				FileSystem::delete($fullPath);

				if ($this->gitRepository !== NULL) {
					$this->gitRepository->removeFile($fullPath);
				}
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


		/**
		 * Find analyzed files & scanned files
		 * @param  string|string[] $masks
		 * @return \AppendIterator<\SplFileInfo>
		 */
		public function findScannedFiles($masks): \AppendIterator
		{
			if (!is_array($masks)) {
				$masks = [$masks];
			}

			$iterator = new \AppendIterator;

			foreach ($this->scannedPaths as $path) {
				$iterator->append(
					is_file($path)
					? new \ArrayIterator([$path])
					: \Nette\Utils\Finder::findFiles(...$masks)
						->from($path)
						->getIterator()
				);
			}

			return $iterator;
		}


		/**
		 * @param  iterable<string|\SplFileInfo> $files
		 * @param  callable(FileContent, Reporter): void $processor
		 */
		public function processFiles(
			iterable $files,
			callable $processor,
			?string $commitMessage = NULL
		): void
		{
			$wasChanged = FALSE;

			foreach ($files as $file) {
				$this->progress();
				$content = new FileContent($file, $this->readFile($file));

				$processor($content, $this);

				if ($content->wasChanged()) {
					$this->writeFile($file, (string) $content);
					$wasChanged = TRUE;
				}
			}

			if ($wasChanged && $commitMessage !== NULL) {
				$this->commit($commitMessage);
			}
		}


		public function commit(string $message): void
		{
			if ($this->gitRepository !== NULL) {
				if ($this->readOnly) {
					echo $this->console->color('fuchsia', '[COMMIT (DRY RUN)] ' . $message), "\n";

				} elseif ($this->hasGitTrackedChanges()) {
					echo $this->console->color('fuchsia', str_pad('[COMMIT]', 10) . $message), "\n";
					$this->gitRepository->execute('add', '--update'); // only updated items
					$this->gitRepository->commit($message);
				}
			}
		}


		/**
		 * @param  string|\SplFileInfo $path
		 */
		private function path($path): string
		{
			if ($path instanceof \SplFileInfo) {
				return $path->getRealPath();
			}

			return $this->projectDirectory . '/' . $path;
		}


		/**
		 * @param  string|\SplFileInfo $file
		 */
		private function write(
			$file,
			string $type,
			string $message,
			?int $line,
			string $color
		): void
		{
			if ($file instanceof \SplFileInfo) {
				$file = $file->getRealPath();
			}

			$relativePath = $file;

			if (Strings::startsWith($file, $this->projectDirectory)) {
				$relativePath = Strings::substring($file, Strings::length($this->projectDirectory));
			}

			$base = basename($relativePath);
			echo $this->console->color($color, str_pad("[$type]", 10)),
				$base === $relativePath ? '' : $this->console->color('silver', dirname($relativePath) . DIRECTORY_SEPARATOR),
				$this->console->color('white', $base . ($line ? ':' . $line : '')), '    ',
				$this->console->color($color, $message), "\n";
		}


		private function hasGitTrackedChanges(): bool
		{
			if ($this->gitRepository === NULL) {
				return FALSE;
			}

			$this->gitRepository->execute('update-index', '-q', '--refresh');
			$output = $this->gitRepository->execute('status', '--porcelain', '--untracked-files=no');
			return count($output) > 0;
		}
	}
