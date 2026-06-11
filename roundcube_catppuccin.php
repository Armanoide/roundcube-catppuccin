<?php

/**
 * Catppuccin theme plugin for Roundcube
 *
 * Four-flavour theme overlay for the Elastic skin. User selects a flavour
 * in Settings > General and the choice is persisted to the database
 * (primary) and synced to a cookie (login-page fallback).
 *
 * Flavour → mode mapping:
 *   mocha / macchiato / frappe  → dark-mode
 *   latte                       → light-mode (dark-mode removed)
 *
 * @license   MIT
 * @author    Armanoide
 */
class roundcube_catppuccin extends rcube_plugin
{
    public $task = '.*';

    private const FLAVORS = [
        'mocha'     => 'Mocha',
        'macchiato' => 'Macchiato',
        'latte'     => 'Latte',
        'frappe'    => 'Frappe'
    ];

    private const DARK_FLAVORS = ['mocha', 'macchiato', 'frappe'];

    private string $active_flavor;

    // ── init ─────────────────────────────────────────────────────── */

    public function init(): void
    {
        $this->active_flavor = $this->get_active_flavor();

        $this->include_stylesheet("src/{$this->active_flavor}/colors.css");
        $this->include_stylesheet("src/theme.css");

        // Guard script — only for light flavors (prevents dark-mode leaks)
        if (!in_array($this->active_flavor, self::DARK_FLAVORS, true)) {
            $this->include_script('scripts/dark-mode-guard.js');
        }

        // Header hook runs on every request — inject color-scheme meta tag
        $this->add_hook('header_write', [$this, 'header_write']);

        // Preference hooks — settings task only
        $rcmail = rcmail::get_instance();
        if ($rcmail->task === 'settings') {
            $this->add_hook('preferences_list', [$this, 'prefs_list']);
            $this->add_hook('preferences_save', [$this, 'prefs_save']);
        }
    }

    // ── flavour resolution ───────────────────────────────────────── */
    private function get_active_flavor(): string
    {
        $rcmail = rcmail::get_instance();

        $val = $rcmail->config->get('catppuccin_flavor');
        if (!empty($val) && isset(self::FLAVORS[$val])) {
            return $val;
        }

        $cookie = rcube_utils::get_input_value('catppuccinFlavor', rcube_utils::INPUT_COOKIE);
        if (!empty($cookie) && isset(self::FLAVORS[$cookie])) {
            return $cookie;
        }

        return 'mocha';
    }

    /** Sync cookie so login page picks up the same flavour. */
    private function sync_cookie(string $flavor): void
    {
        rcube_utils::setcookie('catppuccinFlavor', $flavor, 0, false);

        // Sync Roundcube's built-in colorMode cookie so the
        // built-in dark/light toggle button stays consistent.
        //
        // Roundcube's layout.html inline script checks:
        //   if (cookie has 'colorMode=dark'
        //       || (!cookie has 'colorMode=light' && system prefers dark) {
        //       add dark-mode to <html>
        // So for light flavors we MUST explicitly set 'colorMode=light' to
        // prevent the system-preference fallback from adding 'dark-mode'.
        if (in_array($flavor, self::DARK_FLAVORS, true)) {
            rcube_utils::setcookie('colorMode', 'dark', 0, false);
        } else {
            rcube_utils::setcookie('colorMode', 'light', 0, false);
        }
    }

    // ── hooks ───────────────────────────────────────────────────── */

    /**
     * Inject <meta name="color-scheme"> into <head> so the browser
     * doesn't auto-darken this page (system Night Light, Chrome auto-dark).
     */
    public function header_write(array $args): array
    {
        $scheme = in_array($this->active_flavor, self::DARK_FLAVORS, true)
            ? 'dark'
            : 'light';

        $args['content'] = '<meta name="color-scheme" content="' . $scheme . '">' . "\n"
                          . $args['content'];
        return $args;
    }

    // ── preferences_list ──────────────────────────────────────────── */

    public function prefs_list(array $args): array
    {
        if ($args['section'] !== 'general') {
            return $args;
        }

        $dont_override = (array) rcmail::get_instance()->config->get('dont_override', []);
        if (in_array('catppuccin_flavor', $dont_override, true)) {
            return $args;
        }

        $args['blocks']['catppuccin'] = [
            'title'   => 'Catppuccin Theme',
            'options' => [
                'catppuccin_flavor' => [
                    'title'   => 'Catppuccin Theme',
                    'content' => $this->build_picker_html(),
                ],
            ],
        ];

        return $args;
    }

    // ── preferences_save ──────────────────────────────────────────── */

    public function prefs_save(array $args): array
    {
        if ($args['section'] !== 'general') {
            return $args;
        }

        $dont_override = (array) rcmail::get_instance()->config->get('dont_override', []);
        if (in_array('catppuccin_flavor', $dont_override, true)) {
            return $args;
        }

        $flavor = rcube_utils::get_input_value('_catppuccin_flavor', rcube_utils::INPUT_POST);

        if (!empty($flavor) && isset(self::FLAVORS[$flavor])) {
            $args['prefs']['catppuccin_flavor'] = $flavor;
            $this->sync_cookie($flavor);

            rcmail::get_instance()->output->command('reload', 500);
        }

        return $args;
    }

    // ── helpers ───────────────────────────────────────────────────── */

    private function build_picker_html(): string
    {
        $current_flavor = $this->active_flavor;
        $flavors        = self::FLAVORS;

        ob_start();
        include __DIR__ . '/templates/picker.html.php';
        return ob_get_clean();
    }
}
