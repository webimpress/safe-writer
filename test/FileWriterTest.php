<?php

declare(strict_types=1);

namespace WebimpressTest\SafeWriter;

use ErrorException;
use Generator;
use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\RuntimeException;
use Webimpress\SafeWriter\FileWriter;

use function basename;
use function dirname;
use function file_exists;
use function file_get_contents;
use function fileperms;
use function is_numeric;
use function is_resource;
use function json_encode;
use function octdec;
use function proc_close;
use function proc_open;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function stream_get_contents;
use function substr;
use function sys_get_temp_dir;
use function touch;
use function umask;
use function uniqid;
use function unlink;

use const PHP_EOL;

class FileWriterTest extends TestCase
{
    public function testContentIsSavedToTheFile() : void
    {
        $targetFile = $this->getTargetFile();

        FileWriter::writeFile($targetFile, 'some data');

        self::assertSame('some data', file_get_contents($targetFile));
    }

    public function permission() : Generator
    {
        //    chmod  umask expected
        yield [0666, 022, 0644];
        yield [0644, 0, 0644];
        yield [0600, 022, 0600];
    }

    /**
     * @requires OS Darwin|Linux
     *
     * @dataProvider permission
     */
    public function testCorrectChmodIsSet(int $chmod, int $umask, int $expectedChmod) : void
    {
        $targetFile = $this->getTargetFile();

        $currentUmask = umask($umask);

        FileWriter::writeFile($targetFile, 'content', $chmod);
        umask($currentUmask);

        self::assertSame($expectedChmod, $this->getFilePermission($targetFile));
    }

    private function getFilePermission(string $file) : int
    {
        return (int) octdec(substr(sprintf('%o', fileperms($file)), -4));
    }

    private function getTargetFile() : string
    {
        return sys_get_temp_dir() . '/' . uniqid('test_', true) . '.php';
    }

    public function writer() : Generator
    {
        //                     writer script                   expected result
        yield 'safe-writer' => [__DIR__ . '/safe-writer.php', true];
        yield 'standard-writer' => [__DIR__ . '/standard-writer.php', false];
    }

    /**
     * @dataProvider writer
     */
    public function testMultipleWriters(string $writer, bool $expectedResult) : void
    {
        $processes = [];
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $processes[0]['pipes'] = [];
        $processes[0]['process'] = proc_open(
            'php ' . __DIR__ . '/reader.php',
            $descriptorSpec,
            $processes[0]['pipes']
        );

        for ($i = 1; $i <= 20; ++$i) {
            $processes[$i]['pipes'] = [];
            $processes[$i]['process'] = proc_open(
                'php ' . $writer,
                $descriptorSpec,
                $processes[$i]['pipes']
            );
        }

        $readerResult = null;
        $readerErrors = null;
        $results = [];
        foreach ($processes as $i => $process) {
            if (is_resource($process['process'])) {
                if ($i === 0) {
                    $readerResult = isset($process['pipes'][1]) && is_resource($process['pipes'][1])
                        ? stream_get_contents($process['pipes'][1])
                        : null;
                    $readerErrors = isset($process['pipes'][2]) && is_resource($process['pipes'][2])
                        ? stream_get_contents($process['pipes'][2])
                        : null;
                }

                $code = proc_close($process['process']);
                if ($code !== 0) {
                    $results[$i] = $code;
                }
            } else {
                self::fail(sprintf('Process %d is not a resource', $i));
            }
        }

        self::assertSame(
            $expectedResult,
            empty($results),
            'Response codes: ' . json_encode($results) . PHP_EOL
            . 'Reader errors: ' . (string) $readerErrors
        );
        if ($expectedResult) {
            self::assertNotEmpty($readerResult);
            self::assertTrue(is_numeric($readerResult));
            self::assertGreaterThan(0, $readerResult);
        }
    }

    public function testUnwritableDirThrowsException() : void
    {
        $dir = sys_get_temp_dir() . '/unwritable';
        touch($dir);

        $this->expectException(RuntimeException::class);
        FileWriter::writeFile($dir . '/test', 'foo');
    }

    public function testUnwritableDirThrowsExceptionWhenUsingCustomErrorHandler() : void
    {
        $errorHandler = static function () : bool {
            throw new ErrorException();
        };
        set_error_handler($errorHandler);

        $dir = sys_get_temp_dir() . '/unwritable';
        touch($dir);

        $this->expectException(RuntimeException::class);
        try {
            FileWriter::writeFile($dir . '/test', 'foo');
        } finally {
            self::assertSame($errorHandler, set_error_handler(static function () : bool {
                return true;
            }));
            restore_error_handler();
        }
    }

    public function testRelativeDirectorySaves() : void
    {
        $targetFile = $this->getTargetFile();
        $targetFile = dirname($targetFile) . '/../' . basename(dirname($targetFile)) . '/' . basename($targetFile);

        FileWriter::writeFile($targetFile, 'some data');

        self::assertSame('some data', file_get_contents($targetFile));
    }

    protected function tearDown() : void
    {
        if (file_exists(__DIR__ . '/test.php')) {
            unlink(__DIR__ . '/test.php');
        }
        if (file_exists(sys_get_temp_dir() . '/unwritable')) {
            unlink(sys_get_temp_dir() . '/unwritable');
        }

        parent::tearDown();
    }
}
