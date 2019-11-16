<?php

declare(strict_types=1);

namespace Webimpress\SafeWriter\Exception;

use RuntimeException as PhpRuntimeException;

use function sprintf;

final class WriteContentException extends PhpRuntimeException implements ExceptionInterface
{
    public static function unableToWriteContent(string $file) : self
    {
        return new self(sprintf('Could not write content to the file "%s"', $file));
    }
}
