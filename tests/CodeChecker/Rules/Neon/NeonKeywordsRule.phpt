<?php

declare(strict_types=1);

use JP\CodeChecker\FileContent;
use JP\CodeChecker\MemoryReporter;
use JP\CodeChecker\Rules\Neon\NeonKeywordsRule;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


test('Deprecated keywords on/off', function () {
	$reporter = new MemoryReporter;
	$rule = new NeonKeywordsRule;
	$content = new FileContent('test.neon', implode("\n", [
		'first: on',
		'second: off',
		'third: 123',
	]));
	$rule->processContent($content, $reporter);
	Assert::same([
		'FIX   | /test.neon | Neon: keywords on/off changed to yes/no (deprecated in v3.1)',
	], $reporter->getMessages());
	Assert::same(implode("\n", [
		'first: yes',
		'second: no',
		'third: 123',
	]), $content->contents);
});
