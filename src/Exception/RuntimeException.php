<?php

namespace Webimpress\SafeWriter\Exception;

use function sprintf;

final class RuntimeException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @param string $dir
     * @return self
     */
    public static function unableToCreateTemporaryFile($dir)
    {
        return new self(sprintf('Could not create temporary file in directory "%s"', $dir));
    }
}
