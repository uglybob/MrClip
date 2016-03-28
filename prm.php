<?php

namespace Uglybob\MrClip;

require_once('vendor/autoload.php');

$command = isset($argv[1]) ? $argv[1] : null;
$options = array_slice($argv, 2, count($argv));

new Lib\MrClip($command, $options);
