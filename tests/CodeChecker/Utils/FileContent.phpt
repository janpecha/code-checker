<?php

use JP\CodeChecker\Utils\FileContent;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('detectIndentation()', function () {
	Assert::same('', FileContent::detectIndentation(''));
	Assert::same('', FileContent::detectIndentation('test'));
	Assert::same('', FileContent::detectIndentation("\n\ntest\ntest\n"));
	Assert::same("\t", FileContent::detectIndentation("\n\ttest"));
	Assert::same("  \t", FileContent::detectIndentation("a\n  \ttest"));
	Assert::same('', FileContent::detectIndentation("a\ntest\n\ttest"));
	Assert::same("\t\t", FileContent::detectIndentation(implode("\n", [
		'<?php',
		'',
		"\t\tnamespace Foo;",
	])));
});
