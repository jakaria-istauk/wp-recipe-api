<?php

namespace Jakaria_Istauk_Plugins\Apps;

class Helper
{
	public static $chipher = "aes-256-cbc";
	public static $iv = "up'!zYPMf]6PCvD3";
	public static $secretKey = NONCE_KEY;

	public static function encrypt( $data ) {
		return openssl_encrypt( $data, self::$chipher, self::$secretKey, 0, self::$iv );
	}

	public static function decrypt( $data ) {
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

		if ( $author_id ) {
			$recipe_data['post_author'] = $author_id;
		}

		if ( ! empty( $data['id'] ) ) {
			$recipe_data['ID'] = $data['id'];
			$post_id           = wp_update_post( $recipe_data );
		} else {
			$post_id = wp_insert_post( $recipe_data );
		}

		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		$recipe      = get_post( $post_id );
		$ingredients = $data['ingredients'] ?? sanitize_text_field( $data['ingredients'] );
		update_post_meta( $post_id, '_recipe_ingredients', $ingredients );
		update_post_meta( $post_id, '_recipe_image_src_type', sanitize_text_field( $data['image_src_type'] ) );
		if ( ! empty( $data['image'] && ! empty( $data['image_src_type'] ) && $data['image_src_type'] == 'url' ) ) {
			update_post_meta( $post_id, '_recipe_image_url', sanitize_url( $data['image'] ) );
		}
		elseif ( ! empty( $data['image'] && ! empty( $data['image_src_type'] ) && $data['image_src_type'] == 'file' ) ){
			$attachment_id      = self::save_base_64_image( $data['image'], $recipe->post_name, $recipe->post_author );
			$prev_attachment_id = get_post_thumbnail_id( $recipe->ID );
			set_post_thumbnail( $post_id, $attachment_id );
			if ( $attachment_id && $prev_attachment_id ) {
				wp_delete_attachment( $prev_attachment_id, true );
			}
		}

		return $recipe;
	}

	/**
	 * Save the image on the server.
	 */
	public static function save_base_64_image( $base64_img, $title, $author = 0 ) {

		// Upload dir.
		$upload_dir  = wp_upload_dir();
		$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

		$extension = explode( '/', mime_content_type( $base64_img ) );
		if ( ! isset( $extension[1] ) ) {
			return false;
		}
		$img             = str_replace( "data:image/{$extension[1]};base64,", '', $base64_img );
		$img             = str_replace( ' ', '+', $img );
		$decoded         = base64_decode( $img );
		$filename        = "{$title}.{$extension[1]}";
		$file_type       = 'image/'.$extension[1];
		$hashed_filename = $filename;

		// Save the image in the uploads directory.
		$upload_file = file_put_contents( $upload_path . $hashed_filename, $decoded );

		$attachment = array(
			'post_mime_type' => $file_type,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $hashed_filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'guid'           => $upload_dir['url'] . '/' . basename( $hashed_filename ),
			'post_author'    => $author
		);

		return wp_insert_attachment( $attachment, $upload_dir['path'] . '/' . $hashed_filename );
	}

	public static function format_recipe_response_data( $post ){
		$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
		return [
			'id'             => $post->ID,
			'title'          => $post->post_title,
			'instructions'   => $post->post_content,
			'slug'           => $post->post_name,
			'image'          => $thumbnail ? $thumbnail[0] : get_post_meta( $post->ID, '_recipe_image_url', true ),
			'image_src_type' => get_post_meta( $post->ID, '_recipe_image_src_type', true ),
			'ingredients'    => get_post_meta( $post->ID, '_recipe_ingredients', true ),
			'user_hash'      => self::encrypt( "{$post->post_author}author" )
		];
	}
}