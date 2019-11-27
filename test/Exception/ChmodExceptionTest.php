<?php

declare(strict_types=1);

namespace WebimpressTest\SafeWriter\Exception;

use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\ChmodException;

use function uniqid;

class ChmodExceptionTest extends TestCase
{
    public function testException() : void
    {
        $file = uniqid('file_', true);
        $exception = ChmodException::unableToChangeChmod($file);

        self::assertInstanceOf(ChmodException::class, $exception);
        self::assertStringContainsString($file, $exception->getMessage());
    }
}
