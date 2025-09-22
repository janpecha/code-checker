<?php

declare(strict_types=1);

use JP\CodeChecker\File;
use JP\CodeChecker\Processors\PhpProcessor;
use JP\CodeChecker\ResultMessage;
use JP\CodeChecker\ResultType;
use JP\CodeChecker\Rules;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

test('Fix visibility', function () {
	$file = Fixtures::path('Nette/presenter-methods-visibility.source');
	$file = File::fromFile($file);

	$rule = new Rules\Nette\NettePresenterMethodsVisibilityRule(['*']);
	$processor = new PhpProcessor(['*'], [], [$rule]);
	$processor->processFile($file);

	Assert::equal([
		new ResultMessage(ResultType::Fix, 'Nette: fixed visibility of MyApp\\Presenters\\TestPresenter::actionDefault()'),
		new ResultMessage(ResultType::Fix, 'Nette: fixed visibility of MyApp\\Presenters\\TestPresenter::renderDefault()'),
		new ResultMessage(ResultType::Fix, 'Nette: fixed visibility of MyApp\\Presenters\\TestPresenter::handleAdd()'),
		new ResultMessage(ResultType::Fix, 'Nette: fixed visibility of MyApp\\Presenters\\TestPresenter::startup()'),
		new ResultMessage(ResultType::Fix, 'Nette: fixed visibility of MyApp\\Presenters\\TestPresenter::beforeRender()'),
		new ResultMessage(ResultType::Fix, 'Nette: fixed visibility of MyApp\\Presenters\\TestPresenter::afterRender()'),
		new ResultMessage(ResultType::Fix, 'Nette: fixed visibility of MyApp\\Presenters\\TestPresenter::shutdown()'),
		new ResultMessage(ResultType::Fix, 'Nette: fixed visibility of MyApp\\Presenters\\TestPresenter::createComponentFoo()'),
	], $file->getResult());

	Assert::same(Fixtures::load('Nette/presenter-methods-visibility.expected'), (string) $file);
});
