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
use function fgets;
use function file_get_contents;
use function file_put_contents;
use function fileinode;
use function fileperms;
use function fopen;
use function fwrite;
use function glob;
use function mkdir;
use function octdec;
use function proc_close;
use function proc_open;
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

use const PHP_BINARY;
use const PHP_EOL;

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
        FileWriter::writeFile($file, "<?php\nreturn ['version' => 'initial'];");

        $duration = '2';
        $commands = [[__DIR__ . '/reader.php', $file, $duration]];
        for ($i = 1; $i <= 3; ++$i) {
            $commands[] = [__DIR__ . '/safe-writer.php', $file, $duration, (string) $i];
        }

        $processes = [];
        $pipes = [];
        foreach ($commands as $i => $command) {
            $processPipes = [];
            $process = proc_open(
                [PHP_BINARY, ...$command],
                [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $processPipes
            );
            self::assertIsResource($process);
            $processes[$i] = $process;
            $pipes[$i] = $processPipes;
        }

        // start all processes at the same time
        foreach ($pipes as $processPipes) {
            self::assertSame('ready' . PHP_EOL, fgets($processPipes[1]));
        }
        foreach ($pipes as $processPipes) {
            fwrite($processPipes[0], 'go' . PHP_EOL);
            fclose($processPipes[0]);
        }

        $outputs = [];
        foreach ($processes as $i => $process) {
            $outputs[$i] = (string) stream_get_contents($pipes[$i][1]);
            $errors = (string) stream_get_contents($pipes[$i][2]);
            fclose($pipes[$i][1]);
            fclose($pipes[$i][2]);

            self::assertSame(0, proc_close($process), sprintf('Process %d failed: %s', $i, $errors));
        }

        // the reader saw the file change while it was reading it
        self::assertGreaterThan(1, (int) $outputs[0]);
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
