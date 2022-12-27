<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

$data = array_fill(0, 100, $_SERVER);

// sleep for something between 0.1-2s
usleep(random_int(100000, 2000000));

Webimpress\SafeWriter\FileWriter::writeFile(
    __DIR__ . '/test.php',
    "<?php\nreturn " . var_export($data, true) . ';'
);
