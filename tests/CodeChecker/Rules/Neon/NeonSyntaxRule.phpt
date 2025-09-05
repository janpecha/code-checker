<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\MemoryReporter;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Rules\Neon\NeonSyntaxRule;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


test('valid syntax', function () {
	$rule = new NeonSyntaxRule;
	$file = new File('test.neon', 'a: b');
	$rule->processFile($file);

	Assert::same([], $file->getResult());
});


test('invalid syntax', function () {
	$rule = new NeonSyntaxRule;
	$file = new File('test.neon', 'a: b: c');
	$rule->processFile($file);

	Assert::equal([
		new ResultMessage(ResultType::Error, 'Unexpected \':\' on line 1, column 5.', 1),
	], $file->getResult());
});
