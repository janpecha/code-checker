<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\Processors\PhpProcessor;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Rules;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

test('Fix return types', function () {
	$file = Fixtures::path('Nette/presenter-methods-return-type.source');
	$file = File::fromFile($file);

	$rule = new Rules\Nette\NettePresenterMethodsReturnTypeRule(['*']);
	$processor = new PhpProcessor(['*'], [], [$rule]);
	$processor->processFile($file);

	Assert::equal([
		new ResultMessage(ResultType::Fix, 'Nette: added return type for MyApp\\Presenters\\TestPresenter::actionDefault()'),
		new ResultMessage(ResultType::Fix, 'Nette: added return type for MyApp\\Presenters\\TestPresenter::renderDefault()'),
		new ResultMessage(ResultType::Fix, 'Nette: added return type for MyApp\\Presenters\\TestPresenter::handleAdd()'),
		new ResultMessage(ResultType::Fix, 'Nette: removed return type from MyApp\\Presenters\\TestPresenter::startup()'),
		new ResultMessage(ResultType::Fix, 'Nette: removed return type from MyApp\\Presenters\\TestPresenter::beforeRender()'),
		new ResultMessage(ResultType::Fix, 'Nette: removed return type from MyApp\\Presenters\\TestPresenter::afterRender()'),
		new ResultMessage(ResultType::Fix, 'Nette: removed return type from MyApp\\Presenters\\TestPresenter::shutdown()'),
	], $file->getResult());

	Assert::same(Fixtures::load('Nette/presenter-methods-return-type.expected'), (string) $file);
});
