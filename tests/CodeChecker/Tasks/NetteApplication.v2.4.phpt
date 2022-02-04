<?php

use JP\CodeChecker\Tasks;
use Nette\CodeChecker\Result;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Presenter methods', function () {
	$result = new Result;
	$content = file_get_contents(__DIR__ . '/fixtures/Nette/presenter-methods.source');
	Tasks\NetteApplication::presenterMethods($content, $result);
	Assert::same([
		[Result::FIX, 'Nette: HTTP - method isPost() is deprecated, use isMethod(\'POST\') (deprecated in v2.4.0)', NULL],
	], $result->getMessages());
	Assert::same(file_get_contents(__DIR__ . '/fixtures/Nette/presenter-methods.expected'), $content);
});
