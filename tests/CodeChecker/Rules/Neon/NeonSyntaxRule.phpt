<?php

declare(strict_types=1);

use JP\CodeChecker\FileContent;
use JP\CodeChecker\MemoryReporter;
use JP\CodeChecker\Rules\Neon\NeonSyntaxRule;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


test('valid syntax', function () {
	$reporter = new MemoryReporter;
	$rule = new NeonSyntaxRule;
	$content = new FileContent('test.neon', 'a: b');
	$rule->processContent($content, $reporter);

	Assert::same([
	], $reporter->getMessages());
});


test('invalid syntax', function () {
	$reporter = new MemoryReporter;
	$rule = new NeonSyntaxRule;
	$content = new FileContent('test.neon', 'a: b: c');
	$rule->processContent($content, $reporter);

	Assert::same([
		'ERROR | /test.neon:1 | Unexpected \':\' on line 1, column 5.',
	], $reporter->getMessages());
});
