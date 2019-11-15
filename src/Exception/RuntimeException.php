<?php

namespace Webimpress\SafeWriter\Exception;

use function sprintf;

final class RuntimeException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @param string $file
     * @return self
     */
    public static function unableToCreateTemporaryFile($file)
    {
        return new self(sprintf('Could not create temporary file "%s"', $file));
    }
}
