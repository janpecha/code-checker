<?php

use JP\CodeChecker\Extensions;
use JP\CodeChecker\FileContent;
use JP\CodeChecker\MemoryReporter;
use JP\CodeChecker\Version;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Basic', function () {
	$file = Fixtures::path('Latte/template.source');
	$reporter = new MemoryReporter($file);
	$latteExtension = new Extensions\LatteExtension(new Version('2.4.0'), '*.latte');
	$content = FileContent::fromFile($file);

	$latteExtension->fixDeprecated($content, $reporter);
	$latteExtension->checkDeprecated($content, $reporter);

	Assert::same([
		'FIX   | Latte: filter |nl2br replaced by filter |breaklines (deprecated in v2.4)',
		'FIX   | Latte: tag {? expr} replaced by tag {php} (deprecated in v2.4)',
		'ERROR | Latte: template contains <?php open tag (deprecated in v2.4)',
		'WARN  | Latte: uses deprecated variable $template (deprecated in v2.4)',
		'WARN  | Latte: uses deprecated variable $_l (deprecated in v2.4)',
		'WARN  | Latte: uses deprecated variable $_g (deprecated in v2.4)',
		'ERROR | Latte: uses internal variable $__* (disabled in v2.9)',
		'ERROR | Latte: uses internal variable $ʟ_* (added in v2.8)',
		'WARN  | Latte: uses deprecated tag {includeblock} (deprecated in v2.4)',
		'WARN  | Latte: uses deprecated tag {use} (deprecated in v2.4)',
		'WARN  | Latte: uses deprecated tag {status} (deprecated in v2.4)',
		'ERROR | Latte: uses deprecated tag {!expr}, use filter |noescape instead (deprecated in v2.4)',
		'ERROR | Latte: uses deprecated filter |nl2br (deprecated in v2.4)',
		'ERROR | Latte: uses deprecated filter |safeurl (deprecated in v2.4)',
	], $reporter->getMessages());

	Assert::same(Fixtures::load('Latte/template.expected'), (string) $content);
});
