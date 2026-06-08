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

        // We only target the official Elastic skin
        if ($rcmail->config->get('skin') === 'elastic') {
            // Read the preferred flavor from config, default to mocha
            $flavor = $rcmail->config->get('catppuccin_flavor', 'mocha');

            // Dynamically include the correct stylesheet from the plugin folder
            $this->include_stylesheet("src/{$flavor}/catppucin.css");
        }
    }
}
