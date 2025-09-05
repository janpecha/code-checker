<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Rules;
use JP\CodeChecker\Version;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


test('Basic', function () {
	$file = Fixtures::path('Latte/template.source');
	$latteExtension = new Rules\Latte\LatteDeprecatedRule(new Version('2.4.0'), ['*.latte']);
	$file = File::fromFile($file);

	$latteExtension->fixDeprecated($file);
	$latteExtension->checkDeprecated($file);

	Assert::equal([
		new ResultMessage(ResultType::Fix, 'Latte: filter |nl2br replaced by filter |breaklines (deprecated in v2.4)'),
		new ResultMessage(ResultType::Fix, 'Latte: tag {? expr} replaced by tag {php} (deprecated in v2.4)'),
		new ResultMessage(ResultType::Fix, 'Latte: keyword \'break\' is not supported in {php} (removed in v3.0)'),
		new ResultMessage(ResultType::Fix, 'Latte: operation += is not supported in {var} (removed in v3.0)'),
		new ResultMessage(ResultType::Error, 'Latte: template contains <?php open tag (deprecated in v2.4)'),
		new ResultMessage(ResultType::Warning, 'Latte: uses deprecated variable $template (deprecated in v2.4)'),
		new ResultMessage(ResultType::Warning, 'Latte: uses deprecated variable $_l (deprecated in v2.4)'),
		new ResultMessage(ResultType::Warning, 'Latte: uses deprecated variable $_g (deprecated in v2.4)'),
		new ResultMessage(ResultType::Error, 'Latte: uses internal variable $__* (disabled in v2.9)'),
		new ResultMessage(ResultType::Error, 'Latte: uses internal variable $ʟ_* (added in v2.8)'),
		new ResultMessage(ResultType::Warning, 'Latte: uses deprecated tag {includeblock} (deprecated in v2.4)'),
		new ResultMessage(ResultType::Warning, 'Latte: uses deprecated tag {use} (deprecated in v2.4)'),
		new ResultMessage(ResultType::Warning, 'Latte: uses deprecated tag {status} (deprecated in v2.4)'),
		new ResultMessage(ResultType::Error, 'Latte: uses deprecated tag {!expr}, use filter |noescape instead (deprecated in v2.4)'),
		new ResultMessage(ResultType::Error, 'Latte: uses deprecated filter |nl2br (deprecated in v2.4)'),
		new ResultMessage(ResultType::Error, 'Latte: uses deprecated filter |safeurl (deprecated in v2.4)'),
	], $file->getResult());

	Assert::same(Fixtures::load('Latte/template.expected'), (string) $file);
});
