<?php

declare(strict_types=1);

namespace WebimpressTest\SafeWriter;

use Error;
use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\ExceptionInterface;

use function basename;
use function glob;
use function interface_exists;
use function is_a;
use function strrpos;
use function substr;

class ExceptionTest extends TestCase
{
    /**
     * @psalm-return iterable<string, class-string[]>
     * @psalm-suppress MoreSpecificReturnType
     */
    public function exception() : iterable
    {
        $namespace = substr(ExceptionInterface::class, 0, (int) strrpos(ExceptionInterface::class, '\\') + 1);

        $exceptions = glob(__DIR__ . '/../src/Exception/*.php');
        foreach ($exceptions as $exception) {
            $class = substr(basename($exception), 0, -4);

            yield $class => [$namespace . $class];
        }
    }

    /**
     * @dataProvider exception
     * @psalm-param class-string $exception
     */
    public function testExceptionIsInstanceOfExceptionInterface(string $exception) : void
    {
        self::assertStringContainsString('Exception', $exception);
        self::assertTrue(is_a($exception, ExceptionInterface::class, true));
    }

    /**
     * @dataProvider exception
     * @psalm-param class-string $exception
     */
    public function testExceptionIsNotInstantiable(string $exception) : void
    {
        if (interface_exists($exception)) {
            $this->markTestSkipped('Test does not apply to interface ' . $exception);
        }

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Call to private');

        /** @psalm-suppress MixedMethodCall */
        new $exception();
    }
}
