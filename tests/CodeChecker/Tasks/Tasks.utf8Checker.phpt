<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.txt', "\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd"); // UTF-8   žluťoučký
	Tasks::utf8Checker($file);
	Assert::equal([], $file->getResult());
});

test('Invalid', function () {
	$file = new File('file.txt', "\xFF");
	Tasks::utf8Checker($file);
	Assert::equal([
		new ResultMessage(ResultType::Error, 'Is not valid UTF-8 file', 1),
	], $file->getResult());
});
