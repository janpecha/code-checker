<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Rules;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

test('isPost() fixer', function () {
	$file = Fixtures::path('Nette/presenter-methods.source');
	$file = File::fromFile($file);

	$rule = new Rules\Nette\NettePresenterHttpMethodsFixerRule(NULL);
	$rule->processFile($file);

	Assert::equal([
		new ResultMessage(ResultType::Fix, 'Nette: HTTP - method isPost() is deprecated, use isMethod(\'POST\') (deprecated in v2.4.0)'),
	], $file->getResult());

	Assert::same(Fixtures::load('Nette/presenter-methods.expected'), (string) $file);
});
