<?php

namespace JakariaIstaukPlugins\Apps;

class Helper
{
	public static $chipher = "aes-256-cbc";
	public static $iv = "up'!zYPMf]6PCvD3";
	public static $secretKey = NONCE_KEY;
	public static function encrypt( $data ){
		return openssl_encrypt( $data, self::$chipher, self::$secretKey, 0, self::$iv );
	}

	public static function decrypt( $data ){
		return openssl_decrypt( $data, self::$chipher, self::$secretKey, 0, self::$iv );
	}

	public static function prepare_user_login_hash( $user ){
		$user_login = [
			'email'      => $user->data->user_email,
			'id'         => $user->ID,
			'user_login' => $user->data->user_login
		];

		return  self::encrypt( json_encode( $user_login ) );
	}

	public static function prepare_user_caps_hash( $user ){
		$user_caps = [
			'user_name' => $user->data->user_login,
			'full_name' => get_user_meta( $user->ID, '_user_extra_data', true  ),
			'edit_cap'  => Helper::encrypt( "{$user->ID}author" ),
			'post_cap'  => user_can( $user->ID, 'edit_post' )
		];

		return  base64_encode( json_encode( $user_caps ) );
	}

	public static function recipe_manager( $data, $author_id = false ){

		$recipe_data = [
			'post_type'    => 'recipe',
			'post_title'   => $data['title'] ?? sanitize_text_field( $data['title'] ),
			'post_content' => $data['instructions'] ?? sanitize_textarea_field( $data['instructions'] ),
			'post_status'  => 'publish',
		];

		if ( $author_id ){
			$recipe_data['post_author'] = $author_id;
		}

		if ( !empty( $data['id'] ) ){
			$recipe_data['ID'] = $data['id'];
			$post_id = wp_update_post( $recipe_data );
		}
		else{
			$post_id = wp_insert_post( $recipe_data );
		}

		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		$ingredients = $data['ingredients'] ?? sanitize_text_field( $data['ingredients'] );
		update_post_meta( $post_id, '_recipe_ingredients', $ingredients );
		if ( ! empty( $data['image'] ) ) {
			update_post_meta( $post_id, '_recipe_image_url', sanitize_url( $data['image'] ) );
		}

		return get_post( $post_id );
	}
}