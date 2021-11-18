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
		'tests/code-checker.php',
		'.data/code-checker.php',
	];

	$currentDirectory = getcwd();

	foreach ($configFiles as $configFile) {
		if (is_file($currentDirectory . '/' . $configFile)) {
			$options['-c'] = $currentDirectory . '/' . $configFile;
		}
	}
}

if (!$options['-c']) {
	throw new \RuntimeException('Missing config file, use -c parameter.');
}

if (!is_file($options['-c'])) {
	throw new \RuntimeException('Config file ' . $options['-c'] . ' not found.');
}

$checkerRunner = CheckerFactory::create($options['-c']);
$ok = $checkerRunner->run(
	!isset($options['--fix']),
	!isset($options['--no-progress'])
);

exit($ok ? 0 : 1);
