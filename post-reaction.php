<?php
/*
Plugin Name: Post Reaction
Version: 1.0.0
Description: Count post reactions (likes, loves, cares) and enforce one-time reaction per user.
Author: bPlugins LLC
*/

/*Some Set-up*/
define('PRC_PLUGIN_DIR', plugin_dir_url(__FILE__));
define('PRC_PLUGIN_FILE_BASENAME', plugin_basename( __FILE__ ));
define('PRC_PLUGIN_DIR_BASENAME', plugin_basename( __DIR__ ));
define('PRC_VER', '1.0.0');

// auto loader configuration
if(file_exists(dirname(__FILE__).'/vendor/autoload.php')){
    require_once(dirname(__FILE__).'/vendor/autoload.php');
}

// Add custom meta fields for post reactions
function post_reactions_counter_setup() {
    if(class_exists('PostReaction\\Init')){
        PostReaction\Init::register_services();
    }
}
add_action('plugins_loaded', 'post_reactions_counter_setup');







