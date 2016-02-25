<?php

namespace Uglybob\MrClip\Lib;

class MrClip
{
    public function __construct($command, $options)
    {
        if ($command == 'comp') {
            error_log($command . ':' . $options . "\n", 3, 'debug.log');
        } elseif ($command == 'record') {
            $times = '(?:\d{1,2}:\d{2}\s+){0,2}';
            $string = '[a-zA-Z0-9 ]+';
            $tags = "(?:\s+\+$string)*";
            $text = "\s+:($string)";
            $format = "($times)($string)@($string)($tags)(?:$text)?";
            $regex = "/^\s*$format\s*$/";

            preg_match($regex, $options, $matches);

            $times = isset($matches[1]) ? $matches[1] : null;
            $activity = $matches[2];
            $category = $matches[3];
            $tags = isset($matches[4]) ? $matches[4] : null;
            $text = isset($matches[5]) ? $matches[5] : null;

            preg_match_all('/\d{1,2}:\d{2}/', $times, $matches);
            $times = $matches[0];
            $times = array_map(
                function($value)
                {
                    $split = explode(':', $value);
                    $dt = new \DateTime();
                    $dt->setTime($split[0], $split[1]);

                    return $dt;
                },
                $times
            );

            preg_match_all("/\+($string)/", $tags, $matches);
            $tags = $matches[1];
            $tags = array_map(
                function($value)
                {
                    return trim($value);
                },
                $tags
            );

            $soapOptions = [
                'location' => Setup::get('url') . '/soap.php',
                'uri' => 'http://localhost/',
            ];

            $prm = new \SoapClient(null, $soapOptions);

            $prm->login('sebastian', 'test');
            $prm->editRecord(null, $times[0]->getTimestamp(), $times[1]->getTimestamp(), $activity, $category, $tags, $text);
        } else {
            $soapOptions = [
                'location' => Setup::get('url') . '/soap.php',
                'uri' => 'http://localhost/',
            ];

            $prm = new \SoapClient(null, $soapOptions);
            var_dump($prm->login('sebastian', 'test'));

        }
    }
}

require_once('vendor/autoload.php');

$command = isset($argv[1]) ? $argv[1] : null;
$options = array_slice($argv, 2, count($argv));
new MrClip($command, implode(' ', $options));
