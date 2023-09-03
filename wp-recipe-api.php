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
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class RecipeAPIPlugin {

    public function __construct() {
        // Initialize the plugin
        add_action('rest_api_init', array($this, 'init'));
    }

    // Initialize custom endpoints
    public function init() {
        register_rest_route('recipe-api/v1', '/recipes', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_all_recipes'),
        ));

        register_rest_route('recipe-api/v1', '/recipes/(?P<id>\d+)', array(
            'methods' => 'GET, POST, PUT, DELETE',
            'callback' => array($this, 'recipe_api_handler'),
        ));
    }

    // Callback function to handle recipe API requests
    public function recipe_api_handler($request) {
        // Your API logic goes here
        // You'll need to implement the logic to create, read, update, and delete recipes

        // Example response
        $response = array(
            'message' => 'Recipe API is working!',
        );

        return rest_ensure_response($response);
    }

    // Callback function to get all recipes
    public function get_all_recipes($request) {
        // Your logic to retrieve all recipes goes here

        // Example response
        $recipes = array(
            array(
                'title' => 'Spaghetti Carbonara',
                'ingredients' => 'Pasta, Eggs, Pancetta, Parmesan Cheese',
                'instructions' => 'Cook pasta, mix with egg and pancetta mixture, add cheese.',
            ),
            // Add more recipes here
        );

        return rest_ensure_response($recipes);
    }
}

// Instantiate the RecipeAPIPlugin class
$recipe_api_plugin = new RecipeAPIPlugin();
