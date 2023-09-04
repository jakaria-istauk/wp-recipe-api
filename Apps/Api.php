<?php

namespace JakariaIstaukPlugins\Apps;

class Api
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'init'));
    }
    public function init() {

		$api_name_space = 'recipe-api/v1';
        register_rest_route($api_name_space, '/recipes', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_all_recipes'),
        ));

        register_rest_route($api_name_space, '/recipe/(?P<slug>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_recipe_by_slug'),
        ));

	    register_rest_route($api_name_space, '/recipe', array(
		    'methods' => 'POST',
			'permission_call_back' => [$this, 'is_eligible'],
		    'callback' => array($this, 'create_a_recipe'),
	    ));

		register_rest_route($api_name_space, '/recipes/(?P<id>\d+)', array(
            'methods' => 'GET, POST, PUT, DELETE',
            'callback' => array($this, 'recipe_api_handler'),
        ));
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
				$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
				$recipes[] = [
					'id'          => $post->ID,
					'title'       => $post->post_title,
					'description' => $post->post_content,
					'slug'        => $post->post_name,
					'image'       => $thumbnail ? $thumbnail[0] : $thumbnail,
					'ingredients' => get_post_meta( $post->ID, '_recipe_ingredients', true )
				];
			}
		}

		return rest_ensure_response( $recipes );
	}

	public function get_recipe_by_slug($request) {

		$recipe = get_page_by_path( $request->get_param( 'slug' ), OBJECT, 'recipe' );
		if ( $recipe ) {
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $recipe->ID ), 'full' );
			$response = [
				'id'          => $recipe->ID,
				'title'       => $recipe->post_title,
				'description' => $recipe->post_content,
				'slug'        => $recipe->post_name,
				'image'       => $thumbnail ? $thumbnail[0] : $thumbnail,
				'ingredients' => get_post_meta( $recipe->ID, '_recipe_ingredients', true )
			];
		} else {
			$response = array(
				'message' => 'Recipe API is working!',
				'request' => $request->get_param( 'slug' )
			);
		}


		return rest_ensure_response($response);
	}

	public function is_eligible(){

	}

	public function create_a_recipe($request){
		$response = array(
			'message' => 'Recipe API is working!',
			'request' => $request->get_param( 'slug' )
		);

		return rest_ensure_response($response);
	}
}