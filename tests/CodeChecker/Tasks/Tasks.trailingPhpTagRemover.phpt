<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Fix', function () {
	$file = new File('file.php', '<?php echo 1 ?>');
	Tasks::trailingPhpTagRemover($file);
	Assert::equal([
		new ResultMessage(ResultType::Fix, 'contains closing PHP tag ?>', 1),
	], $file->getResult());
	Assert::same('<?php echo 1 ', $file->contents);
});

test('Fix #2', function () {
	$file = new File('file.php', "<?php echo 1 ?>\r\n ");
	Tasks::trailingPhpTagRemover($file);
	Assert::equal([
		new ResultMessage(ResultType::Fix, 'contains closing PHP tag ?>', 1),
	], $file->getResult());
	Assert::same('<?php echo 1 ', $file->contents);
});

test('Valid', function () {
	$file = new File('file.php', '<?php echo 1');
	Tasks::trailingPhpTagRemover($file);
	Assert::equal([], $file->getResult());
});
