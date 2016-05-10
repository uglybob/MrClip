<?php

namespace Uglybob\MrClip;

require_once('vendor/autoload.php');

array_shift($argv);
$options = Lib\Cli::cleancolons($argv);

new Lib\MrClip($options);
