<?php

use cPad\Command\ConvertCommand;
use Symfony\Component\Console\Application;

require_once 'vendor/autoload.php';
require_once 'lib/parser.php';
require_once 'lib/ds.php';
require_once 'lib/writer.php';
require_once 'lib/spec.php';
require_once 'lib/transform.php';

$cpad = new Application();
$cpad->addCommands([
    new ConvertCommand()
]);
$cpad->run();