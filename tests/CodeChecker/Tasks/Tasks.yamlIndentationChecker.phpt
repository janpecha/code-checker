<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.yaml', 'hello');
	Tasks::yamlIndentationChecker($file);
	Assert::equal([], $file->getResult());
});

test('Invalid', function () {
	$file = new File('file.yaml', "\thello");
	Tasks::yamlIndentationChecker($file);
	Assert::equal([
		new ResultMessage(ResultType::Error, 'Used tabs to indent instead of spaces', 1),
	], $file->getResult());
});
