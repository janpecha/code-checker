<?php

declare(strict_types=1);

use JP\CodeChecker\Utils\PhpCode;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('getDeclarations()', function () {
	Assert::same([
		'strict_types=1',
		'encoding = \'ISO-8859-1\'',
	], PhpCode::getDeclarations('<?php declare(strict_types=1); declare (encoding = \'ISO-8859-1\') ;'));
});
