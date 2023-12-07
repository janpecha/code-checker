<?php

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();


function test(string $description, callable $cb): void
{
	$cb();
}


class Fixtures
{
	public static function path(string $path): string
	{
		return __DIR__ . '/CodeChecker/fixtures/' . $path;
	}


	public static function load(string $path): string
	{
		return (string) file_get_contents(self::path($path));
	}
}
