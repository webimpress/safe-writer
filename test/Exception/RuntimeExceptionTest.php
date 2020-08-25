<?php

declare(strict_types=1);

namespace WebimpressTest\SafeWriter\Exception;

use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\RuntimeException;

use function uniqid;

class RuntimeExceptionTest extends TestCase
{
    public function testException() : void
    {
        $dir = uniqid('dir_', true);
        $exception = RuntimeException::unableToCreateTemporaryFile($dir);

        self::assertStringContainsString($dir, $exception->getMessage());
    }
}
