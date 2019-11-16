<?php

namespace WebimpressTest\SafeWriter\Exception;

use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\RuntimeException;

use function uniqid;

class RuntimeExceptionTest extends TestCase
{
    public function testException()
    {
        $dir = uniqid('dir_', true);
        $exception = RuntimeException::unableToCreateTemporaryFile($dir);

        self::assertInstanceOf(RuntimeException::class, $exception);
        self::assertContains($dir, $exception->getMessage());
    }
}
