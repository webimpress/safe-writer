<?php

namespace WebimpressTest\SafeWriter;

use PHPUnit\Framework\TestCase;
use Webimpress\SafeWriter\FileWriter;

use function file_get_contents;
use function fileperms;
use function sys_get_temp_dir;
use function uniqid;

class FileWriterTest extends TestCase
{
    public function testContentIsSavedToTheFile()
    {
        $targetFile = $this->getTargetFile();

        FileWriter::writeFile($targetFile, 'some data');

        self::assertSame('some data', file_get_contents($targetFile));
    }

    public function permission()
    {
        //    chmod  umask expected
        yield [0666, 022, 0644];
        yield [0644, 0,   0644];
        yield [0600, 022, 0600];
    }

    /**
     * @dataProvider permission
     *
     * @param int $chmod
     * @param int $umask
     * @param int $expectedChmod
     */
    public function testCorrectChmodIsSet($chmod, $umask, $expectedChmod)
    {
        $targetFile = $this->getTargetFile();

        $currentUmask = umask($umask);

        FileWriter::writeFile($targetFile, 'content', $chmod);
        umask($currentUmask);

        self::assertSame($expectedChmod, $this->getFilePermission($targetFile));
    }

    /**
     * @param string $file
     * @return int
     */
    private function getFilePermission($file)
    {
        return octdec(substr(sprintf('%o', fileperms($file)), -4));
    }

    /**
     * @return string
     */
    private function getTargetFile()
    {
        return sys_get_temp_dir() . '/' . uniqid('test_', true) . '.php';
    }
}
