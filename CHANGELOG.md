# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0 - TBD

### Added

- Adds function to safely writing files to avoid race conditions when
  the same file is written multiple times in a short time period,
  and errors on reading not fully written files. Example usage:

  ```php
  use Webimpress\SafeWriter\FileWriter;

  $targetFile = __DIR__ . '/config-cache.php';
  $content = "<?php\nreturn " . var_export($data, true) . ';';

  FileWriter::writeFile($targetFile, $content, 0666);
  ```

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
