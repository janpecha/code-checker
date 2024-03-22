<?php

declare(strict_types=1);

namespace JP\CodeChecker;

use Nette\CommandLine\Parser;

$autoload = is_file(__DIR__ . '/../vendor/autoload.php')
	? __DIR__ . '/../vendor/autoload.php'
	: __DIR__ . '/../../../autoload.php';

if (@!include $autoload) {
	echo 'Install packages using `composer update`';
	exit(1);
}

error_reporting(E_ALL & ~E_USER_DEPRECATED & ~E_DEPRECATED);

set_exception_handler(function (\Throwable $e) {
	echo "Error: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n";
	die(2);
});

set_error_handler(function (int $severity, string $message, string $file, int $line) {
	if (($severity & error_reporting()) === $severity) {
		throw new \ErrorException($message, 0, $severity, $file, $line);
	}
	return false;
});

if (function_exists('pcntl_signal')) {
	pcntl_signal(SIGINT, function (): void {
		pcntl_signal(SIGINT, SIG_DFL);
		throw new \Exception('Terminated');
	});

} elseif (function_exists('sapi_windows_set_ctrl_handler')) {
	sapi_windows_set_ctrl_handler(function () {
		throw new \Exception('Terminated');
	});
}

set_time_limit(0);


echo '
JP\\CodeChecker
---------------
';

$cmd = new Parser(<<<'XX'
Usage:
    php code-checker [options]

Options:
    -c <path>             Config file
    -f | --fix            Fixes files
    --no-progress         Do not show progress dots
    --step-by-step        Stops on change or report
    --git                 Enables GIT support (auto commit of changes)


XX
, [
	'-c' => [Parser::REALPATH => TRUE],
]);

$options = $cmd->parse();
if ($cmd->isEmpty()) {
	$cmd->help();
}


if (!$options['-c']) {
	$configFiles = [
		'code-checker.php',
		'code-checker.neon',

		'tests/code-checker.php',
		'tests/code-checker.neon',

		'.data/code-checker.php',
		'.data/code-checker.neon',
	];

	$currentDirectory = getcwd();

	foreach ($configFiles as $configFile) {
		if (is_file($currentDirectory . '/' . $configFile)) {
			$options['-c'] = $currentDirectory . '/' . $configFile;
		}
	}
}

$cwd = getcwd();
$checkerRunner = CheckerFactory::create(
	isset($options['-c']) ? $options['-c'] : NULL,
	is_string($cwd) ? $cwd : NULL
);
$ok = $checkerRunner->run(
	!isset($options['--fix']),
	isset($options['--step-by-step']),
	!isset($options['--no-progress']),
	isset($options['--git'])
);

exit($ok ? 0 : 1);
