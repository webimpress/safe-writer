<?php

namespace WebimpressTest\SafeWriter\Exception;

use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\RenameException;

use function uniqid;

class RenameExceptionTest extends TestCase
{
    public function testException()
    {
        $file = uniqid('file_', true);
        $target = __DIR__ . '/' . uniqid('file_', true);
        $exception = RenameException::unableToMoveFile($file, $target);

        self::assertInstanceOf(RenameException::class, $exception);
        self::assertContains($file, $exception->getMessage());
        self::assertContains($target, $exception->getMessage());
    }
}
