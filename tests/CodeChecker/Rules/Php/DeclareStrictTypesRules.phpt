<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Rules;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


test('No fix', function () {
	$file = new File('test.php', '<?php declare(strict_types=1)');
	$rule = new Rules\Php\DeclareStrictTypesRule;
	$rule->processFile($file);
	Assert::same([
	], $file->getResult());
});


test('Fix no indentation', function () {
	$file = new File('test.php', implode("\n", [
		'<?php',
		'namespace Foo;',
	]));
	$rule = new Rules\Php\DeclareStrictTypesRule;
	$rule->processFile($file);
	Assert::equal([
		new ResultMessage(ResultType::Fix, 'Missing declare(strict_types=1)'),
	], $file->getResult());
	Assert::same(implode("\n", [
		'<?php',
		'',
		'declare(strict_types=1);',
		'namespace Foo;',
	]), $file->contents);
});


test('Fix no indentation', function () {
	$file = new File('test.php', implode("\n", [
		'<?php',
		'',
		"\t\tnamespace Foo;",
	]));
	$rule = new Rules\Php\DeclareStrictTypesRule;
	$rule->processFile($file);
	Assert::equal([
		new ResultMessage(ResultType::Fix, 'Missing declare(strict_types=1)'),
	], $file->getResult());
	Assert::same(implode("\n", [
		'<?php',
		'',
		"\t\tdeclare(strict_types=1);",
		'',
		"\t\tnamespace Foo;",
	]), $file->contents);
});


test('Multiple declares (invalid)', function () {
	$file = new File('test.php', implode("\n", [
		'<?php',
		'declare(strict_types=1);',
		'declare(strict_types=1);',
		"\t\tnamespace Foo;",
	]));
	$rule = new Rules\Php\DeclareStrictTypesRule;
	$rule->processFile($file);
	Assert::same([], $file->getResult());
});
