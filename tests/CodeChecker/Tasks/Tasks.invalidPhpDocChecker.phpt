<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.php', '<?php ?>');
	Tasks::invalidPhpDocChecker($file);
	Assert::equal([], $file->getResult());
});

test('Valid #2', function () {
	$file = new File('file.php', '<?php /** @var */ ?>');
	Tasks::invalidPhpDocChecker($file);
	Assert::equal([], $file->getResult());
});

test('Valid #3', function () {
	$file = new File('file.php', '<?php /* comment */ ?>');
	Tasks::invalidPhpDocChecker($file);
	Assert::equal([], $file->getResult());
});

test('Valid #4', function () {
	$file = new File('file.php', '/* @not php */');
	Tasks::invalidPhpDocChecker($file);
	Assert::equal([], $file->getResult());
});

test('Invalid', function () {
	$file = new File('file.php', '<?php /* @var */ ?>');
	Tasks::invalidPhpDocChecker($file);
	Assert::equal([
		new ResultMessage(ResultType::Warning, 'Missing /** in phpDoc comment', 1),
	], $file->getResult());
});

test('Valid #5', function () {
	$file = new File('file.php', '<?php /* email@gmail.com */ ?>');
	Tasks::invalidPhpDocChecker($file);
	Assert::equal([], $file->getResult());
});
