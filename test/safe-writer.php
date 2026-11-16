<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

/** @var string[] $argv */
[, $file, $duration, $id] = $argv;

$data = array_fill(0, 1000, str_repeat('x', 100));

// wait until all processes are started
echo 'ready', PHP_EOL;
fgets(STDIN);

$end = microtime(true) + (float) $duration;

$writes = 0;
while (microtime(true) < $end) {
    ++$writes;

    Webimpress\SafeWriter\FileWriter::writeFile(
        $file,
        "<?php\nreturn " . var_export(['version' => $id . '-' . $writes, 'data' => $data], true) . ';'
    );
}

echo $writes;
exit(0);
