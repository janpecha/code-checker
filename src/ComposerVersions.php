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
				return new self([]);
			}

			$composerFile = ComposerFile::open($composerFile);
			$versionParser = new \Composer\Semver\VersionParser;
			$packages = [];

			foreach ($composerFile->getRequire() as $packageName => $constraint) {
				$packageConstraint = $versionParser->parseConstraints($constraint);
				$packageVersion = $packageConstraint->getLowerBound()->getVersion();

				if (self::isVersionValid($packageVersion)) {
					$parts = explode('.', $packageVersion);
					$packages[$packageName] = new Version(implode('.', [$parts[0], $parts[1], $parts[2]]));
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
			return (bool) Strings::match($version, '#^(?:0|[1-9]\\d*)\\.(?:0|[1-9]\\d*)\\.(?:0|[1-9]\\d*)\\.(?:0|[1-9]\\d*)\\-.+\\z#');
		}
	}
