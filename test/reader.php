<?php

declare(strict_types=1);

/** @var string[] $argv */
[, $file, $minDuration, $maxDuration] = $argv;

$start = microtime(true);

$versions = [];
while (true) {
    $elapsed = microtime(true) - $start;
    if ($elapsed >= (float) $maxDuration
        || ($elapsed >= (float) $minDuration && count($versions) > 1)
    ) {
        break;
    }

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
