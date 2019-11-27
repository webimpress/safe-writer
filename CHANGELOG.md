# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.1.0 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.0.1 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.0.0 - 2019-11-27

### Added

- [#5](https://github.com/webimpress/safe-writer/pull/5) adds `\Throwable` extension for package-specific exception marker `Webimpress\SafeWriter\Exception\ExceptionInterface`.

### Changed

- [#5](https://github.com/webimpress/safe-writer/pull/5) changes all exception classes to be non-instantiable and all theirs public method to be internal.
  Library exceptions can only be caught in the user code, cannot be thrown.  

- [#5](https://github.com/webimpress/safe-writer/pull/5) changes all method declarations to have type hints and return types.

### Deprecated

- Nothing.

### Removed

- [#5](https://github.com/webimpress/safe-writer/pull/5) removes support for PHP versions prior to 7.2.

### Fixed

- Nothing.

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
