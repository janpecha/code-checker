<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Fix', function () {
	$file = new File('file.txt', "a\r\nb\nc");
	Tasks::newlineNormalizer($file);
	Assert::equal([
		new ResultMessage(ResultType::Fix, 'contains non-system line-endings', (PHP_EOL === "\n" ? 1 : 2)),
	], $file->getResult());
	Assert::same('a' . PHP_EOL . 'b' . PHP_EOL . 'c', $file->contents);
});

test('Invalid', function () {
	$file = new File('file.txt', 'a' . PHP_EOL . 'b');
	Tasks::newlineNormalizer($file);
	Assert::equal([], $file->getResult());
});
