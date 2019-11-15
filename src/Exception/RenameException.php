<?php

namespace Webimpress\SafeWriter\Exception;

use function sprintf;

final class RenameException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @param string $source
     * @param string $target
     * @return self
     */
    public static function unableToMoveFile($source, $target)
    {
        return new self(sprintf(
            'Could not move file "%s" to location "%s": '
            . 'either the source file is not readable, or the destination is not writable',
            $source,
            $target
        ));
    }
}
