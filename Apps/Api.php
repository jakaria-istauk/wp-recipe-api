<?php

namespace JakariaIstaukPlugins\Apps;

class Api
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'init'));
	    $plugin_dir_path = plugin_dir_path(__FILE__);
	    require_once( $plugin_dir_path . 'Helper.php');
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
		    'callback' => array($this, 'create_a_recipe'),
	    ));

		register_rest_route($api_name_space, '/recipes/(?P<id>\d+)', array(
            'methods' => 'GET, POST, PUT, DELETE',
            'callback' => array($this, 'recipe_api_handler'),
        ));

	    register_rest_route($api_name_space, '/user/signup', array(
		    'methods' => 'POST',
		    'callback' => array($this, 'register_user'),
	    ));

	    register_rest_route($api_name_space, '/user/login', array(
		    'methods' => 'POST',
		    'callback' => array($this, 'user_validation'),
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

	public function get_recipe_by_slug( $request ) {

		$recipe = get_page_by_path( $request->get_param( 'slug' ), OBJECT, 'recipe' );
		if ( $recipe ) {
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $recipe->ID ), 'full' );
			$response  = [
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


		return rest_ensure_response( $response );
	}

	public function create_a_recipe( $request ) {
		$response = array(
			'message' => 'Recipe API is working for post!',
			'request' => $request->get_body_params()
		);

		return rest_ensure_response( $response );
	}

	public function register_user( $request ){
		$params = $request->get_body_params();
		if ( !isset( $params['email'] ) ){
			$response = [
				'status' => 0,
				'type' => 'data_missing',
				'message' => 'Email is required'
			];
			return rest_ensure_response( $response );
		}

		$user_name  = '';
		$first_name = $params['fname'] ?? '';
		$first_name = sanitize_text_field( $first_name );
		$last_name  = $params['lname'] ?? '';
		$last_name  = sanitize_text_field( $last_name );
		$user_name  .= $first_name;
		$user_name  .= $last_name;
		$user_name  = strtolower( $user_name );
		$user_email = sanitize_email( $params['email'] );
		$user_pass  = $params['password'] ?? wp_generate_password( 6, true );
		if ( username_exists( $user_name ) ){
			$_user_name = explode('@', $params['email'] );
			$user_name = $_user_name[0];
		}

		if ( email_exists( $user_email ) ){
			$response = [
				'status' => 0,
				'type' => 'email_exist',
				'message' => 'Email already exist'
			];
			return rest_ensure_response( $response );
		}

		$user_id = wp_create_user( $user_name, $user_pass, $user_email );
		if ( !$user_id ){
			$response = [
				'status' => 0,
				'type' => 'create_failed',
				'message' => 'Something went wrong. Try again'
			];
			return rest_ensure_response( $response );
		}
		$user_data = [
			'first_name' => $first_name,
			'last_name'  => $last_name,
		];

		update_user_meta( $user_id, '_user_extra_data', $user_data );

		$response = array(
			'status'  => 1,
			'type'    => 'user_created',
			'message' => 'User register Successfully!',
		);

		return rest_ensure_response( $response );
	}

	public function user_validation( $request ) {
		$params = $request->get_body_params();

		if ( ! isset( $params['email'] ) ){
			$response = [
				'status' => 0,
				'type' => 'data_missing',
				'message' => 'Email is required'
			];
			return rest_ensure_response( $response );
		}
		$user_email = $params['email'] ?? '';
		$user_pass = $params['password'] ?? '';
		$user_email = sanitize_email( $user_email );

		$user = get_user_by( 'email', $user_email );

		if ( !$user ){
			$response = [
				'status' => 0,
				'type' => 'invalid_email',
				'message' => 'Email is not found'
			];
			return rest_ensure_response( $response );
		}

		if ( !wp_check_password( $user_pass, $user->data->user_pass, $user->ID ) ){
			$response = [
				'status' => 0,
				'type' => 'invalid_password',
				'message' => 'Password Not matched'
			];
			return rest_ensure_response( $response );
		}

		$user_login = [
			'email'      => $user->data->user_email,
			'id'         => $user->ID,
			'user_login' => $user->data->user_login
		];

		$response = array(
			'status'     => 1,
			'type'       => 'logged_in',
			'message'    => 'User authenticated!',
			'user_login' => Helper::encrypt( json_encode( $user_login ) ),
			'user_caps'  => base64_encode( json_encode( $user_login ) )
		);

		return rest_ensure_response( $response );
	}
}