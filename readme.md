# JP\Code Checker

[![Build Status](https://github.com/janpecha/code-checker/workflows/Build/badge.svg)](https://github.com/janpecha/code-checker/actions)
[![Downloads this Month](https://img.shields.io/packagist/dm/janpecha/code-checker.svg)](https://packagist.org/packages/janpecha/code-checker)
[![Latest Stable Version](https://poser.pugx.org/janpecha/code-checker/v/stable)](https://github.com/janpecha/code-checker/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/janpecha/code-checker/blob/master/license.md)

<a href="https://www.janpecha.cz/donate/"><img src="https://buymecoffee.intm.org/img/donate-banner.v1.svg" alt="Donate" height="100"></a>


## Installation

[Download a latest package](https://github.com/janpecha/code-checker/releases) or use [Composer](http://getcomposer.org/):

```
composer require janpecha/code-checker
```

CodeChecker requires PHP 8.2 or later.


## Usage

```
Usage:
    php code-checker [options]

Options:
    -c <path>             Config file
    -f | --fix            Fixes files
    --no-progress         Do not show progress dots
    --step-by-step        Stops on change or report
    --git                 Enables GIT support (auto commit of changes)
```

Config file `code-checker.php`:

```php
<?php

return function (JP\CodeChecker\CheckerConfig $config) {
	$config->addPath(__DIR__ . '/app');
	JP\CodeChecker\AutoConfig::configure($config);
};
```

Based on [Nette\CodeChecker](https://github.com/nette/code-checker).

------------------------------

License: [New BSD License](license.md)
<br>Author: Jan Pecha, https://www.janpecha.cz/
