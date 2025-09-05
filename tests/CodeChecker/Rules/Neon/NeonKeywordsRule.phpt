<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Rules\Neon\NeonKeywordsRule;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


test('Deprecated keywords on/off', function () {
	$rule = new NeonKeywordsRule;
	$file = new File('test.neon', implode("\n", [
		'first: on',
		'second: off',
		'third: 123',
	]));
	$rule->processFile($file);
	Assert::equal([
		new ResultMessage(ResultType::Fix, 'Neon: keywords on/off changed to yes/no (deprecated in v3.1)')
	], $file->getResult());
	Assert::same(implode("\n", [
		'first: yes',
		'second: no',
		'third: 123',
	]), $file->contents);
});
