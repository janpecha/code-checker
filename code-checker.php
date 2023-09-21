<?php

return function (JP\CodeChecker\CheckerConfig $config) {
	$config->addPath('./src');
	JP\CodeChecker\AutoConfig::configure($config);
};
