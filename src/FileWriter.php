<?php

namespace Webimpress\SafeWriter;

use function chmod;
use function file_put_contents;
use function md5;
use function rename;
use function sys_get_temp_dir;
use function tempnam;
use function umask;

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

        if (rename($tmp, $file) === false) {
            throw Exception\RenameException::unableToMoveFile($tmp, $file);
        }
    }
}
