<?php

namespace Uglybob\MrClip;

require_once('vendor/autoload.php');

array_shift($argv);
new Lib\MrClip($argv);
