<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid PHP', function () {
	$file = new File('file.php', '<?php $a = array(array(1 + (1))) ?>');
	Tasks::shortArraySyntaxFixer($file);
	Assert::count(2, $file->getResult());
	Assert::same('<?php $a = [[1 + (1)]] ?>', $file->contents);
});

test('Plain text', function () {
	$file = new File('file.php', '$a = array(array(1 + (1)))');
	Tasks::shortArraySyntaxFixer($file);
	Assert::equal([], $file->getResult());
	Assert::same('$a = array(array(1 + (1)))', $file->contents);
});
