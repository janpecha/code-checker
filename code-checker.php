<?php

declare(strict_types=1);

return function (JP\CodeChecker\CheckerConfig $config) {
	$config->addIgnore('/jpx');
	JP\CodeChecker\AutoConfig::configure($config);
};
