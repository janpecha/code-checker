<?php

use JP\CodeChecker\Tasks;
use JP\CodeChecker\Version;
use JP\CodeChecker\Result;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Basic', function () {
	$result = new Result;
	$latte = new Tasks\Latte(new Version('2.4.0'));
	$content = file_get_contents(__DIR__ . '/fixtures/Latte/template.source');
	$latte->deprecatedFixer($content, $result);
	$latte->deprecatedChecker($content, $result);
	Assert::same([
		[Result::FIX, 'Latte: filter |nl2br replaced by filter |breaklines  (deprecated in v2.4)', NULL],
		[Result::FIX, 'Latte: tag {? expr} replaced by tag {php}  (deprecated in v2.4)', NULL],
		[Result::ERROR, 'Latte: template contains <?php open tag (deprecated in v2.4)', NULL],
		[Result::WARNING, 'Latte: uses deprecated variable $template (deprecated in v2.4)', NULL],
		[Result::WARNING, 'Latte: uses deprecated variable $_l (deprecated in v2.4)', NULL],
		[Result::WARNING, 'Latte: uses deprecated variable $_g (deprecated in v2.4)', NULL],
		[Result::ERROR, 'Latte: uses internal variable $__* (disabled in v2.9)', NULL],
		[Result::ERROR, 'Latte: uses internal variable $ʟ_* (added in v2.8)', NULL],
		[Result::WARNING, 'Latte: uses deprecated tag {includeblock} (deprecated in v2.4)', NULL],
		[Result::WARNING, 'Latte: uses deprecated tag {use} (deprecated in v2.4)', NULL],
		[Result::WARNING, 'Latte: uses deprecated tag {status} (deprecated in v2.4)', NULL],
		[Result::ERROR, 'Latte: uses deprecated tag {!expr}, use filter |noescape instead (deprecated in v2.4)', NULL],
		[Result::ERROR, 'Latte: uses deprecated filter |nl2br (deprecated in v2.4)', NULL],
		[Result::ERROR, 'Latte: uses deprecated filter |safeurl (deprecated in v2.4)', NULL],
	], $result->getMessages());
	Assert::same(file_get_contents(__DIR__ . '/fixtures/Latte/template.expected'), $content);
});
