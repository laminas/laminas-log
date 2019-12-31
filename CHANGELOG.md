# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.12.0 - 2019-12-27

### Added

- [zendframework/zend-log#99](https://github.com/zendframework/zend-log/pull/99) adds `Laminas\Log\PsrLoggerAbstractAdapterFactory`, which will create instances of `PsrLoggerAdapter`. Usage is exactly like with `Laminas\Log\LoggerAbstractServiceFactory`, with the exception that it looks under the `psr_log` configuration key instead of the `log` key for logger configuration.

### Changed

- [zendframework/zend-log#100](https://github.com/zendframework/zend-log/pull/100) updates the psr/log constraint to 1.1.2, removing the need for an extra autoloader in this package.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.11.0 - 2019-08-23

### Added

- [zendframework/zend-log#96](https://github.com/zendframework/zend-log/pull/96) adds support for PHP 7.3.

- [zendframework/zend-log#83](https://github.com/zendframework/zend-log/pull/83) adds
  ability to define custom ignored namespaces in addition to default `Laminas\Log`
  in `Backtrace` processor.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-log#88](https://github.com/zendframework/zend-log/pull/88) removes
  support for HHVM.

### Fixed

- Nothing.

## 2.10.0 - 2018-04-09

### Added

- [zendframework/zend-log#58](https://github.com/zendframework/zend-log/pull/58) adds the class
  `Laminas\Log\Formatter\Json`, which will format log lines as individual JSON
  objects.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.9.3 - 2018-04-09

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-log#79](https://github.com/zendframework/zend-log/pull/79) and
  [zendframework/zend-log#86](https://github.com/zendframework/zend-log/pull/86) provide fixes to
  ensure the `FingersCrossed`, `Mongo`, and `MongoDB` writers work under PHP
  7.2.

## 2.9.2 - 2017-05-17

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-log#74](https://github.com/zendframework/zend-log/pull/74) fixes how the various
  plugin manager factories initialize the plugin manager instances, ensuring
  they are injecting the relevant configuration from the `config` service and
  thus seeding them with configured plugin services. This means that the
  `log_processors`, `log_writers`, `log_filters`, and `log_formatters`
  configuration will now be honored in non-laminas-mvc contexts.
- [zendframework/zend-log#62](https://github.com/zendframework/zend-log/pull/62) fixes registration of
  the alias and factory for the `PsrPlaceholder` processor plugin.
- [zendframework/zend-log#66](https://github.com/zendframework/zend-log/pull/66) fixes the namespace
  of the `LogFormatterProviderInterface` when registering the
  `LogFormatterManager` with the laminas-modulemanager `ServiceListener`.
- [zendframework/zend-log#67](https://github.com/zendframework/zend-log/pull/67) ensures that content
  being injected into a DOM node by `Laminas\Log\Formatter\Xml` is escaped so that
  XML entities will be properly emitted.
- [zendframework/zend-log#73](https://github.com/zendframework/zend-log/pull/73) adds a missing import
  statement to the `Psr` log writer.

## 2.9.1 - 2016-08-11

### Added

- [zendframework/zend-log#53](https://github.com/zendframework/zend-log/pull/53) adds a suggestion to
  the package definition of ext/mongodb, for those who want to use the MongoDB
  writer.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-log#56](https://github.com/zendframework/zend-log/pull/56) fixes an edge case
  with the `AbstractWriter` whereby instantiating a
  `Laminas\Log\Writer\FormatterPluginManager` or `FilterPluginManager` prior to
  creating a writer instance would lead to a naming conflict. New aliases were
  added to prevent the conflict going forwards.

## 2.9.0 - 2016-06-22

### Added

- [zendframework/zend-log#46](https://github.com/zendframework/zend-log/pull/46) adds the ability to
  specify log writer, formatter, filter, and processor plugin configuration via
  the new top-level keys:
  - `log_filters`
  - `log_formatters`
  - `log_processors`
  - `log_writers`
  These follow the same configuration patterns as any other service
  manager/plugin manager as implemented by laminas-servicemanager.

  Additionally, you can now specify filer, formatter, and processor *services*
  when specifying writer configuration for a logger, as these are now backed
  by the above plugin managers.

### Deprecated

- Nothing.

### Removed

- Removes support for PHP 5.5.

### Fixed

- [zendframework/zend-log#38](https://github.com/zendframework/zend-log/pull/38) adds the `MongoDb`
  writer to the list of available writer plugins; the writer was added in a
  previous release, but never enabled within the default set of writers.

## 2.8.3 - 2016-05-25

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Corrected licence headers across files within the project

## 2.8.2 - 2016-04-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-log#43](https://github.com/zendframework/zend-log/pull/43) fixes the
  `Module::init()` method to properly receive a `ModuleManager` instance, and
  not expect a `ModuleEvent`.

## 2.8.1 - 2016-04-06

### Added

- [zendframework/zend-log#40](https://github.com/zendframework/zend-log/pull/40) adds the
  `LogFilterProviderInterface` and `LogFormatterProviderInterface` referenced in
  the `Module` class starting in 2.8.0.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.8.0 - 2016-04-06

### Added

- [zendframework/zend-log#39](https://github.com/zendframework/zend-log/pull/39) adds the following
  factory classes for the exposed plugin managers in the component:
  - `Laminas\Log\FilterPluginManagerFactory`, which returns `FilterPluginManager` instances.
  - `Laminas\Log\FormatterPluginManagerFactory`, which returns `FormatterPluginManager` instances.
  - `Laminas\Log\ProcessorPluginManagerFactory`, which returns `ProcessorPluginManager` instances.
  - `Laminas\Log\WriterPluginManagerFactory`, which returns `WriterPluginManager` instances.
- [zendframework/zend-log#39](https://github.com/zendframework/zend-log/pull/39) exposes the
  package as a Laminas component and/or generic configuration provider, by adding the
  following:
  - `ConfigProvider`, which maps the available plugin managers to the
    corresponding factories as listed above, maps the `Logger` class to the
    `LoggerServiceFactory`, and registers the `LoggerAbstractServiceFactory` as
    an abstract factory.
  - `Module`, which does the same as `ConfigProvider`, but specifically for
    laminas-mvc applications. It also provices a specifications to
    `Laminas\ModuleManager\Listener\ServiceListener` to allow modules to provide
    configuration for log filters, formatters, processors, and writers.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.7.2 - 2016-04-06

### Added

- [zendframework/zend-log#30](https://github.com/zendframework/zend-log/pull/30) adds documentation
  for each of the supported log writers.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-log#33](https://github.com/zendframework/zend-log/pull/33) fixes an issue with
  executing `chmod` on files mounted via NFS on an NTFS partition when using the
  stream writer.

## 2.7.1 - 2016-02-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-log#28](https://github.com/zendframework/zend-log/pull/28) restores the "share
  by default" flag settings of all plugin managers back to boolean `false`,
  allowing multiple instances of each plugin type. (This restores backwards
  compatibility with versions prior to 2.7.)

## 2.7.0 - 2016-02-09

### Added

- [zendframework/zend-log#7](https://github.com/zendframework/zend-log/pull/7) and
  [zendframework/zend-log#15](https://github.com/zendframework/zend-log/pull/15) add a new argument
  and option to `Laminas\Log\Writer\Stream` to allow setting the permission mode
  for the stream. You can pass it as the optional fourth argument to the
  constructor, or as the `chmod` option if using an options array.
- [zendframework/zend-log#10](https://github.com/zendframework/zend-log/pull/10) adds `array` to the
  expected return types from `Laminas\Log\Formatter\FormatterInterface::format()`,
  codifying what we're already allowing.
- [zendframework/zend-log#24](https://github.com/zendframework/zend-log/pull/24) prepares the
  documentation for publication, adds a chapter on processors, and publishes it
  to https://docs.laminas.dev/laminas-log/

### Deprecated

- [zendframework/zend-log#14](https://github.com/zendframework/zend-log/pull/14) deprecates the
  following, suggesting the associated replacements:
  - `Laminas\Log\Writer\FilterPluginManager` is deprecated; use
    `Laminas\Log\FilterPluginManager` instead.
  - `Laminas\Log\Writer\FormatterPluginManager` is deprecated; use
    `Laminas\Log\FormatterPluginManager` instead.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-log#14](https://github.com/zendframework/zend-log/pull/14) and
  [zendframework/zend-log#17](https://github.com/zendframework/zend-log/pull/17) update the component
  to be forwards-compatible with laminas-stdlib and laminas-servicemanager v3.

## 2.6.0 - 2015-07-20

### Added

- [zendframework/zend-log#6](https://github.com/zendframework/zend-log/pull/6) adds
  [PSR-3](http://www.php-fig.org/psr/psr-3/) support to laminas-log:
  - `Laminas\Log\PsrLoggerAdapter` allows you to decorate a
    `Laminas\Log\LoggerInterface` instance so it can be used wherever a PSR-3
    logger is expected.
  - `Laminas\Log\Writer\Psr` allows you to decorate a PSR-3 logger instance for use
    as a log writer with `Laminas\Log\Logger`.
  - `Laminas\Log\Processor\PsrPlaceholder` allows you to use PSR-3-compliant
    message placeholders in your log messages; they will be substituted from
    corresponding keys of values passed in the `$extra` array when logging the
    message.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.2 - 2015-07-06

### Added

- [zendframework/zend-log#2](https://github.com/zendframework/zend-log/pull/2) adds
  the ability to specify the mail transport via the configuration options for a
  mail log writer, using the same format supported by
  `Laminas\Mail\Transport\Factory::create()`; as an example:

  ```php
  $writer = new MailWriter([
      'mail' => [
          // message options
      ],
      'transport' => [
          'type' => 'smtp',
          'options' => [
               'host' => 'localhost',
          ],
      ],
  ]);
  ```

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-log#4](https://github.com/zendframework/zend-log/pull/4) adds better, more
  complete verbiage to the `composer.json` `suggest` section, to detail why
  and when you might need additional dependencies.
- [zendframework/zend-log#1](https://github.com/zendframework/zend-log/pull/1) updates the code to
  remove conditionals related to PHP versions prior to PHP 5.5, and use bound
  closures in tests (not possible before 5.5).
