<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.txt', 'hello');
	Tasks::bomFixer($file);
	Assert::equal([], $file->getResult());
	Assert::same('hello', $file->contents);
});

test('Invalid', function () {
	$file = new File('file.txt', "\xEF\xBB\xBFhello");
	Tasks::bomFixer($file);
	Assert::equal([
		new ResultMessage(ResultType::Fix, 'contains BOM', 1),
	], $file->getResult());
	Assert::same('hello', $file->contents);
});
