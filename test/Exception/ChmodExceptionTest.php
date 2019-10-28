<?php

namespace WebimpressTest\SafeWriter\Exception;

use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\ChmodException;

use function uniqid;

class ChmodExceptionTest extends TestCase
{
    public function testException()
    {
        $file = uniqid('file_', true);
        $exception = ChmodException::unableToChangeChmod($file);

        self::assertInstanceOf(ChmodException::class, $exception);
        self::assertContains($file, $exception->getMessage());
    }
}
