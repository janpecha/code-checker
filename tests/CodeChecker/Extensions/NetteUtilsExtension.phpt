<?php

use JP\CodeChecker\Extensions;
use JP\CodeChecker\FileContent;
use JP\CodeChecker\MemoryReporter;
use JP\CodeChecker\Version;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Nette\\Object replacement', function () {
	$file = Fixtures::path('Nette/object-replacement.source');
	$reporter = new MemoryReporter($file);
	$netteUtilsExtension = new Extensions\NetteUtilsExtension(new Version('2.4.0'), '*.php');
	$content = FileContent::fromFile($file);

	$netteUtilsExtension->fixNetteObjectUsage($content, $reporter);

	Assert::same([
		'FIX   | Nette: Nette\\Object replaced by Nette\\SmartObject (deprecated in v2.4.0)',
	], $reporter->getMessages());

	Assert::same(Fixtures::load('Nette/object-replacement.expected'), (string) $content);
});
