# roundcube_catppuccin

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://github.com/Armanoide/roundcube_catppuccin/blob/main/LICENSE)
[![Roundcube plugin](https://img.shields.io/badge/Roundcube-Plugin-blue.svg)](https://roundcube.net)

Catppuccin theme overlay for Roundcube's Elastic skin. Transforms the default interface into a warm, soothing dark theme with four flavour options switchable from Settings.

![Dark mode preview](https://raw.githubusercontent.com/catppuccin/catppuccin/main/assets/palette/macchiato.png)

## Flavours

| ☕ Mocha | 🌙 Macchiato | 🪍 Frappé | 🤎 Latte |
|:---:|:---:|:---:|:---:|
| Dark | Dark | Dark | Light |

## Features

- 🎨 **Four Catppuccin palettes** — switch between Mocha, Macchiato, Frappé, and Latte
- ⚙️ **Settings integration** — change flavour in Settings > General, saved to database
- 🍪 **Cookie sync** — flavour remembered on login page before authentication
- 🔧 **Zero-config** — plug and play with the Elastic skin
- 📦 **Composer-ready** — works with `roundcube/plugin-installer`
- 🐳 **Docker-friendly** — works with `roundcube/roundcubemail`

## Requirements

- Roundcube 1.6+
- PHP 8.0+
- **Elastic skin** (default in Roundcube)

## Installation

### Via Composer

```bash
cd /path/to/roundcube
composer require armanoide/roundcube-catppuccin
```

### Manual Installation

1. Clone this repository into your `plugins/` directory
2. Enable the plugin in `config/config.inc.php`:

```php
$config['plugins'][] = 'roundcube_catppuccin';
```

## Configuration

### Global default flavour (optional)

Set the default flavour for all users in `config/config.inc.php`:

```php
$config['catppuccin_flavor'] = 'mocha';
```

### Per-user selection

Once the plugin is enabled, users can select their preferred flavour in:
**Settings > General > Catppuccin Theme**

The selection is saved to the user's preferences (database) and synced to a
cookie for the login page.

### Lock the setting (optional)

Prevent users from changing their flavour by adding it to `dont_override`:

```php
$config['dont_override'][] = 'catppuccin_flavor';
```

## Docker

With the official `roundcube/roundcubemail` image:

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

Mount the `plugins` volume to persist the installed plugin across container
restarts.
