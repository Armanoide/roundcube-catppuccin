<?php

/**
 * Catppuccin theme plugin for Roundcube
 *
 * Four-flavor theme overlay for the Elastic skin. User selects a flavor
 * in Settings > General and the choice is persisted to the database
 * (primary) and synced to a cookie (login-page fallback).
 *
 * @license   MIT
 * @author    Armanoide
 */
class roundcube_catppuccin extends rcube_plugin
{
    public $task = '.*';

    /** Supported flavors keyed by slug, values are display labels. */
    private const FLAVORS = [
        'mocha'     => 'Mocha',
        'macchiato' => 'Macchiato',
        'latte'     => 'Latte',
        'frappe'    => 'Frapp\u00e9',
    ];

    /** The resolved flavor for the current request. */
    private string $active_flavor;

    // --------------------------------------------------------------- Init */

    public function init(): void
    {
        $this->active_flavor = $this->get_active_flavor();

        // Include stylesheets on every page
        $this->include_stylesheet("src/{$this->active_flavor}/colors.css");
        $this->include_stylesheet('src/theme.css');

        // Only register preference hooks when in settings task
        $rcmail = rcmail::get_instance();
        if ($rcmail->task === 'settings') {
            $this->add_hook('preferences_list', [$this, 'prefs_list']);
            $this->add_hook('preferences_save', [$this, 'prefs_save']);
        }
    }

    // ----------------------------------------------------------- Flavour resolution cascade:
    //  1. Roundcube config (DB-backed user-prefs merged in automatically
    //  2. Cookie (pre-auth / login page)
    //  3. Hard-coded default
    private function get_active_flavor(): string
    {
        $rcmail = rcmail::get_instance();

        // 1 — DB-backed config
        $val = $rcmail->config->get('catppuccin_flavor');
        if (!empty($val) && isset(self::FLAVORS[$val])) {
            return $val;
        }

        // 2 — Cookie (pre-auth / login page)
        $cookie = rcube_utils::get_input_value('catppuccinFlavor', rcube_utils::INPUT_COOKIE);
        if (!empty($cookie) && isset(self::FLAVORS[$cookie])) {
            return $cookie;
        }

        // 3 — Hard default
        return 'mocha';
    }

    /** Sync the cookie to always match the DB-backed flavour. */
    private function sync_cookie(string $flavor): void
    {
        rcube_utils::setcookie('catppuccinFlavor', $flavor, 0, false, false);
    }

    // --------------------------------------------------------------- Preferences list

    public function prefs_list(array $args): array
    {
        if ($args['section'] !== 'general') {
            return $args;
        }

        // Respect admin-level override (lock the setting)
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

    // --------------------------------------------------------------- Preferences save

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
        }

        return $args;
    }

    // --------------------------------------------------------------- Helpers */

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
