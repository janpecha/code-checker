<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.latte', '');
	Tasks::latteSyntaxChecker($file); // no error
	Assert::equal([], $file->getResult());
});

test('Unknow tag', function () {
	$file = new File('file.latte', '{hello}');
	Tasks::latteSyntaxChecker($file); // ignores unknown macros
	Assert::equal([
		new ResultMessage(ResultType::Warning, 'Unknown macro {hello}', 1),
	], $file->getResult());
});

test('Invalid Latte syntax', function () {
	$file = new File('file.latte', '{hello');
	Tasks::latteSyntaxChecker($file);
	Assert::equal([
		new ResultMessage(ResultType::Error, 'Malformed macro', 1),
	], $file->getResult());
});

test('Invalid PHP syntax', function () {
	$file = new File('file.latte', '{var $x = +}');
	Tasks::latteSyntaxChecker($file); // invalid PHP code
	Assert::count(1, $file->getResult());
	$result = $file->getResult();
	Assert::match('Invalid PHP code: Parse error: syntax error, unexpected token ";"%a%', $result[0]->message);
});
