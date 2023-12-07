<?php

use JP\CodeChecker\Tasks;
use JP\CodeChecker\Result;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('No fix', function () {
	$result = new Result;
	$content = '<?php declare(strict_types=1)';
	Tasks\Php::strictTypesDeclarationFixer($content, $result);
	Assert::same([
	], $result->getMessages());
});


test('Fix no indentation', function () {
	$result = new Result;
	$content = implode("\n", [
		'<?php',
		'namespace Foo;',
	]);
	Tasks\Php::strictTypesDeclarationFixer($content, $result);
	Assert::same([
		[Result::FIX, 'Added missing declare(strict_types=1)', NULL],
	], $result->getMessages());
	Assert::same(implode("\n", [
		'<?php',
		'',
		'declare(strict_types=1);',
		'namespace Foo;',
	]), $content);
});


test('Fix no indentation', function () {
	$result = new Result;
	$content = implode("\n", [
		'<?php',
		'',
		"\t\tnamespace Foo;",
	]);
	Tasks\Php::strictTypesDeclarationFixer($content, $result);
	Assert::same([
		[Result::FIX, 'Added missing declare(strict_types=1)', NULL],
	], $result->getMessages());
	Assert::same(implode("\n", [
		'<?php',
		'',
		"\t\tdeclare(strict_types=1);",
		'',
		"\t\tnamespace Foo;",
	]), $content);
});
