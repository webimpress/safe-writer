<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

/** @var string[] $argv */
[, $file, $stopFile, $maxDuration, $id] = $argv;

$data = array_fill(0, 1000, str_repeat('x', 100));

$end = microtime(true) + (float) $maxDuration;

$writes = 0;
while (microtime(true) < $end) {
    clearstatcache();
    if (file_exists($stopFile)) {
        break;
    }

    ++$writes;

    Webimpress\SafeWriter\FileWriter::writeFile(
        $file,
        "<?php\nreturn " . var_export(['version' => $id . '-' . $writes, 'data' => $data], true) . ';'
    );
}

exit(0);
