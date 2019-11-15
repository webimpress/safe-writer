<?php

namespace WebimpressTest\SafeWriter\Exception;

use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\RuntimeException;

use function uniqid;

class RuntimeExceptionTest extends TestCase
{
    public function testException()
    {
        $file = uniqid('file_', true);
        $exception = RuntimeException::unableToCreateTemporaryFile($file);

        self::assertInstanceOf(RuntimeException::class, $exception);
        self::assertContains($file, $exception->getMessage());
    }
}
