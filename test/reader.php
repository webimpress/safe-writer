<?php

$file = __DIR__ . '/test.php';

if (file_exists($file)) {
    $data = include $file;
    if (! is_array($data)) {
        exit(1);
    }
}

exit(0);
