<?php

declare(strict_types=1);

$file = __DIR__ . '/test.php';

$start = microtime(true);

$numberOfReads = 0;
while (microtime(true) - $start < 3) {
    if (file_exists($file)) {
        $data = include $file;
        if (! is_array($data)) {
            exit(1);
        }

        ++$numberOfReads;
    }
}

echo $numberOfReads;
exit(0);
