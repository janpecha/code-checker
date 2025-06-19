<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.txt', 'hello
world
');
	Tasks::trailingWhiteSpaceFixer($file);
	Assert::equal([], $file->getResult());
});


test('Trailing spaces', function () { // trailing spaces
	$file = new File('file.txt', 'hello
world

');
	Tasks::trailingWhiteSpaceFixer($file);
	Assert::count(1, $file->getResult());
	Assert::same('hello
world
', $file->contents);
});


test('Missing spaces', function () { // missing spaces
	$file = new File('file.txt', 'hello');
	Tasks::trailingWhiteSpaceFixer($file);
	Assert::count(1, $file->getResult());
	Assert::same('hello' . PHP_EOL, $file->contents);
});


test('Empty', function () { // empty
	$file = new File('file.txt', '');
	Tasks::trailingWhiteSpaceFixer($file);
	Assert::equal([], $file->getResult());
});
