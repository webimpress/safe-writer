<?php

namespace WebimpressTest\SafeWriter\Exception;

use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\RenameException;
use Webimpress\SafeWriter\Exception\WriteContentException;

use function uniqid;

class WriteContentExceptionTest extends TestCase
{
    public function testException()
    {
        $file = uniqid('file_', true);
        $target = __DIR__ . '/' . uniqid('file_', true);
        $exception = WriteContentException::unableToWriteContent($file);

        self::assertInstanceOf(WriteContentException::class, $exception);
        self::assertContains($file, $exception->getMessage());
    }
}
