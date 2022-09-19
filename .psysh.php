<?php

use Symfony\Component\Dotenv\Dotenv;

set_time_limit(0);
ini_set('memory_limit','-1');
ini_set('max_execution_time', 0);

require_once 'app/autoload.php';


$environment = new Dotenv();
$environment->overload(__DIR__.'/.env');


return [
 'startupMessage' => 'Welcome to a bootstrapped Gigya helper. To generate a report try: (new \GigyaHelper\Report\Unregistered())->run().',
];