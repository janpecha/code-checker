<?php

declare(strict_types=1);

use JP\CodeChecker\ComposerVersions;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('Basic', function () {
	$versions = ComposerVersions::create(__DIR__ . '/fixtures/ComposerVersions/composer.json');

	Assert::true($versions->hasPackage('czproject/assert'));
	Assert::false($versions->hasPackage('some/package'));
	Assert::true($versions->getVersion('czproject/assert')->isEqual('1.4.1'));

	Assert::false($versions->hasPackage('janpecha/code-checker')); // root package
});


test('Dev packages', function () {
	$versions = ComposerVersions::create(__DIR__ . '/fixtures/ComposerVersions/composer.json');

	Assert::true($versions->hasPackage('nette/tester'));
	Assert::true($versions->getVersion('nette/tester')->isEqual('2.4.0'));
});
