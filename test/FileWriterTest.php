<?php

declare(strict_types=1);

namespace WebimpressTest\SafeWriter;

use ErrorException;
use Generator;
use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\Exception\RuntimeException;
use Webimpress\SafeWriter\FileWriter;

use function basename;
use function clearstatcache;
use function dirname;
use function fclose;
use function file_get_contents;
use function file_put_contents;
use function fileinode;
use function fileperms;
use function fopen;
use function glob;
use function microtime;
use function mkdir;
use function octdec;
use function proc_close;
use function proc_get_status;
use function proc_open;
use function proc_terminate;
use function restore_error_handler;
use function rmdir;
use function set_error_handler;
use function sprintf;
use function stream_get_contents;
use function substr;
use function sys_get_temp_dir;
use function touch;
use function umask;
use function uniqid;
use function unlink;
use function usleep;

use const PHP_BINARY;

class FileWriterTest extends TestCase
{
    private string $dir;

    protected function setUp() : void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . '/' . uniqid('safe-writer_', true);
        mkdir($this->dir);
    }

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
        return $this->dir . '/' . uniqid('test_', true) . '.php';
    }

    public function testConcurrentWritersNeverExposePartialContent() : void
    {
        $file = $this->dir . '/concurrent.php';
        $stopFile = $this->dir . '/stop';
        FileWriter::writeFile($file, "<?php\nreturn ['version' => 'initial'];");

        // Writers run until the reader is done, so the reader always sees them write.
        // The reader stops after 1 second, once it has seen the file change, or after 10 seconds.
        $commands = [];
        for ($i = 1; $i <= 3; ++$i) {
            $commands[$i] = [__DIR__ . '/safe-writer.php', $file, $stopFile, '20', (string) $i];
        }
        $commands[0] = [__DIR__ . '/reader.php', $file, '1', '10'];

        $processes = [];
        foreach ($commands as $i => $command) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, ...$command],
                [
                    1 => ['file', $this->dir . '/' . $i . '.out', 'w'],
                    2 => ['file', $this->dir . '/' . $i . '.err', 'w'],
                ],
                $pipes
            );
            self::assertIsResource($process);
            $processes[$i] = $process;
        }

        $deadline = microtime(true) + 30;
        $codes = [0 => $this->waitForExit($processes[0], $deadline)];
        touch($stopFile);
        for ($i = 1; $i <= 3; ++$i) {
            $codes[$i] = $this->waitForExit($processes[$i], $deadline);
        }

        foreach ($codes as $i => $code) {
            self::assertNotNull($code, sprintf('Process %d timed out', $i));
            self::assertSame(
                0,
                $code,
                sprintf('Process %d failed: %s', $i, (string) file_get_contents($this->dir . '/' . $i . '.err'))
            );
        }
        self::assertGreaterThan(
            1,
            (int) file_get_contents($this->dir . '/0.out'),
            'The reader did not see the file change'
        );
    }

    /**
     * Returns the exit code, or null when the process is still running at the deadline and has been terminated.
     *
     * @param resource $process
     */
    private function waitForExit($process, float $deadline) : ?int
    {
        while (microtime(true) < $deadline) {
            $status = proc_get_status($process);
            if (! $status['running']) {
                proc_close($process);
                return $status['exitcode'];
            }

            usleep(10000);
        }

        proc_terminate($process);
        proc_close($process);
        return null;
    }

    /**
     * @requires OS Darwin|Linux
     */
    public function testFileIsReplacedNotOverwritten() : void
    {
        $targetFile = $this->getTargetFile();
        file_put_contents($targetFile, 'old content');
        $inode = fileinode($targetFile);
        $handle = fopen($targetFile, 'rb');
        self::assertIsResource($handle);

        FileWriter::writeFile($targetFile, 'new content');
        clearstatcache();

        self::assertNotSame($inode, fileinode($targetFile));
        self::assertSame('old content', stream_get_contents($handle));
        self::assertSame('new content', file_get_contents($targetFile));
        self::assertSame([$targetFile], glob($this->dir . '/*'));

        fclose($handle);
    }

    public function testUnwritableDirThrowsException() : void
    {
        $dir = $this->dir . '/unwritable';
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

        $dir = $this->dir . '/unwritable';
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
        $files = glob($this->dir . '/*');
        foreach ($files === false ? [] : $files as $file) {
            unlink($file);
        }
        rmdir($this->dir);

        parent::tearDown();
    }
}
