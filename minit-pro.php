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

add_action( 'plugins_loaded', array( 'Minit_Pro', 'instance' ), 20 );

class Minit_Pro {

	protected function __construct() {
		if ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) {
			$this->disable_minit();
		}
		elseif ( 'HTTP/1.1' != $_SERVER['SERVER_PROTOCOL'] && 'HTTP/1.0' != $_SERVER['SERVER_PROTOCOL'] ) {
			$this->disable_minit();
			$this->minify_single_files();
		}
		else {
			add_action( 'init', array( $this, 'remove_default_filters' ), 20 );

			add_filter( 'minit-content-css', array( $this, 'minify_css' ) );
			add_filter( 'minit-content-js', array( $this, 'minify_js' ) );
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
		return Minit_Plugin::instance();
	}

	public function disable_minit() {
		$minit = $this->get_minit_instance();
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
		include_once 'lib/cssmin.php';

		$compressor = new CSSmin();
		$content = $compressor->run($content);

		return $content;
	}

	public function minify_js( $content ) {
		include_once 'lib/JSqueeze.php';

		$jz = new Patchwork\JSqueeze;

		$content = $jz->squeeze(
			$content,
			true,   // $singleLine
			false,  // $keepImportantComments
			false   // $specialVarRx
		);

		return $content;
	}


	public function minify_single_files() {
		include 'inc/single-files.php';
		new Minit_Pro_Single_Files;
	}

}
