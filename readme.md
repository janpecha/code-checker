# JP\Code Checker

[![Tests Status](https://github.com/janpecha/code-checker/workflows/Tests/badge.svg)](https://github.com/janpecha/code-checker/actions)

<a href="https://www.paypal.me/janpecha/5eur"><img src="https://buymecoffee.intm.org/img/button-paypal-white.png" alt="Buy me a coffee" height="35"></a>


## Installation

[Download a latest package](https://github.com/janpecha/code-checker/releases) or use [Composer](http://getcomposer.org/):

```
composer require janpecha/code-checker
```

CodeChecker requires PHP 7.2 or later.


## Usage

```
Usage:
    php code-checker [options]

Options:
    -c <path>             Config file
    -f | --fix            Fixes files
    --no-progress         Do not show progress dots
```

Config file `code-checker.php`:

```php
<?php

return function (JP\CodeChecker\CheckerConfig $config) {
	$config->addPath(__DIR__ . '/app');
	JP\CodeChecker\Tasks\AutoConfig::configure($config);
};
```

Based on [Nette\CodeChecker](https://github.com/nette/code-checker).

------------------------------

License: [New BSD License](license.md)
<br>Author: Jan Pecha, https://www.janpecha.cz/
