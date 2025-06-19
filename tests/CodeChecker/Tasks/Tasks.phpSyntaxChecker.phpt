<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.php', '');
	Tasks::phpSyntaxChecker($file);
	Assert::equal([], $file->getResult());
});

test('Valid #2', function () {
	$file = new File('file.php', '<?php echo 1;');
	Tasks::phpSyntaxChecker($file);
	Assert::equal([], $file->getResult());
});

test('Invalid', function () {
	$file = new File('file.php', '<?php if');
	Tasks::phpSyntaxChecker($file);
	$result = $file->getResult();
	Assert::count(1, $result);
	Assert::same(ResultType::Error, $result[0]->type);
	Assert::contains('syntax error, unexpected end of file', $result[0]->message);
});
