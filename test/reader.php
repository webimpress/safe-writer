<?php

declare(strict_types=1);

/** @var string[] $argv */
[, $file, $duration] = $argv;

// wait until all processes are started
echo 'ready', PHP_EOL;
fgets(STDIN);

$end = microtime(true) + (float) $duration;

$versions = [];
while (microtime(true) < $end) {
    /** @psalm-suppress UnresolvableInclude */
    $data = include $file;
    if (! is_array($data) || ! isset($data['version'])) {
        fwrite(STDERR, 'Invalid content read from ' . $file);
        exit(1);
    }

    $versions[$data['version']] = true;
}

echo count($versions);
exit(0);
