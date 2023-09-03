<?php
/*
Plugin Name: Recipe API Plugin
* Description: The Recipe API Plugin is a custom WordPress plugin designed to handle food recipe data through a RESTful API. This plugin allows you to create, read, update, and delete recipes, making it easy to manage and share your favorite culinary creations on your WordPress website.
* Version: 1.0.0
* Author: Jakaria Istauk
* Author URI: https://profiles.wordpress.org/jakariaistauk/
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

namespace JakariaIstaukPlugins;

// Exit if accessed directly.
use JakariaIstaukPlugins\Apps\Api;
use JakariaIstaukPlugins\Apps\Common;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RecipeAPIPlugin {

    public function __construct() {
        add_action('plugins_loaded', function (){
            $plugin_dir_path = plugin_dir_path(__FILE__);
            require_once( $plugin_dir_path . 'Apps/Api.php');
            new Api();

            require_once( $plugin_dir_path . 'Apps/Common.php');
            new Common();
        });
    }

}

$recipe_api_plugin = new RecipeAPIPlugin();
