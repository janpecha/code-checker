<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\Processors\PhpProcessor;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Rules;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


test('No fix', function () {
	$file = new File('test.php', '<?php class Foo { function bar(?callable $cb = NULL) {} }');
	$rule = new Rules\Php\PhpNullableParameterFixerRule;
	$processor = new PhpProcessor(['*.php'], [], [$rule]);
	$processor->processFile($file);
	Assert::same([
	], $file->getResult());
});


test('Fix', function () {
	$file = new File('test.php', '<?php class Foo { function bar(callable $cb = NULL) {} }');
	$rule = new Rules\Php\PhpNullableParameterFixerRule;
	$processor = new PhpProcessor(['*.php'], [], [$rule]);
	$processor->processFile($file);
	Assert::equal([
		new ResultMessage(ResultType::Fix, 'Fixed nullable parameter $cb in method Foo::bar()'),
	], $file->getResult());
	Assert::same('<?php class Foo { function bar(?callable $cb = NULL) {} }', $file->contents);
});
