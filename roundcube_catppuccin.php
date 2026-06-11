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
        'frappe'    => 'Frapp\u00e9',
    ];

    private const DARK_FLAVORS = ['mocha', 'macchiato', 'frappe'];

    private string $active_flavor;

    // ── init ─────────────────────────────────────────────────────── */

    public function init(): void
    {
        $this->active_flavor = $this->get_active_flavor();

        $this->include_stylesheet("src/{$this->active_flavor}/colors.css");
        $this->include_stylesheet("src/theme.css");

        // Header hook runs on every request — inject mode-force script
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

    // ── Force correct colour scheme — meta tag + class guard     */
    // 1. `<meta name="color-scheme">` tells the browser: don't
    //    auto-darken this page (system Night Light, Chrome dark mode).
    // 2. For light flavors, any code that runs after <head> that checks the
    //    current class and adds `dark-mode` back (e.g. Roundcube's layout.html).
    // 3. `DOMContentLoaded` + `load` handlers double-check in case
    //    extension injected stylesheets attempt to re-add.</span>
    public function header_write(array $args): array
    {
        $use_dark = in_array($this->active_flavor, self::DARK_FLAVORS, true);
        $scheme   = $use_dark ? 'dark' : 'light';

        // Meta tag: forces the browser's color scheme.
        // Prevents automatic dark-mode from system Night Light or Chrome auto-dark.
        $injection = '<meta name="color-scheme" content="' . $scheme . '">' . "\n";

        // If using a light palette, strip any `dark-mode` class that any agent
        // tries to add (Darkreader, browser SDK, etc) and sync the Roundcube
        // toggle button so the button text and class reflect the actual state.
        if (!$use_dark) {
            $injection .= '<script>
  (function(){
    var h = document.documentElement;
    var pillage = function(){
      // Remove dark-mode from <html>
      h.classList.remove(\'dark-mode\');
      // Sync the Roundcube dark/light toggle button with the actual state
      var btn = document.querySelector(\'#taskmenu a.theme\');
      if (btn) {
        btn.classList.remove(\'dark\');
        btn.classList.add(\'light\');
        var span = btn.querySelector(\'span\');
        if (span) {
          var txt = span.textContent || span.innerText || \'\';
          span.textContent = txt.replace(/Dark|Light/gi, \'Light\');
        }
      }
    };
    pillage();
    // Standard events
    document.addEventListener(\'DOMContentLoaded\', pillage, false);
    window.addEventListener(\'load\', pillage, false);
    // Watch for class changes and remove dark-mode if it reappears
    if (window.MutationObserver) {
      var observer = new MutationObserver(function(mutations){
        for (var i = 0; i < mutations.length; i++) {
          if (mutations[i].attributeName === \'class\') { pillage(); break; }
        }
      });
      observer.observe(h, { attributes: true, attributeOldValue: false });
    }
  })();
</script>' . "\n";
        }

        $args['content'] = $injection . $args['content'];
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
                    'title'   => '',
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
        $current = $this->active_flavor;

        ob_start(); ?>
    <div id="catppuccin-theme-picker" style="padding:.75rem;margin-top:1rem;">
        <div style="font-weight:bold;">Catppuccin Theme</div>
        <input type="hidden" name="_catppuccin_flavor"
               id="rcmfd_catppuccin_flavor"
               value="<?= htmlspecialchars($current) ?>">
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:.5rem;">

        <?php foreach (self::FLAVORS as $slug => $label):
            $checked = $slug === $current;
            $border  = $checked ? '#cba6f7' : '#45475a';
            $clr     = $checked ? 'font-weight:bold;' : '';
        ?>
            <label style="cursor:pointer;padding:6px 14px;border-radius:6px;
                             border:1px solid <?= $border ?>;
                             background:transparent;"
                   onclick="catppuccin_choose(this)">
                <input type="radio"
                       name="_catppuccin_flavor"
                       value="<?= htmlspecialchars($slug) ?>"
                       <?= $checked ? 'checked' : '' ?>
                       style="display:none;">
                <span style="<?= $clr ?>"><?= htmlspecialchars($label) ?></span>
            </label>
        <?php endforeach; ?>

        </div>
    </div>

    <script>
    function catppuccin_choose(el) {
        var picker = document.getElementById('catppuccin-theme-picker');
        picker.querySelectorAll('label').forEach(function(lbl) {
            lbl.style.borderColor = '#45475a';
            lbl.querySelector('span').style.fontWeight = '';
        });
        el.style.borderColor     = '#cba6f7';
        el.querySelector('span').style.fontWeight = 'bold';
        document.getElementById('rcmfd_catppuccin_flavor').value =
            el.querySelector('input').value;
    }
    </script>
    <?php return ob_get_clean();
    }
}
