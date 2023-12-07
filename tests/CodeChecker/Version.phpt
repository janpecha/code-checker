<?php

declare(strict_types=1);

use JP\CodeChecker\Version;
use Nette\CodeChecker\Result;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('Valid version', function () {
	$v1 = new Version('2.4.2');

	Assert::true($v1->isEqual('2.4.2'));
	Assert::false($v1->isEqual('2.4.3'));

	Assert::true($v1->isEqualOrGreater('2.4.2'));
	Assert::false($v1->isEqualOrGreater('2.4.3'));
	Assert::true($v1->isEqualOrGreater('2.4.0'));

	Assert::same('2.4', $v1->toMinorString());
});


test('Invalid version', function () {
	Assert::exception(function () {
		new Version('2.4');
	}, \CzProject\Assert\AssertException::class, 'Invalid version string.');
});
