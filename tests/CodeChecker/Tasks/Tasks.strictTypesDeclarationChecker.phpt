<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Tasks\Tasks;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


foreach (['ok1.php', 'ok2.php', 'ok3.php'] as $fileName) {
	$contents = file_get_contents(__DIR__ . '/../fixtures/strict-types/' . $fileName);
	assert(is_string($contents));
	$file = new File($fileName, $contents);
	Tasks::strictTypesDeclarationChecker($file);
	Assert::equal([], $file->getResult());
}

foreach (['ko1.php', 'ko2.php', 'ko3.php'] as $fileName) {
	$contents = file_get_contents(__DIR__ . '/../fixtures/strict-types/' . $fileName);
	assert(is_string($contents));
	$file = new File($fileName, $contents);
	Tasks::strictTypesDeclarationChecker($file);
	Assert::equal([
		new ResultMessage(ResultType::Error, 'Missing declare(strict_types=1)'),
	], $file->getResult());
}
