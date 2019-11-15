<?php

namespace Webimpress\SafeWriter;

use function chmod;
use function file_put_contents;
use function is_writable;
use function md5;
use function rename;
use function stripos;
use function sys_get_temp_dir;
use function tempnam;
use function umask;

use const PHP_OS;

final class FileWriter
{
    /**
     * @param string $file
     * @param string $content
     * @param int $chmod
     * @return void
     * @throws Exception\ExceptionInterface
     */
    public static function writeFile($file, $content, $chmod = 0666)
    {
        $tmp = tempnam(sys_get_temp_dir(), md5($file));

        if (file_put_contents($tmp, $content) === false) {
            throw Exception\WriteContentException::unableToWriteContent($tmp);
        }

        if (chmod($tmp, $chmod & ~umask()) === false) {
            throw Exception\ChmodException::unableToChangeChmod($tmp);
        }

        while (@rename($tmp, $file) === false) {
            if (is_writable($file) && stripos(PHP_OS, 'WIN') === 0) {
                continue;
            }

            throw Exception\RenameException::unableToMoveFile($tmp, $file);
        }
    }
}
