<?php

declare(strict_types=1);

namespace Webimpress\SafeWriter\Exception;

use RuntimeException as PhpRuntimeException;

use function sprintf;

final class RuntimeException extends PhpRuntimeException implements ExceptionInterface
{
    public static function unableToCreateTemporaryFile(string $dir) : self
    {
        return new self(sprintf('Could not create temporary file in directory "%s"', $dir));
    }
}
