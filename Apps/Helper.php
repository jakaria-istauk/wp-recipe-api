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
}