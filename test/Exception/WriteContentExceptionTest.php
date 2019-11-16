<?php

declare(strict_types=1);

namespace WebimpressTest\SafeWriter\Exception;

use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\WriteContentException;

use function uniqid;

class WriteContentExceptionTest extends TestCase
{
    public function testException() : void
    {
        $file = uniqid('file_', true);
        $exception = WriteContentException::unableToWriteContent($file);

        self::assertInstanceOf(WriteContentException::class, $exception);
        self::assertStringContainsString($file, $exception->getMessage());
    }
}
