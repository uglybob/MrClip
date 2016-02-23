<?php

$commands = ['record', 'todo', 'list'];

if (isset($argv[1]) && $argv[1] == 'comp') {
    $options = $argv;
    array_shift($options);
    array_shift($options);
    array_shift($options);

    echo  implode('-', $options);
} else {
    $options = [
        'location' => 'http://prm.dev/soap.php',
        'uri' => 'http://localhost/',
    ];

    $api = new SoapClient(NULL, $options);

    //var_dump($api->login('sebastian', 'test'));
}
