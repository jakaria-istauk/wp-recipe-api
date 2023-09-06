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

		register_rest_route( $api_name_space, '/user/signup', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'register_user' ),
		) );

		register_rest_route( $api_name_space, '/user/login', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'user_validation' ),
		) );
		register_rest_route( $api_name_space, '/recipes', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_all_recipes' ),
		) );

		register_rest_route( $api_name_space, '/recipe/(?P<slug>[a-zA-Z0-9-]+)', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_recipe_by_slug' ),
		) );

		register_rest_route( $api_name_space, '/recipe', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'create_a_recipe' ),
		) );

		register_rest_route( $api_name_space, '/recipes/(?P<id>\d+)', array(
			'methods'  => 'GET, POST, PUT, DELETE',
			'callback' => array( $this, 'recipe_api_handler' ),
		) );
	}

	public function register_user( $request ){
		$params = $request->get_json_params();

		if ( empty( $params ) ){
			$params = $request->get_body_params();
		}

		if ( !isset( $params['email'] ) ){
			$response = [
				'status' => 0,
				'type' => 'data_missing',
				'message' => 'Email is required'
			];
			return rest_ensure_response( $response );
		}

		$user_name  = '';
		$first_name = $params['first_name'] ?? '';
		$first_name = sanitize_text_field( $first_name );
		$last_name  = $params['last_name'] ?? '';
		$last_name  = sanitize_text_field( $last_name );
		$user_name  .= $first_name;
		$user_name  .= $last_name;
		$user_name  = strtolower( $user_name );
		$user_email = sanitize_email( $params['email'] );
		$user_pass  = $params['password'] ?? wp_generate_password( 6, true );
		if ( username_exists( $user_name ) || !$user_name ){
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

		$user = get_user_by( 'id', $user_id );

		$response = array(
			'status'     => 1,
			'type'       => 'user_created',
			'message'    => 'User register Successfully!',
			'user_login' => Helper::prepare_user_login_hash( $user ),
			'user_caps'  => Helper::prepare_user_caps_hash( $user ),
		);

		return rest_ensure_response( $response );
	}

	public function user_validation( $request ) {
		$params = $request->get_json_params();

		if ( empty( $params ) ){
			$params = $request->get_body_params();
		}

		if ( ! isset( $params['email'] ) ){
			$response = [
				'status' => 0,
				'type' => 'data_missing',
				'message' => 'Email is required'
			];
			return rest_ensure_response( $response );
		}
		$user_email = $params['email'];
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

		$response = array(
			'status'     => 1,
			'type'       => 'logged_in',
			'message'    => 'User authenticated!',
			'user_login' => Helper::prepare_user_login_hash( $user ),
			'user_caps'  => Helper::prepare_user_caps_hash( $user ),
		);

		return rest_ensure_response( $response );
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
					'image'       => $thumbnail ? $thumbnail[0] : get_post_meta( $post->ID, '_recipe_image_url', true ),
					'ingredients' => get_post_meta( $post->ID, '_recipe_ingredients', true ),
					'user_hash'   => Helper::encrypt( "{$post->post_author}author" )
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
				'image'       => $thumbnail ? $thumbnail[0] : get_post_meta( $recipe->ID, '_recipe_image_url', true ),
				'ingredients' => get_post_meta( $recipe->ID, '_recipe_ingredients', true ),
				'user_hash'   => Helper::encrypt( "{$recipe->post_author}author" )
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

		$auth_token = $request->get_header('authorization');
		$auth_data = json_decode( Helper::decrypt($auth_token) );

		if ( !$auth_data ){
			$response = array(
				'status'  => 0,
				'message' => 'Unauthorized Request',
			);
			return rest_ensure_response( $response );
		}

		$user = get_user_by( 'id', $auth_data->id );

		if ( ! user_can( $user->ID, 'edit_posts' ) ) {
			$response = array(
				'status'  => 0,
				'message' => 'User is not capable to create recipe!',
			);

			return rest_ensure_response( $response );
		}

		$params = $request->get_json_params();

		if ( empty( $params ) ){
			$params = $request->get_body_params();
		}

		$post_id = wp_insert_post( [
			'post_type'    => 'recipe',
			'post_title'   => $params['title'] ?? sanitize_text_field( $params['title'] ),
			'post_content' => $params['description'] ?? sanitize_textarea_field( $params['description'] ),
			'post_status'  => 'publish',
			'post_author'  => $user->ID,
		] );

		if ( !$post_id ) {
			$response = array(
				'status'  => 0,
				'message' => 'Recipe Can\'t create. Something went wrong. Try Again',
			);

			return rest_ensure_response( $response );
		}

		update_post_meta( $post_id, '_recipe_ingredients', $params['ingredients'] ?? sanitize_text_field( $params['ingredients'] ) );
		if ( !empty( $params['image_url'] ) ){
			update_post_meta( $post_id, '_recipe_image_url', sanitize_url( $params['image_url'] ) );
		}
		$recipe   = get_post( $post_id );
		$response = array(
			'status'  => 1,
			'message' => 'Recipe created Successfully!',
			'slug'    => $recipe->post_name,
		);

		return rest_ensure_response( $response );
	}
}