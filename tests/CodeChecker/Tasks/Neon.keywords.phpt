<?php

use JP\CodeChecker\Tasks;
use JP\CodeChecker\Result;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Deprecated keywords on/off', function () {
	$result = new Result;
	$content = implode("\n", [
		'first: on',
		'second: off',
		'third: 123',
	]);
	Tasks\Neon::keywordsFixer($content, $result);
	Assert::same([
		[Result::FIX, 'Neon: keywords on/off changed to yes/no (deprecated in v3.1)', NULL],
	], $result->getMessages());
	Assert::same(implode("\n", [
		'first: yes',
		'second: no',
		'third: 123',
	]), $content);
});
