<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Valid', function () {
	$file = new File('file.php', "a
a
\tb
\t\tc
");
	Tasks::tabIndentationPhpChecker($file);
	Assert::equal([], $file->getResult());
});


test('Valid #2', function () {
	$file = new File('file.php', "<?php echo \"a\tb\" ?>");
	Tasks::tabIndentationPhpChecker($file);
	Assert::equal([], $file->getResult());
});


test('Valid #3', function () {
	$file = new File('file.php', "<?php echo 'a\tb' ?>");
	Tasks::tabIndentationPhpChecker($file);
	Assert::equal([], $file->getResult());
});


test('Invalid spaces', function () {
	$file = new File('file.php', "<?php echo '
a
b
' ?>
a
 \tb
");
	Tasks::tabIndentationPhpChecker($file);
	Assert::equal([
		new ResultMessage(ResultType::Error, 'Used space to indent instead of tab', 6),
	], $file->getResult());
});


test('Valid #4', function () {
	$file = new File('file.php', "<?php echo <<<'XX'\n\n\tXX;\n?>");
	Tasks::tabIndentationPhpChecker($file);
	Assert::equal([], $file->getResult());
});
