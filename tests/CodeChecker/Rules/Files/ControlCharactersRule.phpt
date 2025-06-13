<?php

declare(strict_types=1);

use JP\CodeChecker\FileContent;
use JP\CodeChecker\MemoryReporter;
use JP\CodeChecker\Rules\Files\ControlCharactersRule;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


test('Valid', function () {
	$reporter = new MemoryReporter;
	$rule = new ControlCharactersRule;
	$content = new FileContent('test.txt', " \t \n \r");
	$rule->processContent($content, $reporter);
	Assert::same([], $reporter->getMessages());
});



test('Invalid', function () {
	$reporter = new MemoryReporter;
	$rule = new ControlCharactersRule;
	$content = new FileContent('test.txt', "\x00");
	$rule->processContent($content, $reporter);
	Assert::same([
		'ERROR | test.txt:1 | Contains control characters',
	], $reporter->getMessages());
});
