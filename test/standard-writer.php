<?php

$data = array_fill(0, mt_rand(10, 100), $_SERVER);

// sleep for something between 0.5-2s
usleep(mt_rand(500000, 2000000));

file_put_contents(
    __DIR__ . '/test.php',
    "<?php\nreturn " . var_export($data, true) . ';',
    LOCK_EX
);
