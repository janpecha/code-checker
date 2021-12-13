<?php

	declare(strict_types=1);

	namespace JP\CodeChecker;

	use Nette\Utils\Strings;


	/**
	 * @todo move to inteve/types
	 */
	class ComposerVersions
	{
		/** @var array<string, Version> */
		private $packages;


		/**
		 * @param array<string, Version> $packages
		 */
		private function __construct(array $packages)
		{
			$this->packages = $packages;
		}


		public function hasPackage(string $package): bool
		{
			return isset($this->packages[$package]);
		}


		public function getVersion(string $package): Version
		{
			if (!isset($this->packages[$package])) {
				throw new \RuntimeException("Package '$package' not found.");
			}

			return $this->packages[$package];
		}


		public static function create(string $composerFile): self
		{
			if (!is_file($composerFile)) {
				throw new \RuntimeException("Composer file '$composerFile' not found.");
			}

			$path = dirname($composerFile) . '/vendor/composer/installed.php';

			if (!is_file($path)) {
				throw new \RuntimeException("Missing file '$path', run `composer update` or `composer install`.");
			}

			$data = require $path;
			$packages = [];

			if (isset($data['versions']) && is_array($data['versions'])) {
				foreach ($data['versions'] as $packageName => $packageData) {
					if (isset($packageData['version']) && is_string($packageData['version']) && $packageData['version'] !== '' && self::isVersionValid($packageData['version'])) {
						$parts = explode('.', $packageData['version']);
						$packages[$packageName] = new Version(implode('.', [$parts[0], $parts[1], $parts[2]]));
					}
				}
			}

			return new self($packages);
		}


		/**
		 * @param  string $version
		 * @return bool
		 */
		private static function isVersionValid($version)
		{
			return (bool) Strings::match($version, '#^(?:0|[1-9]\\d*)\\.(?:0|[1-9]\\d*)\\.(?:0|[1-9]\\d*)\\.(?:0|[1-9]\\d*)\\z#');
		}
	}
