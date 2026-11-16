<?php

declare(strict_types=1);

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

    file_put_contents(
        $file,
        "<?php\nreturn " . var_export(['version' => $id . '-' . $writes, 'data' => $data], true) . ';',
        LOCK_EX
    );
}

exit(0);
