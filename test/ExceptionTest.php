<?php

declare(strict_types=1);

namespace WebimpressTest\SafeWriter;

use Generator;
use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\ExceptionInterface;

use function basename;
use function glob;
use function is_a;
use function strrpos;
use function substr;

class ExceptionTest extends TestCase
{
    public function exception() : Generator
    {
        $namespace = substr(ExceptionInterface::class, 0, strrpos(ExceptionInterface::class, '\\') + 1);

        $exceptions = glob(__DIR__ . '/../src/Exception/*.php');
        foreach ($exceptions as $exception) {
            $class = substr(basename($exception), 0, -4);

            yield $class => [$namespace . $class];
        }
    }

    /**
     * @dataProvider exception
     */
    public function testExceptionIsInstanceOfExceptionInterface(string $exception) : void
    {
        self::assertStringContainsString('Exception', $exception);
        self::assertTrue(is_a($exception, ExceptionInterface::class, true));
    }
}
