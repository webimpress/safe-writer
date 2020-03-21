# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.2 - 2020-03-21

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#6](https://github.com/webimpress/safe-writer/pull/6) fixes issue when target directory is not writeable - throws exception earlier and prevents fallback to system temp directory.

## 1.0.1 - 2019-11-16

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#4](https://github.com/webimpress/safe-writer/pull/4) fixes exception message when temporary file cannot be created.

## 1.0.0 - 2019-11-15

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
