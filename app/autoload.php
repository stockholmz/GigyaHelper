<?php

require_once __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($class) {
	$path = str_replace('\\', '/', $class);
	$path = str_replace('GigyaHelper/', '/', $path);

	$filePath = __DIR__ .  $path . '.php';
	if (file_exists($filePath)) {
		require $filePath;
	} else {
		throw new Exception('Could not load class ' . $class . ' Tried path ' . $path . ' Path does not exist: ' . $filePath);
	}
});
