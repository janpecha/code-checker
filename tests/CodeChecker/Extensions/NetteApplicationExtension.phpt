<?php

use JP\CodeChecker\Extensions;
use JP\CodeChecker\FileContent;
use JP\CodeChecker\MemoryReporter;
use JP\CodeChecker\Version;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Presenter methods', function () {
	$file = Fixtures::path('Nette/presenter-methods.source');
	$result = new MemoryReporter($file);
	$content = FileContent::fromFile($file);
	$netteApplicationExtension = new Extensions\NetteApplicationExtension(new Version('2.4.0'), '*Presenter.php');
	$netteApplicationExtension->fixHttpMethodsInPresenters($content, $result);

	Assert::same([
		'FIX   | Nette: HTTP - method isPost() is deprecated, use isMethod(\'POST\') (deprecated in v2.4.0)',
	], $result->getMessages());

	Assert::same(Fixtures::load('Nette/presenter-methods.expected'), (string) $content);
});
