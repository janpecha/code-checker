<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Rules;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

test('Nette\\Object replacement', function () {
	$file = Fixtures::path('Nette/object-replacement.source');
	$file = File::fromFile($file);

	$rule = new Rules\Nette\NetteObjectRule(NULL);
	$rule->processFile($file);

	Assert::equal([
		new ResultMessage(ResultType::Fix, 'Nette: Nette\\Object replaced by Nette\\SmartObject (deprecated in v2.4.0)'),
	], $file->getResult());

	Assert::same(Fixtures::load('Nette/object-replacement.expected'), (string) $file);
});
