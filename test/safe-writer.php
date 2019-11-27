<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

$data = array_fill(0, random_int(10, 100), $_SERVER);

// sleep for something between 0.5-2s
usleep(random_int(500000, 2000000));

Webimpress\SafeWriter\FileWriter::writeFile(
    __DIR__ . '/test.php',
    "<?php\nreturn " . var_export($data, true) . ';'
);
