# Service Manager

The `laminas-log` package provides several components which can be used in 
combination with [laminas-servicemanager](https://docs.laminas.dev/laminas-servicemanager).
These components make it possible to quickly setup a logger instance or to
provide custom writers, filters, formatters, or processors.

## LoggerAbstractServiceFactory

When you register the abstract factory called `Laminas\Log\LoggerAbstractServiceFactory`,
you will be able to setup loggers via the configuration. The abstract factory can be
registered in the service manager using the following configuration:

```php
// module.config.php
return [
    'service_manager' => [
        'abstract_factories' => [
            'Laminas\Log\LoggerAbstractServiceFactory',
        ],
    ],
];
```

> ### Users of laminas-component-installer
>
> If you are using laminas-component-installer, you will have been prompted to
> install laminas-log as a module or configuration provider when you installed
> laminas-log. When you do, the abstract factory is automatically registered
> for you in your configuration.

> ### laminas-log as a module
>
> If you are using laminas-log v2.8 or later with a laminas-mvc-based application,
> but not using laminas-component-installer, you can register `Laminas\Log` as a
> module in your application. When you do, the abstract service factory
> will be registered automatically.

Next, define your custom loggers in the configuration as follows:

```php
// module.config.php
return [
    'log' => [
        'MyLogger' => [
            'writers' => [
                'stream' => [
                    'name' => 'stream',
                    'priority' => \Laminas\Log\Logger::ALERT,
                    'options' => [
                        'stream' => 'php://output',
                        'formatter' => [
                            'name' => \Laminas\Log\Formatter\Simple::class,
                            'options' => [
                                'format' => '%timestamp% %priorityName% (%priority%): %message% %extra%',
                                'dateTimeFormat' => 'c',
                            ],
                        ],
                        'filters' => [
                            'priority' => [
                                'name' => 'priority',
                                'options' => [
                                    'operator' => '<=',
                                    'priority' => \Laminas\Log\Logger::INFO,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'processors' => [
                'requestid' => [
                    'name' => \Laminas\Log\Processor\RequestId::class,
                ],
            ],
        ],
    ],
];
```

The logger can now be retrieved via the service manager using the key used in
the configuration (`MyLogger`):

```php
/** @var \Laminas\Log\Logger $logger */ 
$logger = $container->get('MyLogger');
```

For the formatter and the filters, only the `name` is required, the options have
default values (the values set in this example are the default ones). When only
the name is needed, a shorter format can be used:

```php
// module.config.php
                    'options' => [
                        'stream' => 'php://output',
                        'formatter' => \Laminas\Log\Formatter\Simple::class,
                        'filters' => [
                            'priority' => \Laminas\Log\Filter\Priority::class,
                        ],
                    ],
];
```

Because the main filter is `Priority`, it can be set directly too:

```php
// module.config.php
                        'filters' => \Laminas\Log\Logger::INFO,
];
```

## Custom Writers, Formatters, Filters, and Processors

In the `LoggerAbstractServiceFactory` example above, a custom formatter (called
`MyFormatter`) and a custom filter (called `MyFilter`) are used. These classes
need to be made available to the service manager. It's possible to do this via
custom plugin managers which have the names:

- `log_formatters`
- `log_filters`
- `log_processors`
- `log_writers`

### Example

```php
// module.config.php
return [
    'log_formatters' => [
        'factories' => [
            // ...
        ],
    ],
    'log_filters' => [
        'factories' => [
            // ...
        ],
    ],
    'log_processors' => [
        'factories' => [
            // ...
        ],
    ],
    'log_writers' => [
        'factories' => [
            // ...
        ],
    ],
];
```
