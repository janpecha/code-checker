<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid #1', function () {
	$file = new File('file.json', 'true');
	Tasks::jsonSyntaxChecker($file);
	Assert::equal([], $file->getResult());
});

test('Valid #2', function () {
	$file = new File('file.json', '{"a":1}');
	Tasks::jsonSyntaxChecker($file);
	Assert::equal([], $file->getResult());
});

test('Invalid', function () {
	$file = new File('file.json', '{"a":1');
	Tasks::jsonSyntaxChecker($file);
	Assert::count(1, $file->getResult());
});

test('Invalid empty', function () {
	$file = new File('file.json', '');
	Tasks::jsonSyntaxChecker($file);
	Assert::count(1, $file->getResult());
});
