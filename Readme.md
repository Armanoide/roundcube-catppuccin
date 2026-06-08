# roundcube_catppuccin

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://github.com/Armanoide/roundcube_catppuccin/blob/main/LICENSE)
[![Roundcube plugin](https://img.shields.io/badge/Roundcube-Plugin-blue.svg)](https://roundcube.net)

Catppuccin theme overlay for Roundcube's Elastic skin. Transforms the default interface into a warm, soothing dark theme.

![Dark mode preview](https://raw.githubusercontent.com/catppuccin/catppuccin/main/assets/palette/macchiato.png)

## Currently Available Flavours

| 🌿 Mocha |
|:---:|
| Included |

## Features

- 🎨 **Catppuccin Mocha palette** — warm and subdued dark color scheme
- 🔧 **Zero-config** — plug and play with the Elastic skin
- 📦 **Composer-ready** — works with `roundcube/plugin-installer`
- 🐳 **Docker-friendly** — works with `roundcube/roundcubemail`

## Requirements

- Roundcube 2.0+
- **Elastic skin** (default in Roundcube)

## Installation

### Via Composer

```bash
cd /path/to/roundcube
composer require armanoide/roundcube-catppuccin
```

### Manual Installation
1. Clone this repository into your plugins/ directory
2. Enable the plugin in config/config.inc.php

### Configuration
Enable the plugin and set your preferred flavor in your Roundcube config.inc.php:
```php
$config['plugins'] = ['roundcube_catppuccin'];
$config['catppuccin_flavor'] = 'mocha';
```


If you're running Roundcube via the official roundcube/roundcubemail Docker image:
`docker-compose.yml`
```yaml
services:
  roundcubemail:
    image: roundcube/roundcubemail:latest
    environment:
      - ROUNDCUBEMAIL_SKIN=elastic
      - ROUNDCUBEMAIL_PLUGINS=archive,zipdownload,roundcube_catppuccin
    volumes:
      - ./www/config:/var/www/html/config
      - ./www/plugins:/var/www/html/plugins
```

Mount your plugins directory to persist the installed plugin across container restarts.
```php
Roundcube config.inc.php
<?php
$config['plugins'] = [
    'archive',
    'zipdownload',
    'roundcube_catppuccin',
];
$config['catppuccin_flavor'] = 'mocha';
```

