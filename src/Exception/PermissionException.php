<?php

namespace Webimpress\SafeWriter\Exception;

use RuntimeException;

use function sprintf;

final class PermissionException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param string $file
     * @return self
     */
    public static function unableToCreateTemporaryFile($file)
    {
        return new self(sprintf(
            'Could not create temporary file "%s": '
            . 'the destination is not writable',
            $file,
        ));
    }
}
