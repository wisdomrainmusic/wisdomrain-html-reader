# WisdomRain HTML Reader — Structure & Performance Audit

This report documents the current code structure, hook chain, storage model, and runtime pipeline of the WisdomRain HTML Reader plugin.

## 1) Plugin root & bootstrap
- **File:** `wisdomrain-html-reader.php`
- Sets plugin header and guards direct access via `ABSPATH`.
- Defines constants: `WRHR_VERSION`, `WRHR_PLUGIN_FILE`, `WRHR_PLUGIN_DIR`, `WRHR_URL`, `WRHR_PLUGIN_BASENAME`.
- Loads the loader class (`includes/class-wrhr-loader.php`).
- Hooks `wrhr_initialize_plugin` to `plugins_loaded` and `wrhr_load_textdomain` to `init`.

## 2) Loader and module structure
- **Loader:** `WRHR_Loader` (`includes/class-wrhr-loader.php`)
  - Singleton initialized via `WRHR_Loader::init()`.
  - Registers autoloading for `WRHR_` classes in `includes/`.
  - Hooks `WRHR_Shortcode::init()` on `init`, initializes REST routes, and registers admin menus when in the dashboard.
  - Registers and enqueues front-end assets and the shortcode handler.
- **Shortcode:** `WRHR_Shortcode` (`includes/class-wrhr-shortcode.php`)
  - Registers `[wrhr_reader]`.
  - Fetches reader data, enqueues assets, and renders `templates/shortcode-reader.php`.
- **Admin:** `WRHR_Admin` (`includes/class-wrhr-admin.php`)
  - Adds top-level and submenu pages, guards capability, and enqueues admin styles.
  - Supports creating, deleting, and editing readers plus updating their book lists.
- **REST:** `WRHR_REST` (`includes/class-wrhr-rest.php`)
  - Exposes `wrhr/v1/clean` for HTML sanitization via `WRHR_Cleaner`.
- **Cleaner:** `WRHR_Cleaner` (`includes/class-wrhr-cleaner.php`)
  - DOM-based sanitizer flattening disallowed tags, normalizing headings, and stripping unsafe attributes.
- **Storage:** `WRHR_Readers` (`includes/class-wrhr-readers.php`)
  - Stores readers in the `wrhr_readers` option with CRUD helpers and book updates.
- **Assets helper:** `WRHR_Assets` (`includes/class-wrhr-assets.php`)
  - Enqueues CSS/JS for the reader and renders the global modal markup on `wp_footer`.

## 3) Assets
- **CSS:** `assets/css/wrhr-style.css`, `assets/css/wrhr-reader.css` (front-end styles); `assets/css/wrhr-style.css` reused for admin.
- **JS:** `assets/js/wrhr-renderer.js` enqueued on the front end (depends on jQuery and localized REST root). `assets/js/reader.js` is registered via the loader for template usage.

## 4) Hook chain overview
- `plugins_loaded` → `wrhr_initialize_plugin` → loader setup/autoload.
- `init` → textdomain load, shortcode registration, REST boot.
- `admin_menu` → admin pages registered (and `admin_enqueue_scripts` for styles on plugin screens).
- `wp_enqueue_scripts` → asset registration for front-end consumption.
- `wp_footer` → modal markup injection.

## 5) Shortcode runtime pipeline
1. `[wrhr_reader id="..."]` detected.
2. `WRHR_Shortcode::render_reader` sanitizes the ID and retrieves the reader via `WRHR_Readers::get()`.
3. Front-end assets enqueued (`WRHR_Assets::enqueue_frontend`).
4. `templates/shortcode-reader.php` renders the wrapper that the JS renderer binds to.
5. Modal HTML exists in the footer; JS handles open/close, pagination, and fullscreen toggling.

## 6) Data model & storage
- Option key: `wrhr_readers` (associative array).
- Reader shape:
  ```php
  [
    'id'    => 'wrhr_<uuid>',
    'name'  => 'Reader Name',
    'slug'  => 'reader-slug',
    'books' => [
        [
            'title'    => 'Book Title',
            'author'   => 'Author',
            'html_url' => 'https://.../book.html',
            'buy_link' => 'https://...'
        ],
        // ...
    ],
  ]
  ```
- CRUD operations and book updates are sanitized before persisting via `update_option`.

## 7) Performance considerations
- PHP side is lightweight: a single option fetch for readers and simple template rendering.
- JS work is limited to modal lifecycle and pagination state management; heavy DOM only occurs when large HTML payloads are injected.
- Cleaner uses DOMDocument parsing; executed server-side during REST clean requests.
- Asset loading is scoped to shortcode render or admin pages to reduce front-end overhead.
