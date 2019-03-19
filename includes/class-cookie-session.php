<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

new Math_Captcha_Cookie_Session();

class Math_Captcha_Cookie_Session {

	public $session_ids;

	public function __construct() {
		// set instance
		Math_Captcha()->cookie_session = $this;

		// actions
		add_action( 'plugins_loaded', array( $this, 'init_session' ), 1 );
	}

	/**
	 * Initialize cookie-session.
	 */
	public function init_session() {
		if ( is_admin() )
			return;

		// cookie exists?
		if ( isset( $_COOKIE['mc_session_ids'] ) && is_string( $_COOKIE['mc_session_ids'] ) ) {
			$cookie = json_decode( $_COOKIE['mc_session_ids'], true );

			// valid cookie?
			if ( is_array( $cookie ) && ( json_last_error() == JSON_ERROR_NONE ) )
				$this->session_ids = $cookie;
		}

		if ( empty( $this->session_ids ) ) {
			// add default hash
			$this->session_ids = array(
				'default'	=> sha1( $this->generate_password() )
			);

			// additional hashes
			for ( $i = 0; $i < 5; $i ++ ) {
				$this->session_ids['multi'][$i] = sha1( $this->generate_password() );
			}
		}

		// cookie doest not exist?
		if ( ! isset( $_COOKIE['mc_session_ids'] ) )
			setcookie( 'mc_session_ids', json_encode( $this->session_ids ), current_time( 'timestamp', true ) + apply_filters( 'math_captcha_time', Math_Captcha()->options['general']['time'] ), COOKIEPATH, COOKIE_DOMAIN, ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ? true : false ), true );
	}

	/**
	 * Generate password helper, without wp_rand() call
	 * 
	 * @param int $length
	 * @return string
	 */
	private function generate_password( $length = 64 ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$password = '';

		for ( $i = 0; $i < $length; $i ++ ) {
			$password .= substr( $chars, mt_rand( 0, 61 ), 1 );
		}

		return $password;
	}
}