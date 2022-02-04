<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use Nette\Utils\Arrays;


	class ComposerFile
	{
		/** @var string */
		private $path;

		/** @var array */
		private $data;


		public function __construct(string $path, array $data)
		{
			$this->path = $path;
			$this->data = $data;
		}


		public function getPath(): string
		{
			return $this->path;
		}


		public function getRequire(): array
		{
			return Arrays::get($this->data, 'require', []);
		}


		public function getRequireDev(): array
		{
			return Arrays::get($this->data, 'require-dev', []);
		}


		/**
		 * @return string[]
		 */
		public function getLicense(): array
		{
			$licenses = Arrays::get($this->data, 'license', []);

			if (is_string($licenses)) {
				$licenses = [$licenses];
			}

			return $licenses;
		}


		public static function open(string $path): self
		{
			$content = \Nette\Utils\FileSystem::read($path);
			$data = \Nette\Utils\Json::decode($content, \Nette\Utils\Json::FORCE_ARRAY);
			return new self($path, $data);
		}
	}
