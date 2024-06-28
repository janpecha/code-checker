<?php

declare(strict_types=1);

use JP\CodeChecker\FileContent;
use JP\CodeChecker\MemoryReporter;
use JP\CodeChecker\Rules;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

test('Nette\\Object replacement', function () {
	$file = Fixtures::path('Nette/object-replacement.source');
	$reporter = new MemoryReporter($file);
	$content = FileContent::fromFile($file);

	$rule = new Rules\Nette\NetteObjectRule;
	$rule->processContent($content, $reporter);

	Assert::same([
		'FIX   | Nette: Nette\\Object replaced by Nette\\SmartObject (deprecated in v2.4.0)',
	], $reporter->getMessages());

	Assert::same(Fixtures::load('Nette/object-replacement.expected'), (string) $content);
});
