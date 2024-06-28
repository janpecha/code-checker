<?php

declare(strict_types=1);

use JP\CodeChecker\FileContent;
use JP\CodeChecker\MemoryReporter;
use JP\CodeChecker\Rules;
use JP\CodeChecker\Result;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


test('No fix', function () {
	$reporter = new MemoryReporter;
	$content = new FileContent('test.php', '<?php declare(strict_types=1)');
	$rule = new Rules\Php\DeclareStrictTypesRule;
	$rule->processContent($content, $reporter);
	Assert::same([
	], $reporter->getMessages());
});


test('Fix no indentation', function () {
	$reporter = new MemoryReporter;
	$content = new FileContent('test.php', implode("\n", [
		'<?php',
		'namespace Foo;',
	]));
	$rule = new Rules\Php\DeclareStrictTypesRule;
	$rule->processContent($content, $reporter);
	Assert::same([
		'FIX   | /test.php | Missing declare(strict_types=1)',
	], $reporter->getMessages());
	Assert::same(implode("\n", [
		'<?php',
		'',
		'declare(strict_types=1);',
		'namespace Foo;',
	]), $content->contents);
});


test('Fix no indentation', function () {
	$reporter = new MemoryReporter;
	$content = new FileContent('test.php', implode("\n", [
		'<?php',
		'',
		"\t\tnamespace Foo;",
	]));
	$rule = new Rules\Php\DeclareStrictTypesRule;
	$rule->processContent($content, $reporter);
	Assert::same([
		'FIX   | /test.php | Missing declare(strict_types=1)',
	], $reporter->getMessages());
	Assert::same(implode("\n", [
		'<?php',
		'',
		"\t\tdeclare(strict_types=1);",
		'',
		"\t\tnamespace Foo;",
	]), $content->contents);
});


test('Multiple declares (invalid)', function () {
	$reporter = new MemoryReporter;
	$content = new FileContent('test.php', implode("\n", [
		'<?php',
		'declare(strict_types=1);',
		'declare(strict_types=1);',
		"\t\tnamespace Foo;",
	]));
	$rule = new Rules\Php\DeclareStrictTypesRule;
	$rule->processContent($content, $reporter);
	Assert::same([], $reporter->getMessages());
});
