# Phraseanet Graylog Plugin

A Graylog plugin for [Phraseanet](https://github.com/alchemy-fr/Phraseanet).

## Installation

First, retrieve the latest version :

```
git clone https://github.com/Phraseanet/graylog-plugin.git
```

Then, use Phraseanet Konsole to install the plugin (please be sure to run
the command with the right user - www-data for instance)

```
bin/console plugins:add /path/to/graylog-plugin
```

## Please read this before continue

Graylog 2 server must listen `GELF UDP` to use this plugin.

## Configuration

Use the following options to configure the plugin in your `configuration.yml`

```yaml
plugins:
    graylog-plugin:
        name: phraseanet
        host: localhost
        port: 12201
        # values : [DEBUG | INFO | NOTICE | WARNING | ERROR | CRITICAL | ALERT | EMERGENCY]
        level: ERROR
        channels:
            - task-manager.logger
            - monolog
```
 - name: the name of the source. Uses the hostname if not provided
 - level: optional, default to `DEBUG`
 - channels: optional, array, default to all channels.

## Uninstall

Use Phraseanet Konsole to uninstall the plugin

```
bin/console plugin:remove graylog-plugin
```

## License

Phraseanet Graylog plugin is released under the MIT license
