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

namespace Jakaria_Istauk_Plugins;

use Jakaria_Istauk_Plugins\Apps\Api;
use Jakaria_Istauk_Plugins\Apps\Common;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Defining plugin constants.
 *
 * @since 3.0.0
 */
define('WPRA_PLUGIN_FILE', __FILE__);
define('WPRA_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WPRA_PLUGIN_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('WPRA_PLUGIN_URL', trailingslashit(plugins_url('/', __FILE__)));
define('WPRA_PLUGIN_VERSION', '1.0.0');

class Recipe_API_Plugin {

	// instance container
	private static $instance = null;

    public function __construct() {
	    /**
	     * Including composer autoloader globally.
	     *
	     * @since 3.0.0
	     */
	    require_once WPRA_PLUGIN_PATH . 'autoload.php';

        add_action('plugins_loaded', function (){
			new Api();
			new Common();
        });
    }

	/**
	 * Singleton instance
	 *
	 * @since 3.0.0
	 */
	public static function instance()
	{
		if (self::$instance == null) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Recipe_API_Plugin::instance();
