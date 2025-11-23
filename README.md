# WisdomRain HTML Reader

A starter WordPress plugin that provides the scaffolding for rendering HTML reader content with enqueueable assets and a shortcode-based template. Version 0.1.0.

## Features
- Plugin bootstrap with constants, loader, and hooks.
- Front-end asset registration for CSS and JavaScript, enqueued only when the shortcode renders.
- `[wisdomrain_reader]` shortcode with optional `source` attribute for future content injection.
- Admin menu with Dashboard, Manage Readers, and Settings placeholders (admin templates filterable via `wrhr_admin_template_path`).
- Template placeholder and uninstall safety check.

## Installation
1. Copy the `wisdomrain-html-reader` directory into your WordPress `wp-content/plugins/` folder.
2. Activate **WisdomRain HTML Reader** from the WordPress admin dashboard.

## Usage
Add the shortcode to any post or page:

```shortcode
[wisdomrain_reader source="https://example.com"]
```

The template will display a placeholder message and surface the `source` attribute in the rendered markup.

## Development
- PHP 7.4+ and WordPress 5.8+ are expected for this skeleton.
- Assets live in `assets/css` and `assets/js` and are enqueued automatically via the loader.
- Admin templates live in `templates/` and are loaded through the admin menu callbacks.

## Uninstall
The `uninstall.php` file includes a guard for secure uninstallation. Extend it to clean up any stored options if needed.
