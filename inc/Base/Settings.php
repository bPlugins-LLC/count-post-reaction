<?php

namespace PostReaction\Base;


class Settings {


    public function register(){
        add_action('admin_menu', [$this, 'add_opt_in_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
    }

    // add a page settings
    public function add_opt_in_menu(){
        add_submenu_page( 'tools.php', __("Count Post React", "count-post-react"), __("Count Post React", "count-post-react"), 'manage_options', 'count-post-react-settings', [$this, 'callback']
        );
    }

    public function callback(){
        wp_enqueue_script('settings');
        ?>
        <div id="postReactCountSettings"></div>
        <?php
    }

    public function admin_enqueue_scripts(){
        wp_register_script('settings', PRC_PLUGIN_DIR . 'dist/settings.js', ['react', 'react-dom'], PRC_VER);
    }

}