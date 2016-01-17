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

	public static function instance() {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();
		}

		if ( 'HTTP/1.1' != $_SERVER['SERVER_PROTOCOL'] && 'HTTP/1.0' != $_SERVER['SERVER_PROTOCOL'] ) {
			$minit = Minit_Plugin::instance();

			remove_action( 'init', array( $minit, 'init' ) );
		}

		return $instance;

	}


	protected function __construct() {

	}

}
