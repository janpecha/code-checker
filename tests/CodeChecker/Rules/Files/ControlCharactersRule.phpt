<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Rules\Files\ControlCharactersRule;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


test('Valid', function () {
	$rule = new ControlCharactersRule;
	$file = new File('test.txt', " \t \n \r");
	$rule->processFile($file);
	Assert::same([], $file->getResult());
});



test('Invalid', function () {
	$rule = new ControlCharactersRule;
	$file = new File('test.txt', "\x00");
	$rule->processFile($file);
	Assert::equal([
		new ResultMessage(ResultType::Error, 'Contains control characters', 1),
	], $file->getResult());
});
