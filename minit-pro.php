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
		else {
			add_action( 'init', array( $instance, 'remove_default_filters' ), 20 );

			add_filter( 'minit-content-css', array( $instance, 'minify_css' ) );
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

	public function remove_default_filters() {
		remove_filter( 'minit-item-css', 'minit_comment_combined', 15, 3 );
		remove_filter( 'minit-item-js', 'minit_comment_combined', 15, 3 );

		remove_filter( 'minit-content-css', 'minit_add_toc', 100, 2 );
		remove_filter( 'minit-content-js', 'minit_add_toc', 100, 2 );

		remove_filter( 'minit-exclude-js', 'minit_exclude_defaults' );
	}


	public function minify_css( $content ) {
		include_once 'lib/CSSqueeze.php';
		$cz = new CSSqueeze;

		$content = $cz->squeeze( $content );

		return $content;
	}

}
