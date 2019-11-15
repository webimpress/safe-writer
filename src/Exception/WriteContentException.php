<?php

namespace Webimpress\SafeWriter\Exception;

use function sprintf;

final class WriteContentException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @param string $file
     * @return self
     */
    public static function unableToWriteContent($file)
    {
        return new self(sprintf('Could not write content to the file "%s"', $file));
    }
}
