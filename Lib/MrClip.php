<?php

namespace Uglybob\MrClip\Lib;

class MrClip
{
    public function __construct($command, $options)
    {
        if ($command == 'comp') {
            error_log($command . ":" . $options . "\n", 3, 'debug.log');
        } else {
            $soapOptions = [
                'location' => Setup::get('url') . '/soap.php',
                'uri' => 'http://localhost/',
            ];

            $api = new \SoapClient(null, $soapOptions);
            var_dump($api->login('sebastian', 'test'));
        }
    }
}

require_once('vendor/autoload.php');

$command = $argv[1];
$options = array_slice(implode(' ', $argv), 3, count($argv));
new MrClip($command, $options);

