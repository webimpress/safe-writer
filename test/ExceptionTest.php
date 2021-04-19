<?php

declare(strict_types=1);

namespace WebimpressTest\SafeWriter;

use Error;
use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\ExceptionInterface;

use function assert;
use function basename;
use function class_exists;
use function glob;
use function interface_exists;
use function is_a;
use function substr;

class ExceptionTest extends TestCase
{
    /**
     * @psalm-return iterable<string, class-string[]>
     */
    public function exception() : iterable
    {
        $namespace = 'Webimpress\\SafeWriter\\Exception\\';

        $exceptions = glob(__DIR__ . '/../src/Exception/*.php');
        foreach ($exceptions as $exception) {
            $class = substr(basename($exception), 0, -4);

            $fqcn = $namespace . $class;
            assert(class_exists($fqcn) || interface_exists($fqcn));

            yield $class => [$fqcn];
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
