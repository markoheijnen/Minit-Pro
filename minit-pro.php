<?php
/*
Plugin Name: Minit PRO
Plugin URI: 
GitHub URI: 
Description: Add additional functionality to the Minit plugin of Kaspars Dambis.
Version: 1.0.0
Author: Marko Heijnen
Author URI: https://markoheijnen.com
*/

add_action( 'plugins_loaded', array( 'Minit_Plugin_Pro', 'instance' ), 20 );

class Minit_Plugin_Pro {

	protected function __construct() {

	}

	public static function instance() {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();
		}

		if ( 'HTTP/1.1' != $_SERVER['SERVER_PROTOCOL'] && 'HTTP/1.0' != $_SERVER['SERVER_PROTOCOL'] ) {
			self::disable_minit();
		}
		elseif ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) {
			self::disable_minit();
		}

		return $instance;

	}

	public static function get_minit_instance() {
		return Minit_Plugin::instance();
	}

	public static function disable_minit() {
		$minit = self::get_minit_instance();
		remove_action( 'init', array( $minit, 'init' ) );
	}
}
