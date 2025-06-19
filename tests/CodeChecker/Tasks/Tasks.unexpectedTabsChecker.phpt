<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.txt', "a\nb");
	Tasks::unexpectedTabsChecker($file);
	Assert::equal([], $file->getResult());
});

test('Invalid', function () {
	$file = new File('file.txt', "\t");
	Tasks::unexpectedTabsChecker($file);
	Assert::equal([
		new ResultMessage(ResultType::Error, 'Found unexpected tabulator', 1),
	], $file->getResult());
});
