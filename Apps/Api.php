<?php

namespace JakariaIstaukPlugins\Apps;

class Api
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'init'));
    }
    public function init() {
        // Include a PHP file from a subdirectory
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
	        'request' => $request
        );

        return rest_ensure_response($response);
    }

    // Callback function to get all recipes
	public function get_all_recipes( $request ) {
		$args = array(
			'post_type' => 'recipe',
			'per_page'  => - 1, // Retrieve all posts of the 'recipe' type
		);

		$query   = new \WP_Query( $args );
		$recipes = [];

		if ( $query->have_posts() ) {
			$posts = $query->posts;

			foreach ( $posts as $post ) {
				$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) );
				$recipes[] = [
					'id'          => $post->ID,
					'title'       => $post->post_title,
					'description' => $post->post_content,
					'post_slug'   => $post->post_name,
					'image'       => $thumbnail ? $thumbnail[0] : $thumbnail,
					'ingredients' => get_post_meta( $post->ID, '_recipe_ingredients', true )
				];
			}
		}

		return rest_ensure_response( $recipes );
	}
}