<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Extensions;

	use JP\CodeChecker;
	use JP\CodeChecker\Utils\Paths;
	use JP\CodeChecker\Utils\Yaml;
	use JP\CodeChecker\Version;
	use Nette\Utils\Json;
	use Nette\Utils\Strings;


	/**
	 * @link https://github.com/janpecha/actions
	 */
	class JanpechaActionsExtension implements CodeChecker\Extension
	{
		const BuildPath = '.github/workflows/build.yml';
		const BuildPathAlias = '.github/workflows/build.yaml';
		const CodingStylePath = '.github/workflows/coding-style.yml';
		const StaticAnalysisPath = '.github/workflows/static-analysis.yml';
		const TestsPath = '.github/workflows/tests.yml';

		/** @var string */
		private $projectType;

		/** @var Version */
		private $phpVersion;

		/** @var Version */
		private $maxPhpVersion;

		/** @var string */
		private $workingDirectory;

		/** @var bool */
		private $enableCodeCheckerJob;

		/** @var bool */
		private $enableTestsJob;

		/** @var string[] */
		private static $phpstanConfigPaths = [
			'phpstan.neon',
			'tests/phpstan.neon',
		];

		/** @var string[] */
		private static $phpVersions = [
			'5.6.0',
			'7.0.0',
			'7.1.0',
			'7.2.0',
			'7.3.0',
			'7.4.0',
			'8.0.0',
			'8.1.0',
			'8.2.0',
			'8.3.0',
		];


		public function __construct(
			string $projectType,
			Version $phpVersion,
			Version $maxPhpVersion,
			bool $enableCodeCheckerJob,
			bool $enableTestsJob,
			string $workingDirectory = '.'
		)
		{
			$this->projectType = $projectType;
			$this->phpVersion = $phpVersion;
			$this->maxPhpVersion = $maxPhpVersion;
			$this->enableCodeCheckerJob = $enableCodeCheckerJob;
			$this->enableTestsJob = $enableTestsJob;
			$this->workingDirectory = $workingDirectory !== '' ? $workingDirectory : '.';
		}


		public function run(CodeChecker\Engine $engine): void
		{
			$this->processBuildFile($engine);
		}


		private function processBuildFile(CodeChecker\Engine $engine): void
		{
			if (!$engine->existsFile(self::BuildPath) && $engine->existsFile(self::BuildPathAlias)) {
				$engine->reportFixInFile('File renamed to ' . basename(self::BuildPath), self::BuildPathAlias);
				$engine->renameFile(self::BuildPathAlias, self::BuildPath);
				$engine->commit('GitHub Actions: renamed ' . basename(self::BuildPathAlias) . ' to ' . basename(self::BuildPath));
			}

			$oldContent = '';
			$created = TRUE;
			$yaml = [];

			if ($engine->existsFile(self::BuildPath)) {
				$created = FALSE;
				$oldContent = $engine->readFile(self::BuildPath);
				$yaml = Yaml::decode($oldContent);
				assert(is_array($yaml));
			}

			if (!isset($yaml['name'])) {
				$yaml['name'] = 'Build';
			}

			if (!isset($yaml['on'])) {
				$yaml['on'] = [
					'push' => [
						'branches' => [
							'master',
						],
						'tags' => [
							'v*',
						],
					],
					'pull_request' => [],
				];
			}

			if (!isset($yaml['jobs'])) {
				$yaml['jobs'] = [];
			}

			$yaml = $this->createTestsJob($yaml, $engine);
			$yaml = $this->createCodingStyleJob($yaml, $engine);
			$yaml = $this->createStaticAnalysisJob($yaml, $engine);
			assert(isset($yaml['jobs']) && is_array($yaml['jobs']));

			if (count($yaml['jobs']) > 0) {
				$newContent = Yaml::encode($yaml);

				if ($newContent !== $oldContent) {
					$engine->reportFixInFile($created ? 'File created' : 'File updated', self::BuildPath);
					$engine->writeFile(self::BuildPath, $newContent);
					$engine->commit('GitHub Actions: ' . ($created ? 'added' : 'updated') . ' ' . basename(self::BuildPath));
				}
			}
		}


		/**
		 * @param  array<string, mixed> $yaml
		 * @return array<string, mixed>
		 */
		private function createCodingStyleJob(array $yaml, CodeChecker\Engine $engine): array
		{
			if (!$this->enableCodeCheckerJob) {
				return $yaml;
			}

			assert(isset($yaml['jobs']) && is_array($yaml['jobs']));

			if (!isset($yaml['jobs']['coding-style'])) {
				$engine->reportFixInFile("Created job 'coding-style'", self::BuildPath);
				$yaml['jobs']['coding-style'] = [
					'uses' => 'janpecha/actions/.github/workflows/code-checker.yml@master',
				];

				if ($engine->existsFile(self::CodingStylePath)) {
					$engine->reportFixInFile('File removed', self::CodingStylePath);
					$engine->deleteFile(self::CodingStylePath);
				}
			}

			return $yaml;
		}


		/**
		 * @param  array<string, mixed> $yaml
		 * @return array<string, mixed>
		 */
		private function createStaticAnalysisJob(array $yaml, CodeChecker\Engine $engine): array
		{
			assert(isset($yaml['jobs']) && is_array($yaml['jobs']));

			if (!isset($yaml['jobs']['static-analysis']) && ($phpstanConfigPath = $this->findPhpStanConfig($engine)) !== NULL) {
				$engine->reportFixInFile("Created job 'static-analysis'", self::BuildPath);
				$yaml['jobs']['static-analysis'] = [
					'uses' => 'janpecha/actions/.github/workflows/phpstan.yml@master',
					'with' => [],
				];

				if ($phpstanConfigPath !== 'phpstan.neon') { // default value
					$yaml['jobs']['static-analysis']['with']['configFile'] = $phpstanConfigPath;
				}

				if ($this->workingDirectory !== '.') { // default value
					$yaml['jobs']['static-analysis']['with']['workingDirectory'] = $this->workingDirectory;
				}

				if ($engine->existsFile(self::StaticAnalysisPath)) {
					$engine->reportFixInFile('File removed', self::StaticAnalysisPath);
					$engine->deleteFile(self::StaticAnalysisPath);
				}
			}

			if (isset($yaml['jobs']['static-analysis']['with'])) {
				$yaml['jobs']['static-analysis']['with']['phpVersions'] = $this->formatPhpVersions();
			}

			if (isset($yaml['jobs']['static-analysis']['with']) && count($yaml['jobs']['static-analysis']['with']) === 0) {
				unset($yaml['jobs']['static-analysis']['with']);
			}

			return $yaml;
		}


		/**
		 * @param  array<string, mixed> $yaml
		 * @return array<string, mixed>
		 */
		private function createTestsJob(array $yaml, CodeChecker\Engine $engine): array
		{
			if (!$this->enableTestsJob) {
				return $yaml;
			}

			assert(isset($yaml['jobs']) && is_array($yaml['jobs']));

			if (!isset($yaml['jobs']['tests'])) {
				$engine->reportFixInFile("Created job 'tests'", self::BuildPath);
				$yaml['jobs']['tests'] = [
					'uses' => NULL,
					'with' => [],
				];

				if ($this->projectType === 'project') {
					$yaml['jobs']['tests']['uses'] = 'janpecha/actions/.github/workflows/nette-tester-project.yml@master';
					$yaml['jobs']['tests']['with']['phpVersions'] = $this->formatPhpVersions();

				} else { // library
					$yaml['jobs']['tests']['uses'] = 'janpecha/actions/.github/workflows/nette-tester-library.yml@master';
					$yaml['jobs']['tests']['with']['phpVersions'] = $this->formatPhpVersions();
					$yaml['jobs']['tests']['with']['lowestDependencies'] = TRUE;
				}

				if ($this->workingDirectory !== '.') { // default value
					$yaml['jobs']['tests']['with']['workingDirectory'] = $this->workingDirectory;
				}

				if (count($yaml['jobs']['tests']['with']) === 0) { // @phpstan-ignore identical.alwaysFalse
					unset($yaml['jobs']['tests']['with']);
				}

				if ($engine->existsFile(self::TestsPath)) {
					$engine->reportFixInFile('File removed', self::TestsPath);
					$engine->deleteFile(self::TestsPath);
				}

			} else {
				if ($this->projectType === 'project') {
					$yaml['jobs']['tests']['with']['phpVersions'] = $this->formatPhpVersions();

				} else { // library
					$yaml['jobs']['tests']['with']['phpVersions'] = $this->formatPhpVersions();
				}
			}

			return $yaml;
		}


		public static function configure(CodeChecker\CheckerConfig $config): void
		{
			$workingDirectory = Paths::shortPath(
				dirname($config->getComposerFile()->getPath()),
				$config->getProjectDirectory(),
			);

			$config->addExtension(new self(
				$config->getComposerFile()->getType() ?? 'library',
				$config->getPhpVersion(),
				$config->getMaxPhpVersion(),
				$config->getConfigFile() !== NULL,
				$config->getComposerVersions()->hasPackage('nette/tester'),
				$workingDirectory
			));
		}


		private function findPhpStanConfig(CodeChecker\Engine $engine): ?string
		{
			foreach (self::$phpstanConfigPaths as $phpstanConfigPath) {
				if ($engine->existsFile($this->workingDirectory . '/' . $phpstanConfigPath)) {
					return $phpstanConfigPath;
				}
			}

			return NULL;
		}


		private function formatPhpVersions(): string
		{
			$s = Json::encode($this->findPhpVersions());
			return str_replace('","', '", "', $s);
		}


		/**
		 * @return string[]
		 */
		private function findPhpVersions(): array
		{
			$res = [];

			foreach (self::$phpVersions as $phpVersion) {
				if ($this->phpVersion->compare($phpVersion, '<=') && $this->maxPhpVersion->compare($phpVersion, '>=')) {
					$res[] = (string) Strings::before($phpVersion, '.', 2); // minor
				}
			}

			return $res;
		}
	}
