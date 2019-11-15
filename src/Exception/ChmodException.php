<?php

namespace Webimpress\SafeWriter\Exception;

use function sprintf;

final class ChmodException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @param string $file
     * @return self
     */
    public static function unableToChangeChmod($file)
    {
        return new self(sprintf('Could not change chmod of the file "%s"', $file));
    }
}
