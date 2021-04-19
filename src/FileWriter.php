<?php

declare(strict_types=1);

namespace Webimpress\SafeWriter;

use function assert;
use function chmod;
use function dirname;
use function file_put_contents;
use function is_string;
use function is_writable;
use function realpath;
use function rename;
use function restore_error_handler;
use function set_error_handler;
use function stripos;
use function tempnam;
use function umask;
use function unlink;

use const PHP_OS;

final class FileWriter
{
    /**
     * @throws Exception\ExceptionInterface
     */
    public static function writeFile(string $file, string $content, int $chmod = 0666) : void
    {
        $dir = dirname($file);

        // suppress notice thrown when falling back to system temp dir
        $tmp = self::suppress(static function () use ($dir) {
            return tempnam($dir, 'wsw');
        });

        if ($tmp === false) {
            throw Exception\RuntimeException::unableToCreateTemporaryFile($dir);
        }

        assert(is_string($tmp));

        if (dirname($tmp) !== realpath($dir)) {
            unlink($tmp);
            throw Exception\RuntimeException::unableToCreateTemporaryFile($dir);
        }

        if (file_put_contents($tmp, $content) === false) {
            unlink($tmp);
            throw Exception\WriteContentException::unableToWriteContent($tmp);
        }

        if (chmod($tmp, $chmod & ~umask()) === false) {
            unlink($tmp);
            throw Exception\ChmodException::unableToChangeChmod($tmp);
        }

        // On windows try again if rename was not successful but target file is writable.
        while (self::suppress(static function () use ($tmp, $file) {
            return rename($tmp, $file);
        }) === false) {
            if (is_writable($file) && stripos(PHP_OS, 'WIN') === 0) {
                continue;
            }

            unlink($tmp);
            throw Exception\RenameException::unableToMoveFile($tmp, $file);
        }
    }

    /**
     * @return mixed
     */
    private static function suppress(callable $func)
    {
        /** @psalm-suppress InvalidArgument */
        set_error_handler(static function () : void {
        });

        try {
            return $func();
        } finally {
            restore_error_handler();
        }
    }
}
