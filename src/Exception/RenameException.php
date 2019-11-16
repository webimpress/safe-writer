<?php

declare(strict_types=1);

namespace Webimpress\SafeWriter\Exception;

use RuntimeException as PhpRuntimeException;

use function sprintf;

final class RenameException extends PhpRuntimeException implements ExceptionInterface
{
    public static function unableToMoveFile(string $source, string $target) : self
    {
        return new self(sprintf(
            'Could not move file "%s" to location "%s": '
            . 'either the source file is not readable, or the destination is not writable',
            $source,
            $target
        ));
    }
}
