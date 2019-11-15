<?php

namespace WebimpressTest\SafeWriter\Exception;

use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\PermissionException;

use function uniqid;

class PermissionExceptionTest extends TestCase
{
    public function testException()
    {
        $file = uniqid('file_', true);
        $exception = PermissionException::unableToCreateTemporaryFile($file);

        self::assertInstanceOf(PermissionException::class, $exception);
        self::assertContains($file, $exception->getMessage());
    }
}
