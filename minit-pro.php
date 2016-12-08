<?php
/*
Plugin Name: Minit PRO
Plugin URI: https://github.com/markoheijnen/Minit-Pro
GitHub URI: https://github.com/markoheijnen/Minit-Pro
Description: Add additional functionality to the Minit plugin of Kaspars Dambis.
Version: 1.0.0
Author: Marko Heijnen
Author URI: https://markoheijnen.com
*/

include 'inc/gz.php';

add_action( 'plugins_loaded', array( 'Minit_Pro', 'instance' ), 20 );

class Minit_Pro {

	protected function __construct() {
		$protocol = $_SERVER['SERVER_PROTOCOL'];

		if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTOCOL'] ) ) {
			$protocol = $_SERVER['HTTP_X_FORWARDED_PROTOCOL'];
		}

		if ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) {
			$this->disable_minit();
		}
		elseif ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol ) {
			$this->disable_minit();

			add_action( 'init', array( $this, 'minify_single_files' ) );
		}
		else {
			add_action( 'init', array( $this, 'remove_default_filters' ), 20 );

			add_filter( 'minit-content-css', array( $this, 'minify_css' ), 1000 );
			add_filter( 'minit-content-js', array( $this, 'minify_js' ), 1000 );

			add_filter( 'minit-result-css', array( $this, 'create_gz' ), 1000 );
			add_filter( 'minit-result-js', array( $this, 'create_gz' ), 1000 );
		}
	}

	public static function instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;

	}

	public function get_minit_instance() {
		if ( class_exists('Minit_Plugin') ) {
			return Minit_Plugin::instance();
		}

		return false;
	}

	public function disable_minit() {
		$minit = $this->get_minit_instance();

		if ( $minit ) {
			remove_action( 'init', array( $minit, 'init' ) );
		}
	}

	public function remove_default_filters() {
		remove_filter( 'minit-item-css', 'minit_comment_combined', 15, 3 );
		remove_filter( 'minit-item-js', 'minit_comment_combined', 15, 3 );

		remove_filter( 'minit-content-css', 'minit_add_toc', 100, 2 );
		remove_filter( 'minit-content-js', 'minit_add_toc', 100, 2 );
	}


	public function minify_css( $content ) {
		include_once 'lib/cssmin.php';

		$compressor = new CSSmin();
		$content = $compressor->run($content);

		return $content;
	}

	public function minify_js( $content ) {
		include_once 'lib/JSqueeze.php';

		$jz = new Patchwork\JSqueeze;

		// Remove broken else statements first
		$content = str_replace( 'else;', '', $content );

		$content = $jz->squeeze(
			$content,
			true,   // $singleLine
			false,  // $keepImportantComments
			false   // $specialVarRx
		);

		return $content;
	}

	public function create_gz( $result ) {
		if ( apply_filters( 'minit_pro_create_gz_file', false ) ) {
			Minit_Pro_GZ::compress_source( $result['file'] );
		}
	}


	public function minify_single_files() {
		if ( is_admin() || is_customize_preview() ) {
			return;
		}

		include 'inc/single-files.php';
		new Minit_Pro_Single_Files;
	}

}
