<?php

use ArangoDb\ClientOptions;


$options = new ClientOptions([
    ClientOptions::OPTION_ENDPOINT => 'tcp://arangodb:8529',
    ClientOptions::OPTION_DATABASE => 'testing',
]);

var_dump($options->toArray());