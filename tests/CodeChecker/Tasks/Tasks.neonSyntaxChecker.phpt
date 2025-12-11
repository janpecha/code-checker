<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.neon', 'a: b');
	Tasks::neonSyntaxChecker($file);
	Assert::equal([], $file->getResult());
});

test('Invalid', function () {
	$file = new File('file.neon', 'a: b: c');
	Tasks::neonSyntaxChecker($file);
	Assert::equal([
		new ResultMessage(ResultType::Error, 'Unexpected \':\' on line 1 at column 5', 1),
	], $file->getResult());
});
