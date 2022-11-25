<?php

use JP\CodeChecker\Tasks;
use JP\CodeChecker\Result;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Nette\\Object replacement', function () {
	$result = new Result;
	$content = file_get_contents(__DIR__ . '/fixtures/Nette/object-replacement.source');
	Tasks\NetteUtils::netteObjectFixer($content, $result);
	Assert::same([
		[Result::FIX, 'Nette: Nette\\Object replaced by Nette\\SmartObject (deprecated in v2.4.0)', NULL],
	], $result->getMessages());
	Assert::same(file_get_contents(__DIR__ . '/fixtures/Nette/object-replacement.expected'), $content);
});
