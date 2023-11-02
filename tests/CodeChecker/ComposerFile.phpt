<?php

use JP\CodeChecker\ComposerFile;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


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
	Assert::true($composerFile->getPhpVersion()->isEqual('8.0.0'));

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '>=7.2.1'
		],
	]);
	Assert::true($composerFile->getPhpVersion()->isEqual('7.2.1'));

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '>=7'
		],
	]);
	Assert::true($composerFile->getPhpVersion()->isEqual('7.0.0'));

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '>=7.4'
		],
	]);
	Assert::true($composerFile->getPhpVersion()->isEqual('7.4.0'));

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '7.2'
		],
	]);
	Assert::true($composerFile->getPhpVersion()->isEqual('7.2.0'));
});


test('getPhpVersion() Hyphenated Version Range', function () {
	$composerFile = new ComposerFile(__DIR__ . '/composer.json', []);
	Assert::null($composerFile->getPhpVersion());

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '8.0 - 8.1',
		],
	]);
	Assert::true($composerFile->getPhpVersion()->isEqual('8.0.0'));

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '7.2.1 - 7.2.2'
		],
	]);
	Assert::true($composerFile->getPhpVersion()->isEqual('7.2.1'));

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '5.6 - 8.2.2'
		],
	]);
	Assert::true($composerFile->getPhpVersion()->isEqual('5.6.0'));

	$composerFile = new ComposerFile(__DIR__ . '/composer.json', [
		'require' => [
			'php' => '5.6.8 - 8.2'
		],
	]);
	Assert::true($composerFile->getPhpVersion()->isEqual('5.6.8'));
});
