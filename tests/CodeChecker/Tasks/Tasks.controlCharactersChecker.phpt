<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.txt', " \t \n \r");
	Tasks::controlCharactersChecker($file);
	Assert::equal([], $file->getResult());
});

test('Invalid', function () {
	$file = new File('file.txt', "\x00");
	Tasks::controlCharactersChecker($file);
	Assert::equal([
		new ResultMessage(ResultType::Error, 'Contains control characters', 1),
	], $file->getResult());
});
