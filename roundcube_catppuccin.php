<?php

/**
 * Catppuccin Theme Loader Plugin for Roundcube
 *
 * @license MIT
 * @author Armanoide
 */
class roundcube_catppuccin extends rcube_plugin
{
    public $task = '.*';

    public function init()
    {
        $rcmail = rcmail::get_instance();

        rcube::write_log('catppuccin', 'Skin actuel: ' . $rcmail->config->get('skin'));

        if ($rcmail->config->get('skin') === 'elastic') {
            $flavor = $rcmail->config->get('catppuccin_flavor', 'mocha');
            $rcmail->output->set_cookie('catppuccinFlavor', $flavor, time() + 86400, '/');
            $this->include_stylesheet("src/{$flavor}/colors.css");
            $this->include_stylesheet("src/theme.css");
        }
    }
}
