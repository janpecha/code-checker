<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.txt', "a
a
\tb
\t\tc
");
	Tasks::tabIndentationChecker($file);
	Assert::equal([], $file->getResult());
});


test('Invalid space', function () {
	$file = new File('file.txt', "a
a
 \tb
\t\tc
");
	Tasks::tabIndentationChecker($file);
	Assert::equal([
		new ResultMessage(ResultType::Error, 'Used space to indent instead of tab', 3),
	], $file->getResult());
});


test('Invalid tab', function () {
	$file = new File('file.txt', "a\tb");
	Tasks::tabIndentationChecker($file);
	Assert::equal([
		new ResultMessage(ResultType::Error, 'Found unexpected tabulator', 1),
	], $file->getResult());
});
