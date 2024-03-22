<?php

declare(strict_types=1);

use JP\CodeChecker\ComposerFile;
use JP\CodeChecker\Version;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


function isVersionEqual(?Version $version, string $requiredValue): bool
{
	return ($version !== NULL) && $version->isEqual($requiredValue);
}


test('getPhpVersion()', function () {
	$composerFile = new ComposerFile(__DIR__ . '/composer.json', []);
	Assert::null($composerFile->getPhpVersion());

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'config' => [
			'platform' => [
				'php' => '8.0',
			],
		],
	]);
	Assert::true(isVersionEqual($composerFile->getPhpVersion(), '8.0.0'));
	Assert::null($composerFile->getMaxPhpVersion());

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '>=7.2.1'
		],
	]);
	Assert::true(isVersionEqual($composerFile->getPhpVersion(), '7.2.1'));
	Assert::null($composerFile->getMaxPhpVersion());

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '>=7'
		],
	]);
	Assert::true(isVersionEqual($composerFile->getPhpVersion(), '7.0.0'));
	Assert::null($composerFile->getMaxPhpVersion());

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '>=7.4'
		],
	]);
	Assert::true(isVersionEqual($composerFile->getPhpVersion(), '7.4.0'));
	Assert::null($composerFile->getMaxPhpVersion());

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '7.2'
		],
	]);
	Assert::true(isVersionEqual($composerFile->getPhpVersion(), '7.2.0'));
	Assert::null($composerFile->getMaxPhpVersion());
});


test('getPhpVersion() Hyphenated Version Range', function () {
	$composerFile = new ComposerFile(__DIR__ . '/composer.json', []);
	Assert::null($composerFile->getPhpVersion());

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '8.0 - 8.1',
		],
	]);
	Assert::true(isVersionEqual($composerFile->getPhpVersion(), '8.0.0'));
	Assert::true(isVersionEqual($composerFile->getMaxPhpVersion(), '8.1.9999'));

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '7.2.1 - 7.2.2'
		],
	]);
	Assert::true(isVersionEqual($composerFile->getPhpVersion(), '7.2.1'));
	Assert::true(isVersionEqual($composerFile->getMaxPhpVersion(), '7.2.2'));

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '5.6 - 8.2.2'
		],
	]);
	Assert::true(isVersionEqual($composerFile->getPhpVersion(), '5.6.0'));
	Assert::true(isVersionEqual($composerFile->getMaxPhpVersion(), '8.2.2'));

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '5.6.8 - 8.2'
		],
	]);
	Assert::true(isVersionEqual($composerFile->getPhpVersion(), '5.6.8'));
	Assert::true(isVersionEqual($composerFile->getMaxPhpVersion(), '8.2.9999'));
});
