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
}