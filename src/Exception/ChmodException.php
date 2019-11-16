<?php

declare(strict_types=1);

namespace Webimpress\SafeWriter\Exception;

use RuntimeException as PhpRuntimeException;

use function sprintf;

final class ChmodException extends PhpRuntimeException implements ExceptionInterface
{
    public static function unableToChangeChmod(string $file) : self
    {
        return new self(sprintf('Could not change chmod of the file "%s"', $file));
    }
}
