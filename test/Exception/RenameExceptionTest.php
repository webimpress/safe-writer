<?php

declare(strict_types=1);

namespace WebimpressTest\SafeWriter\Exception;

use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\RenameException;

use function uniqid;

class RenameExceptionTest extends TestCase
{
    public function testException() : void
    {
        $file = uniqid('file_', true);
        $target = __DIR__ . '/' . uniqid('file_', true);
        $exception = RenameException::unableToMoveFile($file, $target);

        self::assertStringContainsString($file, $exception->getMessage());
        self::assertStringContainsString($target, $exception->getMessage());
    }
}
