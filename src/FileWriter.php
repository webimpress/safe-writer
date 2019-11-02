<?php

namespace Webimpress\SafeWriter;

use function chmod;
use function is_writable;
use function md5;
use function sys_get_temp_dir;
use function umask;
use function tempnam;
use function file_put_contents;
use function rename;

final class FileWriter
{
    /**
     * @param string $file
     * @param string $content
     * @param int $chmod
     * @return void
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
            if (is_writable($file)) {
                continue;
            }

            throw Exception\RenameException::unableToMoveFile($tmp, $file);
        }
    }
}
